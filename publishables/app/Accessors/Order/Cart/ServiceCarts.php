<?php

namespace App\Accessors\Order\Cart;

use App\Accessors\EventContactAccessor;
use App\Enum\OrderClientType;
use App\Models\EventContact;
use App\Models\EventManager\Sellable;
use App\Models\Order\Cart\ServiceCart;
use Illuminate\Support\Collection;

class ServiceCarts
{


    public static function getServiceCartsBySellable(Sellable $sellable): Collection
    {
        // CA doit eventuellement réfletter les insncriptions groupe
        return ServiceCart::join('orders', fn($join) => $join->on('order_id', '=', 'orders.id')->where([
            'event_id'    => $sellable->event_id,
            'client_type' => OrderClientType::default(),
        ]))
            ->with('order.account')->where("service_id", $sellable->id)->get();
    }

    public static function getServiceCartsByEventContact(EventContact $eventContact): Collection
    {
        $ecOrders = EventContactAccessor::getOrdersWithServices($eventContact);

        return $ecOrders->map(function ($order) {
            return $order->services;
        })->flatten();
    }
}
