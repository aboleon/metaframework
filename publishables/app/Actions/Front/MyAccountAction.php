<?php

namespace App\Actions\Front;

use App\Accessors\Dates;
use App\Accessors\EventContactAccessor;
use App\Enum\ClientType;
use App\Enum\ParticipantType;
use App\Http\Requests\Front\User\AccountRequest;
use App\Models\Account;
use App\Models\AccountPhone;
use App\Models\AccountProfile;
use App\Models\Event;
use App\Models\EventContact;
use App\Models\ParticipationType;
use App\Models\User;
use App\Models\UserRegistration;
use DateTime;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MetaFramework\Mediaclass\Controllers\FileUploadImages;
use MetaFramework\Services\Passwords\PasswordBroker;
use MetaFramework\Traits\Responses;
use Throwable;

class MyAccountAction
{
    use Responses;

    private ?string $registrationType = null;
    private array $options = [];

    private Account $account;
    private Event $event;
    private bool $isRegistering = false;
    private UserRegistration $registrationInstance;

    public function __construct(
        public AccountRequest $request,
    ) {}

    public function setAccount(Account $user): self
    {
        $this->account = $user;

        return $this;
    }

    public function setEvent(Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function setRegistrationInstance(UserRegistration $registrationInstance): self
    {
        $this->registrationInstance = $registrationInstance;

        return $this;
    }

    public function isRegistering(): self
    {
        $this->isRegistering = true;

        return $this;
    }

    public function setRegistrationType(string $type): self
    {
        $this->registrationType = $type;

        return $this;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    // Public method to handle account creation

    /**
     * @throws Throwable
     */
    public function register(): RedirectResponse
    {
        // Step 1: Check for duplicate email before proceeding
        if ( ! $this->isRegistering) {
            $error = $this->checkForDuplicateEmail();
            if ($error) {
                return $this->handleError($error);
            }
        }
        DB::beginTransaction();

        try {

            // Step 2: Begin transaction for account creation

            // Step 3: Create the user account
            $this->account->fill(request()->only(['first_name', 'last_name']));

            if (!$this->account?->id) {

                $password_broker = (new PasswordBroker(request()))->passwordBroker();
                $this->account->email    = $this->registrationInstance->email;
                $this->account->password = $password_broker->getEncryptedPassword();
                $this->account->email_verified_at = now();
                session(['registration_temp_password' => $password_broker->getPublicPassword()]);
            }
            $this->account->save();

            # Update registration account relation
            $this->registrationInstance->account_id = $this->account->id;
            $this->registrationInstance->save();

            $this->handleEventContact();

          //  event(new Registered($this->account->user));

            $this->proccess();
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            $this->responseException($e);
        }

        $this->fetchResponse();

        // Step 9: Redirect after successful creation
        return $this->redirectAfterCreation();
    }

    // Public method to handle account updating

    /**
     * @throws Throwable
     */
    public function update(): RedirectResponse
    {
        $error = $this->checkForDuplicateEmail();
        if ($error) {
            return $this->handleError($error);
        }

        DB::beginTransaction();

        try {
            $this->account->fill(request()->only(['first_name', 'last_name']));
            if (request()->filled('email')) {
                $this->account->email = $this->request->email;
            }
            $this->account->save();
            $this->proccess();

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Transaction failed: '.$e->getMessage());
            throw $e;
        }

        return $this->redirectAfterUpdate();
    }

    private function proccess(): void
    {
        // Step 4: Handle profile update
        $this->handleProfile();

        // Step 5: Handle event contact update if needed
        $this->handleEventContact();

        // Step 6: Process phone entry if provided
        $this->handlePhone();

        // Step 7: Handle file uploads (photos)
        $this->handleFileUpload();
    }

    // --------------- Validation & Error Handling -----------------

    private function checkForDuplicateEmail(): ?string
    {
        $exists = User::where('email', $this->request->email)
            ->where('id', '<>', $this->account->id)
            ->exists();

        return $exists ? __('front/account.email_already_exists') : null;
    }

    private function handleError($error): RedirectResponse
    {
        $eventGroupId    = $this->options['event_group_id'] ?? null;
        $existingAccount = $this->options['existing_account'] ?? false;

        if ( ! $this->isRegistering) {
            return redirect()->route('front.event.account.update', $this->event)->withInput()->with('error', $error);
        } else {
            return redirect()->route('front.register-public-account-form', [
                'locale'           => app()->getLocale(),
                'token'            => $this->event,
                'existing_account' => $existingAccount,
                'event_group_id'   => $eventGroupId,
            ])->withInput()->with('error', $error);
        }
    }

    // --------------- Profile Logic -----------------

    private function handleProfile(): void
    {
        if ( ! $this->account->profile) {
            $this->account->profile             = new AccountProfile();
            $this->account->profile->user_id    = $this->account->id;
            $this->account->profile->created_by = $this->account->id;
        }

        // Determine account type based on registration type
        $accountType = match ($this->registrationType) {
            'participant', 'congress', ParticipantType::ORATOR->value => ClientType::MEDICAL->value,
            'industry', 'group' => ClientType::COMPANY->value,
            default => ClientType::OTHER->value,
        };

        // Fill profile information
        $profileData                 = request()->only([
            'passport_first_name',
            'passport_last_name',
            'domain_id',
            'title_id',
            'civ',
            'rpps',
            'language_id',
            'profession_id',
            'function',
            'establishment_id',
            'savant_society_id',
        ]);
        $profileData['account_type'] = $accountType;

        if ($this->request->filled('birth')) {
            $profileData['birth'] = DateTime::createFromFormat(Dates::getFrontDateFormat(), $this->request->birth)->format('Y-m-d');
        }

        $this->account->profile->fill($profileData);
        $this->account->profile->save();
    }

    // --------------- Event Contact Logic -----------------

    private function handleEventContact(): void
    {
        $createEventContact           = $this->options['createEventContact'] ?? false;
        $allowParticipationTypeUpdate = $this->options['allowParticipationTypeUpdate'] ?? true;

        if ($createEventContact) {
            $eventContact = EventContactAccessor::getEventContactByEventAndUser($this->event->id, $this->account->id);

            if ( ! $eventContact) {
                $eventContact = new EventContact();
                $eventContact->fill([
                    'event_id'             => $this->event->id,
                    'user_id'              => $this->account->id,
                    'registration_type'    => $this->registrationType,
                    'subscribe_newsletter' => $this->options['subscribe_newsletter'] ?? false,
                    'subscribe_sms'        => $this->options['subscribe_sms'] ?? false,
                ]);
                $eventContact->save();
            }
        }

        if ($allowParticipationTypeUpdate) {
            $this->updateParticipationType();
        }
    }

    private function updateParticipationType(): void
    {
        $pId = (int)request('participation_type');
        if (ParticipationType::find($pId)) {
            $eventContact = EventContactAccessor::getEventContactByEventAndUser($this->event->id, $this->account->id);

            if ( ! $eventContact) {
                $eventContact           = new EventContact();
                $eventContact->event_id = $this->event->id;
                $eventContact->user_id  = $this->account->id;
            }

            $eventContact->participation_type_id = $pId;
            $eventContact->save();
        }
    }

    // --------------- Phone Handling -----------------

    private function handlePhone(): void
    {
        if (request()->filled('phone')) {
            $phone          = new AccountPhone();
            $phone->user_id = $this->account->id;
            $phone->fill(request()->only('phone'))->save();
        }
    }

    // --------------- File Upload Logic -----------------

    private function handleFileUpload(): void
    {
        if ($this->request->hasFile('files')) {
            $this->account->access_key = "account/".$this->account->id;
            $this->account->deleteModelMedia();

            $uploader = new FileUploadImages();
            $uploader->setModel("App\Models\Account")->upload()->fetchResponse();
        }
    }

    // --------------- Redirect Logic -----------------

    private function redirectAfterCreation(): RedirectResponse
    {
        $event_group_id = $this->options['event_group_id'] ?? null;

        return redirect()->route('front.register-public-account-form', [
            'locale'           => app()->getLocale(),
            'token'            => $this->registrationInstance->id,
            'step'             => 2,
            'event_group_id'   => $event_group_id,
        ]);
    }

    private function redirectAfterUpdate(): RedirectResponse
    {
        return redirect()
            ->route('front.event.account.update', $this->event)
            ->with('success', __('front/account.update_success'));
    }
}
