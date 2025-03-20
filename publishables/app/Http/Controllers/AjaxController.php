<?php


namespace App\Http\Controllers;

use App\Accessors\{Accounts, Dictionnaries, EventContactAccessor, Geo, PlaceRooms, Places, Users};
use App\Actions\{Account\AssociateUsersToEventAction,
    Account\AssociateUsersToGroupAction,
    Account\EventContactActions,
    Account\ExportAccountProfilesWrapperAction,
    Account\SendConnexionMailAction,
    Account\UpdateAccountProfileAction,
    Dictionnary\AddEntryInSimpleDictionnary,
    Dictionnary\AddProfession,
    EstablishmentActions,
    GroupContactActions,
    Groups\AssociateGroupsToEventAction,
    Groups\ExportGroupsWrapperAction,
    Groups\MakeMainContactAction,
    Order\ContingentActions,
    Order\OrderAccommodationActions,
    Order\OrderActions,
    Order\OrderInvoiceableActions,
    Order\OrderNoteActions,
    Order\OrderServiceActions,
    Order\ServiceCartActions,
    Order\StockActions,
    PlaceActions,
    SellableActions,
    SellableDeposit\MakeInvoiceForEventDepositAction,
    SellableDeposit\ReimburseEventDepositAction};
use App\Actions\Account\Ajax\CreateAccountEmail;
use App\Actions\Account\Order\OrderInvoiceCancelAction;
use App\Actions\Account\Order\OrderPaymentAction;
use App\Actions\Account\Search\SavedSearchAction;
use App\Actions\Account\Search\Select2Accounts;
use App\Actions\Account\Search\Select2ParticipantsAction;
use App\Actions\EventManager\{Accommodation, Accommodation\BlockGroupRooms, EventAssociator, EventContact\GetUserInfo, EventGroup\AssociateEventContactToEventGroupAction, EventGroup\AssociateUserToEventGroupAction, EventGroup\DissociateUserFromEventGroupAction, GrantActions, HotelAssociate, HotelSearch, Invitation\CreateInvitationAction, Program\GetSessionInfoAction, Program\MoveProgramThingAction, Program\ProgramDayRoomsAction, SellableService};
use App\Actions\Front\Cart\FrontCartActions;
use App\Actions\Front\Grant\AddGrantWaiverFeesToCart;
use App\Actions\Front\Group\CreateGroupMemberFromEmailAction;
use App\Actions\Front\Group\DissociateUserFromMyEventGroupAction;
use App\Actions\Front\Paybox\PayboxActions;
use App\Actions\Groups\Search\Select2Groups;
use App\Http\Controllers\Dev\ArtisanController;
use App\Http\Controllers\Testable\Availability;
use App\Models\{CustomPaymentCall, Establishment, Order\Accompanying, Order\RoomNote, Setting};
use App\Models\EventManager\Grant\GrantDepositLocation;
use App\Models\EventManager\Grant\GrantLocation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MetaFramework\Services\Validation\ValidationTrait;
use MetaFramework\Traits\Ajax;
use ReflectionClass;
use ReflectionException;
use Throwable;

