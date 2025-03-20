<?php

namespace App\Accessors\Order;

use App\Enum\OrderClientType;
use App\Models\Account;
use App\Models\EventContact;
use App\Models\Group;
use App\Models\Order;
use App\Models\Order\Cart\ServiceCart;
use Illuminate\Support\Collection;


class Orders
{
    public static function orderContainsGrantDeposit(Order $order): bool
    {
        return $order->grantDeposit->isNotEmpty();
    }

    public static function getUserLastOrder(int $userId)
    {
        return Order::where('client_id', $userId)
            ->where('client_type', OrderClientType::CONTACT->value)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public static function ownerHasCancelled(Order $order): bool
    {
        if (OrderClientType::CONTACT->value === $order->client_type) {

            $ec = EventContact::where('event_id', $order->event_id)
                ->where('user_id', $order->client_id)
                ->first();
            if ($ec?->order_cancellation) {
                return true;
            }
        } elseif (OrderClientType::GROUP->value === $order->client_type) {
            // todo ...
        }
        return false;
    }

    public static function orderContainsServiceOfFamily(Order $order, int $serviceFamilyId): bool
    {
        $has = $order->cart()
            ->where('shoppable_type', ServiceCart::class)
            ->whereHas('services', function ($query) use ($serviceFamilyId) {
                $query->join('event_sellable_service', 'event_sellable_service.id', '=', 'order_cart_service.service_id')
                    ->where('event_sellable_service.service_group', $serviceFamilyId);
            })
            ->exists();

        return $has;
    }

    public static function getOrderClient(Order $order): Account|Group
    {
        return match ($order->client_type) {
            OrderClientType::GROUP->value => $order->group,
            default => $order->account,
        };
    }

    public static function getServiceCarts(Order $order): Collection
    {
        return $order->services->load(['service', 'vat']);
    }

    public static function getAccommodationCarts(Order $order): Collection
    {
        return $order->accommodation->load(['eventHotel', 'roomGroup', 'room']);
    }

    public static function getSellableDepositCarts(Order $order): Collection
    {
        return $order->sellableDeposit->load('deposit');
    }

    public static function getGrantDepositCarts(Order $order): Collection
    {
        return $order->grantDeposit->load('grant');
    }

    public static function getTaxRoomCarts(Order $order): Collection
    {
        return $order->taxRoom->load(['eventHotel', 'room.group']);
    }
}
