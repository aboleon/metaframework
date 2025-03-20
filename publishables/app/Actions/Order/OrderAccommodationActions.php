<?php

namespace App\Actions\Order;

use App\Accessors\EventManager\Availability;
use App\Accessors\GroupAccessor;
use App\Accessors\OrderAccessor;
use App\Enum\OrderCartType;
use App\Enum\OrderClientType;
use App\Enum\OrderOrigin;
use App\Models\Event;
use App\Models\EventManager\Accommodation;
use App\Models\Order;
use App\Models\Order\Cart\AccommodationAttribution;
use App\Models\Order\Cart\AccommodationCart;
use App\Models\Order\Cart\TaxRoomCart;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Validation\Rules\Enum;
use MetaFramework\Accessors\VatAccessor;
use MetaFramework\Services\Validation\ValidationTrait;
use MetaFramework\Traits\Ajax;
use Throwable;
use View;

class OrderAccommodationActions
{
    use Ajax;
    use ValidationTrait;

    public function __construct()
    {
        $this->ajaxMode();
    }


    public function removeAccommodationAttribution(): array
    {
        try {
            $model = AccommodationAttribution::findOrfail((int)request('id'));
            $this->responseElement('model', $model);
            $this->responseElement('to_restore', $model->quantity);
            $model->delete();
            $this->responseSuccess("L'attribution a été supprimée");
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();
    }

    public function removeFrontAccommodationAttribution(): array
    {
        request()->validate([
            'roomId'         => ['required', 'integer', 'exists:order_attributions,shoppable_id'],
            'eventContactId' => ['required', 'integer', 'exists:order_attributions,event_contact_id'],
            'date'           => ['required', 'date', 'date_format:Y-m-d'],
            'qty'            => ['required', 'integer', 'min:1'],
        ]);

        try {
            $this->deleteFrontAccommodationAttributionRecords(
                request('roomId'),
                request('eventContactId'),
                request('date'),
                request('qty'),
            );
            $this->responseSuccess(__('mfw.record.deleted'));
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();
    }

    public function updateRoomAttributions(): array
    {
        $groupAccessor = new GroupAccessor(request('group_id'));
        $groupAccessor->setEvent(request('event_id'));

        $event    = Event::find(request('event_id'));
        $origin   = request('origin');
        $order_id = (int)request('order_id');

        // Ca vient du front, il faut se préparer à recouper
        if ($origin == OrderOrigin::FRONT->value) {
            $ordered = $groupAccessor->stockAccommodationQuery();
        }

        try {
            $data = [];

            for ($i = 0; $i < count(request('service_id')); ++$i) {
                $quantity         = (int)request('quantity.'.$i);
                $date             = request('date.'.$i);
                $room_id          = request('service_id.'.$i);
                $event_contact_id = request('event_contact_id.'.$i);
                $identifier       = request('identifier.'.$i) ?? '';

                if ($origin == OrderOrigin::FRONT->value) {
                    $attributed = $event->accommodationAttributions
                        ->filter(fn($a) => $a->shoppable_id == $room_id && $a->attributes['date'] == $date)
                        ->groupBy('order_id')
                        ->mapWithKeys(fn($group, $orderId)
                            => [
                            $orderId => $group->sum('quantity'),
                        ])->toArray();

                    $ordersForAttribution = $this->allocateOrders($ordered, $attributed, $quantity, $date);

                    if (count($ordersForAttribution) < 2) {
                        $order_id         = key($ordersForAttribution);
                        $record           = AccommodationAttribution::create(
                            $this->pushAttrubutionData(
                                $order_id,
                                $room_id,
                                $quantity,
                                $event_contact_id,
                                $date,
                            ),
                        );
                        $data[$room_id][] = [
                            'member_id'     => $event_contact_id,
                            'date'          => $date,
                            'date_formated' => Carbon::createFromFormat('Y-m-d', $date)->format('d/m/Y'),
                            'id'            => $record->id,
                            'qty'           => $quantity,
                            'identifier'    => $identifier,
                        ];
                    } else {
                        foreach ($ordersForAttribution as $order_id => $quantity) {
                            $record           = AccommodationAttribution::create(
                                $this->pushAttrubutionData(
                                    $order_id,
                                    $room_id,
                                    $quantity,
                                    $event_contact_id,
                                    $date,
                                ),
                            );
                            $data[$room_id][] = [
                                'member_id'     => $event_contact_id,
                                'date'          => $date,
                                'date_formated' => Carbon::createFromFormat('Y-m-d', $date)->format('d/m/Y'),
                                'id'            => $record->id,
                                'qty'           => $quantity,
                                'identifier'    => $identifier,
                            ];
                        }
                    }
                } else {
                    $record           = AccommodationAttribution::create([
                        'order_id'         => $order_id,
                        'shoppable_id'     => $room_id,
                        'shoppable_type'   => OrderCartType::ACCOMMODATION->value,
                        'quantity'         => $quantity,
                        'event_contact_id' => $event_contact_id,
                        'assigned_by'      => auth()->id(),
                        'attributes'       => ['date' => $date],
                    ]);
                    $data[$room_id][] = [
                        'member_id'     => $event_contact_id,
                        'date'          => $date,
                        'date_formated' => Carbon::createFromFormat('Y-m-d', $date)->format('d/m/Y'),
                        'id'            => $record->id,
                        'qty'           => $quantity,
                        'identifier'    => $identifier,
                    ];
                }
            }

            $this->responseElement('stored', $data);
            $this->responseElement('callback', 'postCreateAccommodationAttributions');
            $this->responseElement('type', 'accommodation');
            $this->responseElement('affected_date', now()->format('m/d/Y'));

            $this->responseSuccess("Les attributions ont été mises à jour.");
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();
    }

    public function fetchAccommodationForEvent(): array
    {
        $this->validation_rules    = [
            'entry_date'     => 'bail|required|date_format:d/m/Y',
            'out_date'       => 'bail|required|date_format:d/m/Y|after:entry_date',
            'event_hotel_id' => 'bail|required|exists:event_accommodation,id',
            'account_type'   => [new Enum(OrderClientType::class)],
            'account_id'     => 'required|integer',
        ];
        $this->validation_messages = [
            'event_hotel_id.required' => __('validation.required', ['attribute' => "L'ID de l'hébergement"]),
            'event_hotel_id.exists'   => __('validation.exists', ['attribute' => "L'hébergement indiqué"]),
            'entry_date.required'     => __('validation.required', ['attribute' => "La date d'arrivée"]),
            'out_date.required'       => __('validation.required', ['attribute' => "La date d'arrivée"]),
            'out_date.after'          => __('validation.after', ['attribute' => "La date de départ", 'date' => "date d'arrivée"]),
            'entry_date.date_format'  => __('validation.date_format', ['attribute' => "La date d'arrivée", 'format' => 'd/m/Y']),
            'out_date.date_format'    => __('validation.date_format', ['attribute' => "La date d'arrivée", 'format' => 'd/m/Y']),
            'account_id.required'     => __('validation.required', ['attribute' => "Le compte d'affectation"]),
        ];

        $this->validation();

        $accommodation = Accommodation::query()->find((int)request('event_hotel_id'));


        $availability = (new Availability())
            ->setEventAccommodation($accommodation)
            ->setDateRange([request('entry_date'), request('out_date')])
            ->setParticipationType((int)request('participation_type'))
            ->setEventGroupId(request('account_type') == 'group' ? (int)request('event_group_id') : 0);


        if ( ! $availability->getRoomGroups()) {
            $this->responseWarning("Aucune chambre n'est configurée.");
        }

        if ( ! $availability->get('contingent')) {
            $this->responseWarning("Aucun contingent n'est saisi pour cet hébergement.");
        }

        if ($this->hasErrors()) {
            return $this->fetchResponse();
        }


        $period = (new CarbonPeriod(
            Carbon::createFromFormat('d/m/Y', request('entry_date')),
            Carbon::createFromFormat('d/m/Y', request('out_date'))->subDay(),
        ));


        $range        = iterator_to_array($period->map(fn($item) => $item->toDateString()));
        $account_type = request('account_type');
        $data         = [
            'availability_summary' => $availability->getAvailability(),
            'group_recap'          => $availability->get('group_recap'),
            'range'                => $range,
            'defaultVatId'         => VatAccessor::defaultRate()->id,
            'availability'         => $availability,
            'account_type'         => $account_type,
            'account_id'           => $account_type == OrderClientType::GROUP->value ? (int)request('event_group_id') : (int)request('account_id'),
            'participation_type'   => $availability->get('participation_type'),
            'accommodation'        => $accommodation,
            'services'             => $accommodation->service,
            'pec_eligible'         => (bool)request('pec'),

        ];

        $this->responseElement('html', View::make('orders.shared.available-accommodation', $data)->render());

        return $this->fetchResponse();
    }


    public static function attachAccommodationToOrder(Order $order): void
    {
        if ( ! $order->wasRecentlyCreated) {
            $order->accommodation()->delete();
            $order->taxRoom()->delete();
        }

        self::attachRoomToOrder($order);
        self::attachTaxRoomToOrder($order);
    }

    public function removeAccommodationCartRow(): array
    {
        $cart = AccommodationCart::find((int)request('accommodation_cart_id'));

        if ( ! $cart) {
            $this->responseError("Aucune ligne n'a été trouvée.");

            return $this->fetchResponse();
        }

        $order         = $cart->order;
        $orderAccessor = (new OrderAccessor($order));

        // Remettre la PEC à niveau
        if ($cart->total_pec) {
            $this->pushMessages(
                (new PecActions())
                    ->enableAjaxMode()
                    ->setOrder($order)
                    ->resetAccommodationPec($cart),
            );
        }

        $cart->delete();

        $this->responseSuccess("La disponibilité a été remise.");

        # Order Totals
        (new OrderActions())->setOrder($order)->updateTotals($orderAccessor->computeOrderTotalsFromCarts());


        return $this->fetchResponse();
    }

    public function removeTaxRoomCartRow(): array
    {
        try {
            $cart = TaxRoomCart::find((int)request('id'));

            if ( ! $cart) {
                $this->responseError("Aucune ligne n'a été trouvée.");

                return $this->fetchResponse();
            }

            // Remettre la PEC à niveau
            if ($cart->amount_pec) {
                $this->pushMessages(
                    (new PecActions())
                        ->enableAjaxMode()
                        ->setOrder($cart->order)
                        ->resetTaxRoomPec($cart),
                );
            }

            $orderAccessor = (new OrderAccessor($cart->order));
            $cart->delete();

            $this->responseSuccess("Les frais de dossier ont été supprmés.");

            # Order Totals
            (new OrderActions())->setOrder($cart->order)->updateTotals($orderAccessor->computeOrderTotalsFromCarts());
        } catch (Throwable $e) {
            $this->responseException($e, 'Une erreur est survenue sur la suppression des frais de dossier.');
        }

        return $this->fetchResponse();
    }

    private static function attachRoomToOrder(Order $order): void
    {
        if (request()->filled('shopping_cart_accommodation')) {
            $data   = request('shopping_cart_accommodation');
            $models = [];

            for ($i = 0; $i < count($data['date']); ++$i) {
                $models[] = new AccommodationCart([
                    'event_hotel_id' => $data['event_hotel_id'][$i],
                    'date'           => $data['date'][$i],
                    'room_id'        => $data['room_id'][$i],
                    'room_group_id'  => $data['room_group_id'][$i],
                    'quantity'       => $data['quantity'][$i],
                    'unit_price'     => $data['unit_price'][$i],
                    'total_net'      => $data['price_ht'][$i],
                    'total_vat'      => $data['vat'][$i],
                    'total_pec' => ($data['pec_allocation_ht'][$i] + $data['pec_allocation_vat'][$i]) * $data['quantity'][$i],
                    'vat_id'                       => $data['vat_id'][$i],
                    'participation_type_id'        => (int)request('participation_type'),
                    'event_contact_id' => $order->getBeneficiary(),
                    'amended_cart_id'              => $order->getAmendedAccommodationCartId(),
                    //TODO: ça doit se trouver dans les Attributions, pas ici
                    'on_quota'                     => $data['on_quota'][$i],
                ]);
            }
            $order->accommodation()->saveMany($models);
        }
    }

    private static function attachTaxRoomToOrder(Order $order): void
    {
        if (request()->filled('shopping_cart_taxroom')) {
            $data   = request('shopping_cart_taxroom');
            $models = [];

            for ($i = 0; $i < count($data['room_id']); ++$i) {

                $models[] = new TaxRoomCart([
                    'event_hotel_id' => $data['event_hotel_id'][$i],
                    'room_id'        => $data['room_id'][$i],
                    'quantity'       => $data['quantity'][$i],
                    'amount'         => $data['unit_price'][$i],
                    'amount_net'     => $data['price_ht'][$i],
                    'amount_vat'     => $data['vat'][$i],
                    'amount_pec'     => $data['pec_allocation_ht'][$i] + $data['pec_allocation_vat'][$i],
                    'vat_id'         => $data['vat_id'][$i],
                ]);
            }
            $order->taxRoom()->saveMany($models);
        }
    }


    /**
     * Allocates orders based on available ordered elements, previously attributed quantities, and the needed quantity.
     *
     * @param  array   $orderedElements     Array of stdClass objects with properties: order_id, date, room_id, quantity.
     * @param  array   $attributedElements  Associative array where keys are order_ids and values are already attributed quantities.
     * @param  int     $neededQuantity      The quantity needed for the next attribution.
     * @param  string  $date                The date to match in ordered elements.
     *
     * @return array Associative array where keys are order_ids and values are the assigned quantities to fulfill the need.
     */
    private function allocateOrders(array $orderedElements, array $attributedElements, int $neededQuantity, string $date): array
    {
        $result        = [];
        $remainingNeed = $neededQuantity;

        foreach ($orderedElements as $element) {
            if ($element->date === $date) {
                $availableQuantity = $element->quantity - ($attributedElements[$element->order_id] ?? 0);

                if ($availableQuantity > 0) {
                    $assignedQuantity = min($availableQuantity, $remainingNeed);

                    $result[$element->order_id] = $assignedQuantity;

                    $remainingNeed -= $assignedQuantity;

                    if ($remainingNeed <= 0) {
                        break;
                    }
                }
            }
        }

        return $result;
    }

    private function pushAttrubutionData(int $order_id, int $room_id, int $quantity, int $event_contact_id, string $date): array
    {
        return [
            'order_id'         => $order_id,
            'shoppable_id'     => $room_id,
            'shoppable_type'   => OrderCartType::ACCOMMODATION->value,
            'quantity'         => $quantity,
            'event_contact_id' => $event_contact_id,
            'assigned_by'      => auth()->id(),
            'attributes'       => ['date' => $date],
        ];
    }

    function deleteFrontAccommodationAttributionRecords(
        int $roomId,
        int $eventContactId,
        string $date,
        int $qty,
    ): void {
        $records = AccommodationAttribution::query()
            ->where('shoppable_id', $roomId)
            ->where('event_contact_id', $eventContactId)
            ->whereJsonContains('attributes->date', $date)
            ->orderBy('order_id')
            ->get(['id', 'order_id', 'quantity']);


        $remainingQty = $qty;
        foreach ($records as $record) {
            if ($record->quantity >= $remainingQty) {
                $record->quantity === $remainingQty
                    ? AccommodationAttribution::query()->where('id', $record->id)->delete()
                    : AccommodationAttribution::query()
                    ->where('id', $record->id)
                    ->update(['quantity' => $record->quantity - $remainingQty]);
                break;
            } else {
                AccommodationAttribution::query()->where('id', $record->id)->delete();
                $remainingQty -= $record->quantity;
            }
        }
    }


}