class AjaxController extends Controller
{
    use Ajax;
    use ValidationTrait;

    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
        $this->enableAjaxMode();
    }

    public function updateAdminAdressSettings(): array
    {
        try {
            Setting::updateOrCreate(['name' => 'admin_shared_address'], ['value' => (string)request('data')]);
            $this->responseSuccess(__('ui.record_updated'));
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();
    }

    /**
     * Associe un compte client à un group
     *
     * @return array
     */
    protected function addAccountToGroup(): array
    {
        return (new GroupContactActions(account_id: (int)request('id'), group_id: (int)request('object_id')))
            ->enableAjaxMode()
            ->associate()
            ->fetchResponse();
    }

    /**
     * Dissocie un compte d'un groupe
     *
     * @return array
     */
    protected function removeAccountFromGroup(): array
    {
        return (new GroupContactActions(account_id: (int)request('id'), group_id: (int)request('object_id')))
            ->enableAjaxMode()
            ->dissociate()
            ->fetchResponse();
    }

    protected function associateUsersToGroupByEventContact(): array
    {
        return (new AssociateUsersToGroupAction())->associateUsersToGroupByEventContact();
    }


    protected function associateEventContactToEventGroup(): array
    {
        return (new AssociateEventContactToEventGroupAction())->associateEventContactToEventGroup();
    }

    protected function associateEventContactsToEventGroup(): array
    {
        return (new AssociateEventContactToEventGroupAction())->associateEventContactsToEventGroup(explode(',', request('ids')));
    }

    protected function associateUserToEventGroup(): array
    {
        return (new AssociateUserToEventGroupAction())->associateUserToEventGroup();
    }

    protected function dissociateUserFromMyEventGroup(): array
    {
        return (new DissociateUserFromMyEventGroupAction())->dissociate();
    }

    protected function dissociateUserFromEventGroup(): array
    {
        return (new DissociateUserFromEventGroupAction())->dissociate();
    }

    public function makeMainContactOfTheEventGroup(): array
    {
        return (new MakeMainContactAction())->makeMainContactOfEventGroup();
    }

    public function sendConnexionMailToEventGroupMainContact()
    {
        return (new SendConnexionMailAction())->sendToEventGroupMainContact();
    }


    /**
     * Associe un compte client à un évènement
     *
     * @return array
     */
    protected function addAccountToEvent(): array
    {
        return (new EventContactActions())
            ->enableAjaxMode()
            ->setAccount((int)request('id'))
            ->setEvent((int)request('object_id'))
            ->enableAjaxMode()
            ->associate()
            ->fetchResponse();
    }

    protected function associateUserToEvent(): array
    {
        $action = (new EventContactActions())
            ->enableAjaxMode()
            ->setAccount((int)request('user_id'))
            ->setEvent((int)request('event_id'))
            ->setParticipationTypeId([
                'participation_type_id' => request('participation_type_id'),
            ])
            ->associate();


        if ($action->getEventContact()) {
            $action->pushMessages(
                (new GrantActions())->enableAjaxMode()->updateEligibleStatusForSingleContact($action->getEventContact()->event, $action->getEventContact()),
            );
        }

        return $action->fetchResponse();
    }

    /**
     * Dissocie un client d'un évènement
     *
     * @return array
     */
    protected function removeAccountFromEvent(): array
    {
        return (new EventContactActions())
            ->enableAjaxMode()
            ->setAccount((int)request('id'))
            ->setEvent((int)request('object_id'))
            ->dissociate()
            ->fetchResponse();
    }


    protected function searchClientBase(): array
    {
        $this->response['callback']  = request('callback');
        $this->response['container'] = request('container');
        $eventGroupId                = request('event_group');
        $this->response['items']     = Accounts::searchByKeyword(request('keyword'), [
            'event_group_id' => $eventGroupId,
        ]);

        return $this->fetchResponse();
    }

    protected function searchEstablishmentBase(): array
    {
        $this->response['callback']  = request('callback');
        $this->response['container'] = request('container');

        $collection = Establishment::query()
            ->where('name', 'like', '%'.request('keyword').'%')
            ->get();

        $this->response['items'] = $collection->map(
            fn($i)
                => array_merge(
                $i->toArray(),
                ['route' => route('panel.establishments.edit', $i)],
            ),
        )->toArray();

        return $this->fetchResponse();
    }

    /**
     * Statut de publication d'un objet utilisant le trait OnlineStatus
     */
    protected function publishedStatus(): array
    {
        $result = [];
        if (request()->filled('class') && request()->filled('id') && class_exists(request('class'))) {
            $class                                  = request('class');
            $object                                 = new $class();
            $object                                 = $object->find(request('id'));
            $object->{$object->onlineStatusField()} = (request('from') == $object->onlineStatusOpen() ? 0 : 1);
            $object->save();
            $result['success'] = 1;
        } else {
            $result['error'] = 1;
        }

        return $result;
    }

    /**
     * Fonction générique drag&drop sur des éléments ayant la classe '.sortable"
     */
    protected function sortable(): array
    {
        $targets = ['meta', 'nav'];

        if (in_array(request('target'), $targets) && request()->filled('data')) {
            DB::beginTransaction();
            foreach (request('data') as $item) {
                DB::table(request('target'))->where('id', $item['id'])->update(['position' => $item['index']]);
            }
            DB::commit();
            $this->responseSuccess("L'ordre a été mis à jour");

            return $this->fetchResponse();
        }

        return [];
    }

    /**
     * Ajoute une entrée depuis l'UI simplifié JS dans Contacts
     *
     * @return array
     */
    protected function addDictionnaryEntry(): array
    {
        return (new AddEntryInSimpleDictionnary())();
    }

    /**
     * Créé une adresse e-mail depuis un modal
     *
     * @return array
     */
    public function createAccountEmail(): array
    {
        return (new CreateAccountEmail())();
    }


    /**
     * Ajoute une profession depuis un modal
     *
     * @return array
     */
    public function createProfession(): array
    {
        $profession = new AddProfession();

        if (request()->has('optgroup')) {
            return $profession->addSubEntry();
        }

        return $profession->addEntry();
    }

    /**
     * Ajoute une profession depuis un modal
     *
     * @return array
     */
    public function createEstablishment(): array
    {
        return (new EstablishmentActions())->create();
    }

    /**
     * Ajoute un lieu depuis un modal
     *
     * @return array
     */
    public function createPlace(): array
    {
        return (new PlaceActions())->create();
    }

    /**
     * Ajoute une salle depuis un modal
     *
     * @return array
     */
    public function createPlaceRoom(): array
    {
        return (new PlaceActions())->createRoomFroModal();
    }

    /**
     * Ajoute une profession depuis un modal
     *
     * @return array
     */
    public function sellableByEvent(): array
    {
        return (new SellableActions())->attachToEvent(event_id: request('event_id'), sellable_id: request('sellable_id'));
    }

    /**
     * Ajoute une profession depuis un modal
     *
     * @return array
     */
    public function removeSellableCustomization(): array
    {
        return (new SellableActions())->removeCustomization(event_id: request('event_id'), sellable_id: request('sellable_id'));
    }

    /**
     * @throws ReflectionException
     */
    public function datatableMassDelete(Request $request): array
    {
        $response       = [];
        $controllerPath = $request->get('controller_path', $request->get('model').'Controller');

        try {
            $class = (new ReflectionClass('\App\Http\Controllers\\'.$controllerPath))->newInstance();
            if (method_exists($class, 'massDelete')) {
                $response = $class->massDelete($request, name: ($request->get('name') ?: 'name'));
            } else {
                $this->responseWarning("La suppression par sélection n'est pas autorisée.");
            }
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return array_merge($this->fetchResponse(), $response);
    }

    /**
     * Recherche des hôtels à associer à un évènement
     *
     * @return array [items, event_id, callback]
     */
    public function eventHotelSearch(): array
    {
        return (new HotelSearch(request('keyword'), (int)request('event_id')))->find();
    }

    public function hotelSearch(): array
    {
        return (new \App\Actions\Hotels\HotelSearch(request('keyword')))->find();
    }

    public function placeSearch(): array
    {
        return (new PlaceActions(request('keyword')))->find();
    }

    /**
     * @return array [response messages]
     */
    public function eventHotelAssociate(): array
    {
        return (new HotelAssociate((int)request('hotel_id'), (int)request('event_id')))->associate();
    }

    public function removeAccommodationRoom(): array
    {
        return (new Accommodation())->deleteRoom((int)request('id'));
    }

    public function removeRoomGroup(): array
    {
        return (new Accommodation())->deleteGroup((int)request('id'));
    }

    public function removeContingentRow(): array
    {
        return (new Accommodation())->deleteContingentRow((int)request('id'));
    }

    public function removeBlockedRow(): array
    {
        return (new Accommodation())->deleteBLocked((int)request('id'));
    }

    public function removeBlockedGroupRow(): array
    {
        return (new Accommodation())->deleteBLockedGroupRoom((int)request('id'));
    }

    public function removeGrantRow(): array
    {
        return (new Accommodation())->deleteGrant((int)request('id'));
    }

    public function eventAssociator(): array
    {
        return (new EventAssociator(
            type: request('type'),
            event_id: (int)request('event_id'),
            ids: explode(',', request('ids')),
        ))->associate();
    }

    public function removeTimeBindedPriceRow(): array
    {
        return (new SellableService())->deletePrice((int)request('id'));
    }

    public function removePlaceRoomSetup(): array
    {
        return (new PlaceActions())->removePlaceRoomSetup((int)request('id'));
    }

    public function removeSellableServiceOptionRow(): array
    {
        return (new SellableService())->deleteOption((int)request('id'));
    }

    public function removeGrantBindedLocationRow(): array
    {
        try {
            $model = match (request('model')) {
                'GrantLocation' => new GrantLocation(),
                default => new GrantDepositLocation()
            };

            $model->newQuery()->where('id', (int)request('id'))->delete();
            $this->responseElement('callback', 'ajaxPostDeleteGrantBindedLocationRow');
            $this->responseSuccess("L'association géographique a été supprimée.");
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();
    }


    public function getProgramDayRooms(): array
    {
        return (new ProgramDayRoomsAction())->getProgramDayRooms();
    }

    public function getEstablishments(): array
    {
        try {
            return (new EstablishmentActions())->getEstablishments();
        } catch (Throwable $e) {
            $this->responseException($e);

            return $this->fetchResponse();
        }
    }

    public function getEstablishmentsForCountry(): array
    {
        try {
            return (new EstablishmentActions())->getEstablishmentsForCountry(country_code: (string)request('country'));
        } catch (Throwable $e) {
            $this->responseException($e);

            return $this->fetchResponse();
        }
    }

    public function getEstablishmentsForLocality(): array
    {
        try {
            return (new EstablishmentActions())->getEstablishmentsForLocality(country_code: request('country'), locality: request('locality'));
        } catch (Throwable $e) {
            $this->responseException($e);

            return $this->fetchResponse();
        }
    }

    public function moveProgramThing()
    {
        return (new MoveProgramThingAction())->moveByArrow();
    }

    public function moveProgramThingBySwap()
    {
        return (new MoveProgramThingAction())->moveBySwap();
    }

    public function searchParticipants(): array
    {
        $eventId                 = request('eventId');
        $search                  = request('q', "");
        $this->response['items'] = EventContactAccessor::getSearchResultsByEventId($eventId, $search);

        return $this->fetchResponse();
    }

    public function select2InterventionParticipantsInfo(): array
    {
        return (new Select2ParticipantsAction())->getParticipantsByEventIdInterventionId(
            request('q'),
            request('event_id'),
            request('intervention_id'),
            request('alreadySelectedOratorIds', []),
            'orator',
        );
    }

    public function select2InterventionModeratorsInfo(): array
    {
        return (new Select2ParticipantsAction())->getParticipantsByEventIdInterventionId(
            request('q'),
            request('event_id'),
            request('intervention_id'),
            request('alreadySelectedModeratorIds', []),
            'moderator',
        );
    }

    public function select2Accounts(): array
    {
        return (new Select2Accounts())->filterAccounts(request('q'));
    }

    public function select2Groups(): array
    {
        return (new Select2Groups())->filter(request('q'));
    }


    public function select2Hotels(): array
    {
        return (new HotelSearch(request('q', ''), (int)request('event_id')))->find([
            'select'      => ['hotels.id', 'hotels.name as text'],
            'useCallback' => false,
            'itemsKey'    => "results",
        ]);
    }


    public function searchPlaces(): array
    {
        $search                  = request('q');
        $this->response['items'] = Places::getSearchResults($search);

        return $this->fetchResponse();
    }

    public function searchUsers(): array
    {
        $search                  = request('q');
        $this->response['items'] = Users::getSearchResults($search);

        return $this->fetchResponse();
    }

    public function selectDictionaryEntries(): array
    {
        $dictionarySlug          = request('dictionary_slug');
        $this->response['items'] = Dictionnaries::selectValues($dictionarySlug);

        return $this->fetchResponse();
    }

    public function getRoomsByPlaceId(): array
    {
        $this->response['items'] = PlaceRooms::selectableArray(request('id'));

        return $this->fetchResponse();
    }

    public function getPlaceIdRoomIdPlaceRoomsSelectableByEventProgramDayRoomId(): array
    {
        return (new ProgramDayRoomsAction())->getPlaceIdRoomIdPlaceRoomsSelectableByEventProgramDayRoomId();
    }

    public function associateUsersToEvent(): array
    {
        return (new AssociateUsersToEventAction())->associateUsersToEvent();
    }

    public function associateUsersToEventByEventContact(): array
    {
        return (new AssociateUsersToEventAction())->associateUsersToEventByEventContact();
    }

    public function associateGroupsToEvent(): array
    {
        return (new AssociateGroupsToEventAction())->associateGroupsToEvent();
    }

    public function getSessionInfo(): array
    {
        return (new GetSessionInfoAction())->getSessionInfo((int)request('session_id'));
    }

    //--------------------------------------------
    //
    //--------------------------------------------

    public function updateAccountProfiles(): array
    {
        return (new UpdateAccountProfileAction())->updateAccountProfiles();
    }

    public function updateAccountProfilesByEventContacts(): array
    {
        return (new UpdateAccountProfileAction())->updateAccountProfilesByEventContacts();
    }

    public function exportAccountProfiles(): array
    {
        return (new ExportAccountProfilesWrapperAction())->exportAccountProfiles();
    }

    public function exportAccountProfilesByEventContact(): array
    {
        return (new ExportAccountProfilesWrapperAction())->exportAccountProfilesByEventContacts();
    }

    public function exportGroups(): array
    {
        return (new ExportGroupsWrapperAction())->exportGroups();
    }


    public function storeSavedSearchInSession()
    {
        return (new SavedSearchAction())->storeCurrentSearchInSession(request('type'), request('search_filters'));
    }


    public function saveSavedSearch(): array
    {
        return (new SavedSearchAction())->storeSavedSearch(request('name'), request('type'), session("savedSearch.".request('type').".filters"), request('id'));
    }

    public function loadSavedSearch(): array
    {
        return (new SavedSearchAction())->loadSavedSearch(request('id'), request('type'));
    }

    public function deleteSavedSearch(): array
    {
        return (new SavedSearchAction())->deleteSavedSearch(request('type'), request('id'));
    }


    public function saveOrderPayment(): array
    {
        return (new OrderPaymentAction())->savePayment(request()->all(), (int)request("id"));
    }

    public function deleteOrderPayment(): array
    {
        return (new OrderPaymentAction())->deletePayment(request('id'));
    }

    public function saveOrderInvoiceCancel(): array
    {
        return (new OrderInvoiceCancelAction())->save(request()->all(), request("id"));
    }

    public function makeInvoice(): array
    {
        return (new InvoiceController())->enableAjaxMode()->fetchCallback()->store()->fetchResponse();
    }

    public function deleteOrderInvoice(): array
    {
        return (new OrderInvoiceCancelAction())->delete(request('id'));
    }


    # Panel > EventManager > Order > Accommodation selector
    public function fetchAccommodationForEvent(): array
    {
        return (new OrderAccommodationActions())->fetchAccommodationForEvent();
    }

    # Panel > EventManager > Order Edit > Accommodation Cart > Remove
    public function removeAccommodationCartRow(): array
    {
        return (new OrderAccommodationActions())->removeAccommodationCartRow();
    }

    # Panel > EventManager > Order Edit > Accommodation Cart > Remove
    public function clearAccommodationTempStock(): array
    {
        return (new ContingentActions())->clearTempStock();
    }

    # Panel > EventManager > Order Edit > ServiceCart Cart > Remove
    public function removeServicePriceRow(): array
    {
        return (new ServiceCartActions())->enableAjaxMode()->removeServicePriceRow();
    }


    public function cancelServicePriceRow(): array
    {
        return (new ServiceCartActions())->ajaxMode()->cancelServicePriceRow();
    }

    public function orderSelectAccountForAssignment(): array
    {
        $this->pushMessages(
            (new OrderInvoiceableActions())->fetchPayerFromDatabase(),
        );

        return $this->fetchResponse();
    }

    public function decreaseShoppableStock(): array
    {
        return (new StockActions())->decreaseShoppableStock();
    }

    public function increaseShoppableStock(): array
    {
        return (new StockActions())->increaseShoppableStock();
    }

    public function decreaseAccommodationStock(): array
    {
        return (new ContingentActions())->decreaseStock();
    }

    public function increaseAccommodationStock(): array
    {
        return (new ContingentActions())->increaseStock();
    }

    public function addOrderNote(): array
    {
        return (new OrderNoteActions())->addNote();
    }

    public function removeOrderNote(): array
    {
        return (new OrderNoteActions())->removeNote();
    }

    public function update_serviceOrderAttributions(): array
    {
        return (new OrderServiceActions())->updateServiceAttributions();
    }

    public function removeServiceAttribution(): array
    {
        return (new OrderServiceActions())->removeServiceAttribution();
    }

    public function removeAccommodationAttribution(): array
    {
        return (new OrderAccommodationActions())->removeAccommodationAttribution();
    }

    public function removeFrontAccommodationAttribution(): array
    {
        return (new OrderAccommodationActions())->removeFrontAccommodationAttribution();
    }

    public function removeFrontServiceAttribution(): array
    {
        return (new OrderServiceActions())->removeFrontServiceAttribution();
    }

    public function update_accommodationOrderAttributions(): array
    {
        return (new OrderAccommodationActions())->updateRoomAttributions();
    }

    public function saveBlockedGroupRoomsForEvent(): array
    {
        return (new BlockGroupRooms((int)request('event_group_id')))->enableAjaxMode()->process();
    }

    public function fetchEventAccommodationRecapForGroup(): array
    {
        $availability = (new \App\Accessors\EventManager\Availability())
            ->setEventAccommodation((int)request('event_accommodation_id'))
            ->setEventGroupId((int)request('group_id'));

        $this->responseElement(
            'html',
            view()->make('events.manager.accommodation.inc.group_recap')->with([
                'availability' => $availability,
                'group_id'     => $availability->groupId(),
            ])->render(),
        );

        return $this->fetchResponse();
    }

    // Retourne la dispo depuis l'UI de blocage de chambre groupes Event Manager > Participation > Groupes
    public function controlHotelAvailability(): array
    {
        $availability = (new \App\Accessors\EventManager\Availability())
            ->setEventAccommodation((int)request('event_accommodation_id'))
            ->setDate((string)request('date'))
            ->setRoomGroupId((string)request('roomgroup'))
            ->setEventGroupId((int)request('event_group_id'));

        $this->responseElement('availability', $availability->getRoomGroupAvailability());

        return $this->fetchResponse();
    }

    // Affiche une liste de salles rattachées à un lieu lors de la config prestation pour un évent
    public function fetchPlaceRoomForPlace(): array
    {
        $rooms = Places::selectableRoomsForPlace((int)request('place_id'));

        $this->responseElement('rooms', $rooms);
        if ( ! $rooms) {
            $this->responseWarning("Aucune salle n'est disponible pour ce lieu");
        }

        return $this->fetchResponse();
    }


    public function frontCartAddService(): array
    {
        return (new FrontCartActions())
            ->addService(
                (int)request('service_id'),
                (int)request('quantity'),
                (bool)request('force'),
            );
    }

    public function frontCartUpdateServiceQuantity(): array
    {
        return (new FrontCartActions(request('event_id')))
            ->updateServiceQuantity(
                request('service_id'),
                request('quantity'),
                (bool)request('force'),
            );
    }


    public function createInvitation(): array
    {
        return (new CreateInvitationAction())->createInvitation();
    }

    /**
     * @throws Exception
     */
    public function sendOrderCartCancellationRequest(): array
    {
        return (new OrderActions())->ajaxMode()->cancelCartItem();
    }

    public function sendDeclinetVenutRequest(): array
    {
        return (new OrderActions())->ajaxMode()->declineVenue();
    }

    public function sendOrderCancellationRequest(): array
    {
        return (new OrderActions())->ajaxMode()->cancelOrder();
    }

    public function removeAccompanyingRow(): array
    {
        try {
            Accompanying::where('id', (int)request('id'))->delete();
            $this->responseSuccess("La ligne a été supprimée.");
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();
    }

    public function removeRoomNotesRow(): array
    {
        try {
            RoomNote::where('id', (int)request('id'))->delete();
            $this->responseSuccess("La ligne a été supprimée.");
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();
    }

    public function removeTaxRoomRow(): array
    {
        return (new OrderAccommodationActions())->removeTaxRoomCartRow();
    }

    public function getUserInfoByEventEmail(): array
    {
        return (new GetUserInfo())->getUserInfoByEventEmail();
    }

    public function testableAvailabilityHotels(): array
    {
        $this->responseElement('hotels', Availability::fetchHotels((int)request('event')));
        $this->responseElement('subjects', Availability::fetchSubjects((int)request('event')));

        return $this->fetchResponse();
    }

    public function testableAvailabilityFetch(): array
    {
        return (new Availability())->ajaxMode()->fetch((int)request('hotel'));
    }

    public function loadPecWaiverInCart()
    {
        return (new AddGrantWaiverFeesToCart())->addGrantWaiverFeesToCart();
    }

    public function sendInvoiceFromModal(): array
    {
        return (new MailController())->ajaxMode()->distribute('invoice', (string)request('uuid'))->fetchResponse();
    }

    public function sendRefundFromModal(): array
    {
        return (new MailController())->ajaxMode()->distribute('refundable', (string)request('uuid'))->fetchResponse();
    }


    public function reimburseEventDeposit()
    {
        return (new ReimburseEventDepositAction())->reimburseEventDeposit();
    }

    public function makeInvoiceForEventDeposit()
    {
        return (new MakeInvoiceForEventDepositAction())->makeInvoiceForEventDeposit();
    }

    public function createGroupMemberFromMail()
    {
        return (new CreateGroupMemberFromEmailAction())->create();
    }

    public function getPayboxForm()
    {
        return (new PayboxActions())->getPayboxFormByOrder();
    }

    public function getContinentCodeByCountryCode(): array
    {
        $this->responseElement('continent', Geo::getContinentFromRequest());

        return $this->fetchResponse();
    }

    public function artisanOptimize(): array
    {
        return (new ArtisanController())->enableAjaxMode()->optimizeClear();
    }

    # Envoi le mail pour demander le paiment d'une caution Grant
    public function sendEventDepositPaymentMail(): array
    {
        return CustomPaymentCall::findOrFail((int)request('payment_call_id'))->ajaxMode()->sendPaymentMail();
    }
}
