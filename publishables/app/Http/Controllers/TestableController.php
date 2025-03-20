<?php

namespace App\Http\Controllers;


use App\Accessors\EventContactAccessor;
use App\Accessors\EventManager\Availability;
use App\Accessors\Pec;
use App\Actions\Order\PecActions;
use App\Models\EventContact;
use App\Models\EventManager\Accommodation;
use App\Services\Availability\Interfaces\BlockedRoomRepository;
use App\Services\Availability\Interfaces\BookingRepository;
use App\Services\Availability\Interfaces\ContingentRepository;
use App\Services\Availability\Repositories\Eloquent\EloquentBlockedRoomRepository;
use App\Services\Availability\Repositories\Eloquent\EloquentBookingRepository;
use App\Services\Availability\Repositories\Eloquent\EloquentContingentRepository;
use App\Services\Availability\Services\AvailabilityCalculator;
use App\Services\Availability\Services\AvailabilityService;
use App\Services\Availability\ValueObjects\DateRange;
use App\Services\Pec\PecFinder;
use App\Services\Pec\PecParser;
use MetaFramework\Services\Validation\ValidationTrait;
use MetaFramework\Traits\DateManipulator;


class TestableController extends Controller
{
    use ValidationTrait;
    use DateManipulator;

    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'verified']);
    }

    public function index()
    {
        $this->testAvailability();
    }

    public function eventContact()
    {
        $id       = 140;
        $accessor = (new EventContactAccessor())->setEventContact($id);

        d($accessor->getOrdersWithRemainingPayments());
        d($accessor->getModel(), 'Model');
        d($accessor->getOrders(), 'Orders');
        d($accessor->getAssignedOrdersWithRemainingPayments(), 'Assigned Orders');
    }


    public function testNewAvailability()
    {
        request()->merge([
            'accommodation_id' => 36,
            // 'event_contact_id'   => 229,
            //  'participation_type' => '',
            //    'event_group_id'     => 36,
            //  'date'               => '2026-09-01',
            //'room_group_id'      => 57,
            //'entry_date' => '05/12/2024',
            //'out_date' => '06/12/2024',
        ]);

        $hotel = Accommodation::findOrFail(request('accommodation_id'));
        d($hotel->event_id);
        $dateRange = new DateRange();

        // Optional: Set date or date range
        // $dateRange->setDate('2026-09-01');
        // $dateRange->setStartDate('2024-12-05')->setEndDate('2024-12-06');



        $availability = AvailabilityService::for($hotel)
            ->forDateRange($dateRange);

        d($availability
            ->calculate()->map->toArray(), 'Final availability');
        d($availability->getAvailability(), 'Simple availability');
        d(Pec::getPecDistributedForHotelId($hotel->id), 'getGrantDistributedDetail');
    }


    public function testAvailability()
    {
        request()->merge(
            input: [
                'accommodation_id' => 52,
               //  'event_contact_id'   => 229,
                 // 'participation_type' => 4,
                  //  'event_group_id'     => 36,
                //  'date'               => '2026-09-01',
                //'room_group_id'      => 57,
                //'entry_date' => '05/12/2024',
                //'out_date' => '06/12/2024',
            ],
        );
        /*
                $this->availability = (new Availability())
                    ->setEventAccommodation(request('event_accommodation_id'))
                    ->setDate(request('date'))
                    ->setRoomGroupId((int)request('shoppable_id'))
                    ->setParticipationType((int)request('participation_type'))
                    ->setEventGroupId(request('account_type') == 'group' ? (int)request('event_group_id') : 0);
                */

        $availability = (new Availability())
            ->setEventAccommodation(request('accommodation_id'))
            //->setEventContact(request('event_contact_id'))
            //->setDateRange([('entry_date'), request('out_date')])
            //->setParticipationType((int)request('participation_type'))
            //->setDate((string)request('date'))
            //->setEventGroupId(request('account_type') == 'group' ? (int)request('event_group_id') : 0);
            //->setExcludeRoomsId(24)
            //->setDateRange([request('entry_date'), request('out_date')])
            // ->publishedRoomsOnly()
            //->setRoomGroupId((int)request('room_group_id'))
        ;


        // d($availability->isGrantDependable());

        // $ec = $availability->getEventContact();
        // d($ec->hasPaidGrantDeposit(), 'hasPaidGrantDeposit');
        // d($availability->getEventContact());


        // d($availability->get('contingent'), 'contingent');
        // d($availability->groupId(),'GroupId');
        //d($availability->baseGroupId(),'Base GroupId');
        //   d($availability->get('participation_type'), 'participation_type');
        //   d($availability->getAvailability(), 'Availability for ptype 11');
        //   d($availability->get('blocked'), 'Blocked for 11');
        d($availability->getAvailability(), 'Final availability');
        d($availability->getGrantDistributedDetail(), 'getGrantDistributedDetail');
        d($availability->getGrantDistributed(), 'getGrantDistributed');
        d($availability->getSummarizedData(), 'Summarized data');
        //d($availability->getRoomGroups(), 'RoomGroups');
        //d($availability->getRoomGroupAvailability(), 'getRoomGroupAvailability');
        // d($availability->getRoomConfigs(), 'Room Configs');
        /*     d(view()->make('events.manager.accommodation.inc.group_recap')->with([
                  'availability' => $availability,
                  'group_id' => $availability->groupId()
              ])->render());*/
    }


    private function decreaseAccommodationStock()
    {
        request()->merge([
            "action"                      => "decreaseAccommodationStock",
            "callback"                    => "resetAccommodationCartSelectablesStock",
            "shoppable_model"             => "App\\Models\\EventManager\\AccommodationAccessor\\RoomGroup",
            "shoppable_id"                => "21",
            "event_accommodation_id"      => "15",
            "room_id"                     => "24",
            "prevalue"                    => "1",
            "order_uuid"                  => "13552e97-eff5-4f1d-bc23-e4b5ca2d14dc",
            "quantity"                    => "2",
            "cart_id"                     => 201,
            'date'                        => '2024-03-20',
            "account_type"                => "contact",
            "account_id"                  => "109",
            "row_id"                      => "7zi09ywp0",
            'participation_type'          => 6,
            "shopping_cart_accommodation" => [
                "2024-03-20" => [
                    "date"           => [
                        "2024-03-20",
                    ],
                    "room_id"        => [
                        "26",
                    ],
                    "room_group_id"  => [
                        "21",
                    ],
                    "quantity"       => [
                        "1",
                    ],
                    "unit_price"     => [
                        "130",
                    ],
                    "price"          => [
                        "130",
                    ],
                    "price_ht"       => [
                        "108.33",
                    ],
                    "vat"            => [
                        "21.67",
                    ],
                    "event_hotel_id" => [
                        "15",
                    ],
                ],
            ],
        ]);


        $availability = (new Availability())
            ->setEventAccommodation(request('event_accommodation_id'))
            ->setDate(request('date'))
            ->setRoomGroupId((int)request('shoppable_id'))
            ->setParticipationType((int)request('participation_type'))
            ->setEventGroupId(request('account_type') == 'group' ? (int)request('account_id') : 0);


        d($availability->get('contingent'), 'contingent');
        d($availability->getSummarizedData(), 'Summarized data');
        d($availability->getRoomGroups(), 'RoomGroups');
        d($availability->getRoomConfigs(), 'Room Configs');
        d($availability->getAvailability(), 'Availability');
        /*
          d(
              (new ContingentActions())->decreaseStock(), 'decreaseAccommodationStock'
          );
        */
    }


    public function fetchAccommodationForEvent()
    {
        request()->merge([
            'action'             => 'fetchAccommodationForEvent',
            'callback'           => 'showAccommodatioRecap',
            'event_hotel_id'     => 26,
            'entry_date'         => '12/06/2024',
            'out_date'           => '15/06/2024',
            'account_type'       => 'contact',
            'pec'                => 1,
            'account_id'         => 55,
            'event_group_id'     => 0,
            "participation_type" => 4,
        ]);

        d(
            (new \App\Actions\Order\OrderAccommodationActions())->fetchAccommodationForEvent(),
            'fetchAccommodationForEvent',
        );
    }

    private function testPecParser()
    {/*
        $order = Order::find(271);

        $orderPecCount = $order->pecDistributions->count();

        de($order->pecDistributions->first()->type == PecType::PROCESSING_FEE->value);
        if ($orderPecCount == 0) {
            $order->pecQuota()->delete();
        } else {
            if ($orderPecCount < 2 && $order->pecDistributions->first()->type == PecType::PROCESSING_FEE) {
                $order->pecDistributions()->delete();
                $order->pecQuota()->delete();
            }
        }

        exit;
*/
        $contact_id = 329;
        $contact    = EventContact::find($contact_id);

        $pec = new PecParser($contact->event, collect()->push($contact));
        $pec->trackFailures();
        $pec->calculate();

        d($pec->grantParser->fetchAvailableGrants(), 'fetchAvailableGrants');
        /*
        if ($pec->hasGrants($contact_id)) {
            $pecFinder = new PecFinder();
            $pecFinder->setEventContact($contact);
            $pecFinder->setServices([15 => 800, 16 => 500]);
            $pecFinder->setGrants($pec->getGrantsFor($contact_id));
            //$pecFinder->setAccommodationTotal(500);//OrderRequestAccessor::getTotalAccommodationPecFromRequest()
            $pecFinder->askForProcessingFees(true);


            $pecDistrubutionResult = $pecFinder->filterGrants();

            d($pecDistrubutionResult, 'filterGrants of Pec Finder');
        }
        */
    }


    private function fullPecOrderTest()
    {
        //$event = Event::find(75);//->load('contacts.profile','contacts.address');
        $contact_id = 163;
        $contact    = EventContact::find($contact_id);


        //$pec = new PecParser($event, $event->contacts);
        $pec = new PecParser($contact->event, collect()->push($contact));
        $pec->trackFailures();
        $pec->calculate();

        d($pec->grantParser->fetchAvailableGrants(), 'fetchAvailableGrants');

        d($pec->hasGrants($contact_id), 'hasGrants');
        d($pec->getEligibilitySummary(), 'getEligibilitySummary');
        //d($pec->getEligibilityFailures(), 'getEligibilityFailures');
        //d($pec->getEligibleGrants(),'getEligibleGrants');
        //d($pec->getGrantsFor($contact_id), 'getGrantsFor');


        if ($pec->hasGrants($contact_id)) {
            $pecFinder = new PecFinder();
            $pecFinder->setEventContact($contact);
            $pecFinder->setServices([56 => 500]);
            $pecFinder->setGrants($pec->getGrantsFor($contact_id));
            //$pecFinder->setAccommodationTotal(500);//OrderRequestAccessor::getTotalAccommodationPecFromRequest()
            $pecFinder->askForProcessingFees(true);


            $pecDistrubutionResult = $pecFinder->filterGrants();

            //  d($pecDistrubutionResult, 'filterGrants of Pec Finder');

            // de($pecDistrubutionResult->getProcessingFee(),'getProcessingFee');

            de($pec->getEligibilityFor($contact_id, 33), 'getEligibilityFor');

            $actions = (new PecActions());
            $actions->setEventContact($contact);
            $actions->setEvent($contact->event);
            // $actions->setOrder(Order::find(246));
            $actions->setPecParser($pec);
            $actions->setPecDistributionResult($pecDistrubutionResult);

            d($actions->quotaMatches());

            $actions->registerPecDistributionResult();
            $actions->registerQuotas();

            d($actions->fetchResponse());
        }
    }


}

