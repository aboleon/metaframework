<?php

namespace App\Http\Controllers;

use App\Accessors\Accounts;
use App\Accessors\Establishments;
use App\Actions\Account\EventContactActions;
use App\Actions\Account\Replicator;
use App\Actions\AccountProfile\Profile as AccountProfileManager;
use App\Actions\EventManager\GrantActions;
use App\Actions\EventManager\Program\AssociateEventContactToInterventionAction;
use App\Actions\EventManager\Program\AssociateEventContactToSessionAction;
use App\Actions\GroupContactActions;
use App\DataTables\AccountDataTable;
use App\Enum\ParticipantType;
use App\Enum\UserType;
use App\Http\Requests\AccountRequest;
use App\Models\{Account, AccountPhone, Event, EventContact, EventManager\Program\EventProgramIntervention, EventManager\Program\EventProgramSession, Group, ParticipationType};
use App\Printers\EventPrinter;
use App\Traits\{Locale, SelectableValues};
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use MetaFramework\Actions\Suppressor;
use MetaFramework\Services\Passwords\PasswordBroker;
use MetaFramework\Services\Validation\ValidationTrait;
use MetaFramework\Traits\Responses;
use Throwable;

class AccountController extends Controller
{
    use Locale;
    use SelectableValues;
    use Responses;
    use ValidationTrait;


    public function index(AccountDataTable $dataTable, string $role): JsonResponse|View
    {
        return $dataTable->render('accounts.index', [
            'role'     => $role,
            'archived' => request()->routeIs('panel.accounts.archived'),
        ]);
    }

    public function create(): Renderable
    {
        $account = new Account();

        // Associer un groupe
        $group = request()->filled('group')
            ? Group::find(request('group'))
            : new Group();

        $group_msg = $group->id
            ? "Ce compte sera affecté comme contact du groupe <a href='".route('panel.groups.edit', $group)."'>".$group->name.' / '.$group->company.'</a>'
            : null;

        // Associer un évènement
        $event = request()->filled('event')
            ? Event::find(request('event'))
            : new Event();

        $event_msg = $event->id
            ? "Ce compte sera affecté comme contact de l'évènement <a href='".route('panel.events.edit', $event)."'>".(new EventPrinter($event))->names().'</a>'
            : null;


        return view('accounts.edit')->with(
            array_merge(
                $this->sharedEditableData($account),
                [
                    'photo_media_settings' => $account->photoMediaSettings(),
                    'route'                => route('panel.accounts.store'),
                    'group_msg'            => $group_msg,
                    'event_msg'            => $event_msg,
                    'associate_group'      => $group->id,
                    'associate_event'      => $event->id,
                ],
            ),
        );
    }

    public function edit(int $account_id): Renderable
    {
        $account = Account::withTrashed()->findOrFail($account_id);

        return view('accounts.edit')->with(
            $this->getAccountEditViewData($account),
        );
    }

    public function getAccountEditViewData(Account $account): array
    {
        return array_merge(
            $this->sharedEditableData($account),
            [
                'photo_media_settings' => $account->photoMediaSettings(),
                'route'                => route('panel.accounts.update', $account),
            ],
        );
    }

