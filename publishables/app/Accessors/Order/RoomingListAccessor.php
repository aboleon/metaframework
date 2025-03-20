<?php

namespace App\Accessors\Order;

use App\Accessors\Dictionnaries;
use App\Accessors\OrderAccessor;
use App\Models\EventManager\Accommodation as Hotel;
use Illuminate\Support\Collection;

class RoomingListAccessor extends AccommodationAccessor
{

    private ?Collection $bookingData = null;
    private ?Collection $parsedData = null;

    private int $count = 0;


    public function __construct(public ?Hotel $hotel)
    {
        $this->setEventAccommodation($this->hotel);
        $this->setEventFromAccommodation();
        $this->parseData();
    }

    public function getData(): Collection
    {
        if (is_null($this->parsedData)) {
            $this->parsedData = $this->makeDataSet();
        }

        return $this->parsedData;
    }

    protected function makeDataSet(): Collection
    {
        if ($this->bookingData->isEmpty()) {
            return collect();
        }

        $data = [];
        foreach ($this->bookingData as $client_id => $orders) {
            $this->count += $orders->count();

            foreach ($orders as $booking) {
                $bookingInvoiceable = $booking->invoiceable;

                $orderAccessor = (new OrderAccessor($booking));
                if ($orderAccessor->hasAmendedAnotherOrder()) {
                        $bookingInvoiceable = (new OrderAccessor($orderAccessor->getAmendedOrder()))->invoiceable();
                }

                $accompanying = $booking->accompanying->filter(fn($item) => $item['room_id'] == $booking->accommodation->first()?->room_id)->first();

                $dataline = [
                    'beneficiary_id'    => $client_id,
                    'order_id'          => $booking->id,
                    'order_client_type' => $booking->client_type,
                    'last_name'         => mb_strtoupper($booking->account->last_name),
                    'first_name'        => ucfirst(mb_strtolower($booking->account->first_name)),
                    'email'             => $booking->account->email,
                    'company'           => $booking->account->profile->company_name,
                    'country'           => \MetaFramework\Accessors\Countries::getCountryNameByCode(
                        (new \App\Accessors\Accounts($booking->account))->billingAddress()?->country_code,
                    ),
                    'roomnotes'         => $booking->roomnotes->filter(fn($item) => $item->room_id == $booking->accommodation->first()?->room_id)->first()?->note,
                    'invoiceable'       => $bookingInvoiceable?->account?->names(),
                    'order_total'       => ($booking->total_net + $booking->total_vat) - $booking->total_pec,
                    'payments_total'    => $booking->payments->sum('amount'),
                    'order_status'      => $booking->status,
                    'pec'               => $booking->total_pec,
                    'pax'               => 1 + ($accompanying ? $accompanying->total : 0),
                    'accompanying'      => ($accompanying ? nl2br($accompanying->names) : ''),
                    'cost'              => $booking->total_net + $booking->total_vat,

                ];

                foreach ($booking->accommodation as $cart) {
                    $cartData = [
                        'accommodation_cart_id' => $cart->id,
                        'date'                  => $cart->date->format('d/m/Y'),
                        'room_category_id'      => $cart->room_group_id,
                        'room_id'               => $cart->room_id,
                        'room_category_label'   => $this->roomGroups()[$cart->room_group_id] ?? 'NC',
                        'room_label'            => $this->rooms()[$cart->room_id] ?? 'NC',
                        'quantity'              => $cart->quantity,
                        'participation_type'    => Dictionnaries::participationTypesListable($cart->participation_type_id, 'Participant'),

                    ];
                    $data[]   = array_merge($dataline, $cartData);
                }
            }
        }

        return collect($data)
            ->sortBy(['date', 'last_name'])
            ->groupBy(['order_id', 'beneficiary_id', 'room_id']);
    }

    private function parseData(): void
    {
        if (is_null($this->bookingData)) {
            $this->bookingData = $this->bookings()->groupBy('client_id');
        }
    }

    public function getCounter(): int
    {
        return $this->count;
    }


}
