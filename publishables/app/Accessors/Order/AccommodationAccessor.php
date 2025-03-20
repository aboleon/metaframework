<?php

namespace App\Accessors\Order;

use App\Abstract\Orders;
use App\Accessors\EventManager\Availability;
use App\Enum\OrderAmendedType;
use App\Models\EventManager\Accommodation as Hotel;
use App\Models\Order\Cart\AccommodationCart;
use Illuminate\Database\Eloquent\Collection;

class AccommodationAccessor extends Orders
{
    protected ?Hotel $hotel = null;
    protected ?Hotel\RoomGroup $roomgroup = null;

    private ?\Illuminate\Support\Collection $bookings = null;
    private ?Collection $room_groups = null;
    private ?array $rooms = null;
    private ?array $orders_id = null;
    private ?array $booking_dates = null;

    private array $amendedCartsIds = [];

    public function setEventAccommodation(int|Hotel $hotel): self
    {
        $this->hotel = is_int($hotel) ? Hotel::find($hotel) : $hotel;

        if ( ! $this->hotel && is_int($hotel)) {
            $this->responseWarning("Aucun hébergement trouvé avec cet identifiant : #".$hotel);
        }

        return $this;
    }

    public function accommodationCart(): Collection
    {
        return $this->orders->load(
            'accommodation',
            'roomnotes',
            'accompanying',
            'invoiceable',
            'payments',
            'account.profile',
            'account.address',
        );
    }

    public function bookings()
    {
        $this->getOrders();

        if ($this->bookings !== null) {
            return $this->bookings;
        }


        $this->orders->load(
            'accommodation',
            'roomnotes',
            'accompanying',
            'invoiceable',
            'payments',
            'account.profile',
            'account.address',
        );

        $this->bookings = $this->orders->reject(fn($order) => ((!is_null($order->amended_by_order_id) && $order->amend_type == OrderAmendedType::ORDER->value) or $order->accommodation->isEmpty()));

        if ($this->hotel) {
            $this->bookings = $this->bookings->filter(fn($order) => $order->accommodation->filter(fn($item) => $item->event_hotel_id == $this->hotel->id));
        }

        $this->amendedCartsIds = $this->bookings->pluck('accommodation.*.amended_cart_id')->flatten()->filter()->values()->toArray();

        return $this->bookings;
    }

    public function get(): array
    {
        if ($this->orders_id !== null) {
            return $this->orders_id;
        }

        $this->orders_id = AccommodationCart::query()->pluck('order_id', 'id')->toArray();

        return $this->orders_id;
    }


    public function setEventFromAccommodation(): self
    {
        if ($this->hotel && ! $this->event) {
            $this->event = $this->hotel->event;
        }

        return $this;
    }

    private function fetchRoomGroups(): Collection
    {
        if (is_null($this->room_groups)) {
            $this->room_groups = $this->hotel->id
                ? $this->hotel->roomGroups->load('rooms.room')
                : collect();
        }

        return $this->room_groups;
    }

    public function roomGroups(): array
    {
        $this->fetchRoomGroups();

        return ! is_null($this->room_groups) ? $this->room_groups->pluck('name', 'id')->toArray() : [];
    }

    public function rooms(): array
    {
        if (is_null($this->rooms)) {
            try {
                $this->rooms =  $this->fetchRoomGroups()->mapWithKeys(fn($item) => $item->rooms->pluck('room.name', 'id'))->toArray();
            } catch (\Throwable) {
                return ['error'];
            }
        }

        return $this->rooms;
    }

    public function bookingsDates(): array
    {
        if ( ! $this->hotel) {
            return [];
        }

        if (is_null($this->booking_dates)) {
            $this->booking_dates = array_keys((new Availability())->setEventAccommodation($this->hotel)->get('contingent'));
        }

        return $this->booking_dates;
    }

    public function getAmendedCartsIds(): array
    {
        return $this->amendedCartsIds;
    }

}