    public function store(): RedirectResponse
    {
        $this->tabRedirect();

        $validation                = new AccountRequest();
        $this->validation_rules    = $validation->rules();
        $this->validation_messages = $validation->messages();

        $this->validation();

        $password_broker = (new PasswordBroker(request()))->passwordBroker();

        $this->validated_data['user']['password'] = $password_broker->getEncryptedPassword();
        $this->validated_data['user']['type']     = UserType::ACCOUNT->value;


        //$this->responseNotice($password_broker->printPublicPassword());
        try {
            $account = Account::create($this->validated_data['user']);
            $this->processPhoto($account);
            $this->processDocuments($account);

            $this->manageBlacklisted();
            (new AccountProfileManager($account, $this->validated_data['profile']))->create();


            # Create phone
            if ( ! empty($this->validated_data['phone']['phone']) && ! empty($this->validated_data['phone']['country_code'])) {
                $account->phones()->save(new AccountPhone(array_merge($this->validated_data['phone'], ['default' => 1])));
            }


            // event(new Registered($account));

            /*
             *
             * $this->responseSuccess(__('auth.verification_link_sent_admin'));
            */

            $this->responseSuccess("Le compte a été créé.");
            $this->redirect_to = route('panel.accounts.index', request('profile.account_type'));
            $this->saveAndRedirect(route('panel.accounts.index', request('profile.account_type')));


            if (request()->filled('intervention_id')) {
                $intervention = EventProgramIntervention::find(request('intervention_id'));
                if ($intervention) {
                    $event    = $intervention->session->programDay->event;
                    $ecAction = (new EventContactActions())
                        ->enableAjaxMode()
                        ->setAccount($account->id)
                        ->setEvent($event->id)
                        ->associate();
                    $this->pushMessages($ecAction);

                    if ($ecAction->getEventContact()) {
                        $this->pushMessages(
                            (new AssociateEventContactToInterventionAction(
                                eventContact: $ecAction->getEventContact(),
                                intervention: $intervention,
                            ))->associate(),
                        );
                    } else {
                        $this->responseWarning("L'association du contact à l'intervention a échoué car aucun EventContact n'a été trouvé.");
                    }

                    $this->redirectTo(route('panel.manager.event.program.intervention.edit', [
                        'event'        => $event,
                        'intervention' => $intervention->id,
                    ]));
                }
            } elseif (request()->filled('session_id')) {
                $session = EventProgramSession::find(request('session_id'));
                if ($session) {
                    $event    = $session->programDay->event;
                    $ecAction = (new EventContactActions())
                        ->enableAjaxMode()
                        ->setAccount($account->id)
                        ->setEvent($event->id)
                        ->associate();

                    $this->pushMessages($ecAction);


                    if ($ecAction->getEventContact()) {
                        $this->pushMessages(
                            (new AssociateEventContactToSessionAction(
                                eventContact: $ecAction->getEventContact(),
                                session: $session,
                            ))->associate(),
                        );
                    } else {
                        $this->responseWarning("L'association du contact à la session a échoué car aucun EventContact n'a été trouvé.");
                    }

                    $this->redirectTo(route('panel.manager.event.program.session.edit', [
                        'event'   => $event,
                        'session' => $session->id,
                    ]));
                }
            } elseif (request()->filled('associate_group')) {
                $this->pushMessages(
                    (new GroupContactActions(account_id: $account->id, group_id: request('associate_group')))->associate(),
                );
                $this->redirectTo(route('panel.groups.edit', request('associate_group')));

            } elseif (request()->filled('associate_event')) {
                $participation_type_id = request('participation_type_id');
                $provenance            = request('provenance');

                if ($provenance) {
                    if ($provenance == 'event') {
                        $this->redirectTo(route('panel.events.edit', request('associate_event')));
                    } else {
                        // todo: some pages still might need to define provenance, search for <x-ajaxable-contacts component
                        $this->redirectTo(route('panel.events.edit', request('associate_event')));
                    }
                } elseif ($participation_type_id) {

                    $group = ParticipationType::find($participation_type_id)?->group == ParticipantType::ORATOR->value ? "orator" : 'all';
                    $this->redirectTo(route('panel.manager.event.event_contact.index', [
                        'event' => request('associate_event'),
                        'group' => $group,
                    ]));
                }
            }
        } catch (Throwable $e) {
            if (isset($account) && $account instanceof Account && $account->id) {
                $account->forceDelete();
            }
            $this->responseException($e);
        }

        return $this->sendResponse();
    }


