<?php

namespace App\Http\Controllers;

use App\Accessors\Dictionnaries;
use App\Enum\EstablishmentType;
use App\Models\Account;
use App\Models\AccountMail;
use App\Models\Establishment;
use App\Models\Place;
use App\Models\PlaceRoom;
use App\Models\Sellable;
use App\Models\SellableByEvent;
use Illuminate\Support\Facades\View;
use MetaFramework\Traits\Responses;
use Throwable;

class ModalController extends Controller
{
    use Responses;

    public function distribute(string $requested): array
    {
        return method_exists($this, $requested)
            ? $this->{$requested}()
            : $this->default();
    }

    public function sellableByEvent(): array
    {
        try {
            $sellable = Sellable::find(request('id'));
            $sellable_event = SellableByEvent::where(['event_id' => request('event_id'), 'sellable_id' => request('id')])->first();

            $message = view()->make('mfw::components.alert', [
                'type' => 'warning',
                'class' => '',
                'message' => "Cet article n'as pas de configuration sur mesure pour cet évènement"
            ])->render();

            if ($sellable_event) {
                $message = view()->make('mfw::components.notice', [
                    'class' => '',
                    'message' => "<div class='d-flex justify-content-between align-items-center'><span>Ceci est la configuration sur mesure de l'article pour cet évènement</span><button id='remove_sellable_customization' type='button' class='btn btn-danger btn-sm'>Supprimer cette personnalisation</button></div>"
                ])->render();
            }

            return [
                'title' => "Ficher d'un article à la vente",
                'btn_cancel' => 'Fermer',
                'btn_save' => 'Enregistrer des informations sur mesure',
                'action' => __FUNCTION__,
                'view' => View::make('sellable.modals.show', [
                    'event_id' => request('event_id'),
                    'data' => $sellable_event ?: $sellable,
                    'sellable' => $sellable,
                    'custom' => $sellable_event,
                    'info' => $message,
                ])->render()
            ];
        } catch (Throwable $e) {
            $this->responseException($e);
            return $this->throwable();
        }
    }

    public function createPlace(): array
    {
        try {
            return [
                'title' => "Ajouter un lieu",
                'action' => __FUNCTION__,
                'view' => View::make('places.modals.create', [
                    'data' => new Place()
                ])->render()
            ];
        } catch (Throwable $e) {
            $this->responseException($e);
            return $this->throwable();
        }
    }

    public function createPlaceRoom(): array
    {
        try {

            $place = Place::findOrFail(request('place_id'));
            $room = new PlaceRoom;
            $room->place()->associate($place);

            return [
                'title' => "Ajouter une salle",
                'action' => __FUNCTION__,
                'view' => View::make('places.modals.create_room', [
                    'place' => $place,
                    'data' => $room
                ])->render()
            ];
        } catch (Throwable $e) {
            $this->responseException($e);
            return $this->throwable();
        }
    }

    public function createEstablishment(): array
    {
        try {
            return [
                'title' => "Ajouter un établissement",
                'action' => __FUNCTION__,
                'view' => View::make('establishments.modals.create', [
                    'data' => new Establishment(),
                    'types' => EstablishmentType::translations(),
                ])->render()
            ];
        } catch (Throwable $e) {
            $this->responseException($e);
            return $this->throwable();
        }
    }

    public function createProfession(): array
    {
        try {
            return [
                'title' => "Ajouter une profession",
                'action' => __FUNCTION__,
                'view' => View::make('dictionnary.modals.profession', [
                    'entries' => Dictionnaries::selectValues('professions'),
                ])->render()
            ];
        } catch (Throwable $e) {
            $this->responseException($e);
            return $this->throwable();
        }
    }

    public function createAccountEmail(): array
    {
        try {

            $account = Account::findOrFail(request('account_id'));

            return [
                'title' => "Ajouter une adresse e-mail",
                'action' => __FUNCTION__,
                'view' => View::make('accounts.form.mail', [
                    'account' => $account,
                    'data' => new AccountMail(),
                ])
                    ->render()
            ];
        } catch (Throwable $e) {
            $this->responseException($e);
            return $this->throwable();
        }
    }

    public function default(): array
    {
        return [
            'title' => "Éditeur de contenu",
            'action' => __FUNCTION__,
            'view' => View::make('mfw-modals.default')->render()
        ];
    }

    private function throwable(): array
    {
        return [
            'title' => "Éditeur de contenu",
            'view' => View::make('mfw-modals.throwable', ['response' => $this->fetchResponse()])->render()
        ];
    }
}