    public function update(Account $account): RedirectResponse
    {
        $this->tabRedirect();

        $validation                = new AccountRequest($account);
        $this->validation_rules    = $validation->rules();
        $this->validation_messages = $validation->messages();

        $this->validation();


        /**
         * Manage password change
         */
        $password_broker = (new PasswordBroker(request()));
        if ($password_broker->requestedChange()) {
            $this->validated_data['user']['password'] = $password_broker->getEncryptedPassword();
            $this->responseNotice($password_broker->printPublicPassword());
        }

        try {
            $account->update($this->validated_data['user']);
            $this->processPhoto($account);
            $this->processDocuments($account);

            $account->saveCustomFormFields();

            $this->manageBlacklisted();
            (new AccountProfileManager($account, $this->validated_data['profile']))->update();


            # Update phone

            if ( ! empty($this->validated_data['phone']['phone']) && ! empty($this->validated_data['phone']['country_code'])) {
                $defaultPhone = $account->phones->where('default', 1)->first();
                if ($defaultPhone) {
                    $defaultPhone->phone        = $this->validated_data['phone']['phone'];
                    $defaultPhone->country_code = $this->validated_data['phone']['country_code'];
                    $defaultPhone->save();
                } else {
                    $account->phones()->save(new AccountPhone(array_merge($this->validated_data['phone'], ['default' => 1])));
                }
            }

            // Update Event Contact ID (si la req.vient de l'UI Participant dans Gestion de l'évènement
            if (request()->has('event_contact_id')) {
                try {
                    $eventContact = EventContact::findOrFail(request('event_contact_id'));
                    $this->pushMessages(
                        (new GrantActions())->updateEligibleStatusForSingleContact($eventContact->event, $eventContact),
                    );
                } catch (Throwable $e) {
                    $this->responseException($e, "Une erreur est survenue avec la mise à jour du contact évènement");
                }
            }

            $this->redirect_to = route('panel.accounts.index', request('profile.account_type'));
            $this->saveAndRedirect(route('panel.accounts.index', request('profile.account_type')));
            $this->responseSuccess(__('ui.record_updated'));
        } catch (Throwable $e) {
            $this->responseWarning(__('ui.operation_failed'));
            $this->responseWarning($e->getMessage());
        }

        return $this->sendResponse();
    }

    /**
     * @throws \Exception
     */
    public function destroy(Account $account): RedirectResponse
    {
        return (new Suppressor($account))
            ->remove()
            ->whitout('object')
            ->responseSuccess(__('Le compte est archivé.'))
            ->redirectTo(route('panel.accounts.index', $account->profile->account_type))
            ->sendResponse();
    }

    public function restore(int $account_id): RedirectResponse
    {
        try {
            $account = Account::withTrashed()->findOrFail($account_id);
            $account->restore();
            $this->responseSuccess("Le compte a été réactivé");
        } catch (Throwable) {
            $this->responseSuccess("Ce compte n'existe pas");
        }

        return $this->sendResponse();
    }

    public function replicate(int $account_id): RedirectResponse
    {
        $account = Account::withTrashed()->findOrFail($account_id);

        try {
            $replicated = (new Replicator($account))();
            $this->responseSuccess("Le compte a été dupliqué");
            $this->redirectTo(route('panel.accounts.edit', $replicated));
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->sendResponse();
    }


    //--------------------------------------------
    //
    //--------------------------------------------
    /**
     * @param  \App\Models\Account  $account
     *
     * @return array<string, mixed>
     */
    private function sharedEditableData(Account $account): array
    {
        return [
            'account'               => $account,
            'accessor'              => (new Accounts($account)),
            'roles'                 => self::convertCollectionToValues($account->publicUsers(), 'label'),
            'role'                  => $account->profile?->account_type ?: request('role', 'all'),
            'provenance'            => request('provenance'),
            'participation_type_id' => request('participation_type_id'),
            'establishments'        => Establishments::orderedIdNameArray(),
            'intervention_id'       => request('intervention_id'),
            'session_id'            => request('session_id'),
        ];
    }

    private function manageBlacklisted(): void
    {
        $this->validated_data['profile']['blacklisted'] = Arr::has($this->validated_data['profile'], 'blacklisted') ? now() : null;
    }

    private function processPhoto(Account $account): void
    {
        if (request()->has('photo')) {
            $account
                ->clearMediaCollection('photo')
                ->addFromMediaLibraryRequest(request("photo"))
                ->toMediaCollection('photo');
        }
    }

    private function processDocuments(Account $account): void
    {
        if (request()->has('documents')) {
            $account
                ->syncFromMediaLibraryRequest(request("documents"))
                ->toMediaCollection('documents');
        }
    }
}

