<?php

namespace App\Accessors;

use App\Enum\OrderCartType;
use App\Enum\OrderClientType;
use App\Models\EventContact;
use App\Models\EventManager\EventGroup;
use MetaFramework\Accessors\VatAccessor;

class OrderRequestAccessor
{
    public static function getTotalNetFromRequest(): int|float
    {
        $total = 0;
        $total += self::getNetFromAccommodation();
        $total += self::getNetFromService();
        $total += self::getNetFromTaxroom();

        return round($total, 2);
    }

    public static function getTotalVatFromRequest(): int|float
    {
        $total = 0;
        $total += self::getVatFromService();
        $total += self::getVatFromAccommodation();
        $total += self::getVatFromTaxroom();

        return round($total, 2);
    }

    public static function getTotalPecFromRequest(): int|float
    {
        $total = 0;
        $total += self::getPecFromService();
        $total += self::getPecFromAccommodation();
        $total += self::getPecFromTaxroom();

        return round($total, 2);
    }

    public static function getTotalAccommodationPecFromRequest(): int|float
    {
        $total = 0;
        $total += self::getPecFromAccommodation();
        $total += self::getPecFromTaxroom();

        return round($total, 2);
    }

    public static function getNetFromAccommodation(): int|float
    {
        if (request()->filled('shopping_cart_accommodation')) {
            $data  = (array)request('shopping_cart_accommodation');
            $total = 0;
            for ($i = 0; $i < count($data['room_id']); $i++) {
                $total += $data['price_ht'][$i];
            }

            return round($total, 2);
        }

        return 0;
    }

    public static function getNetFromService(): int|float
    {
        if (request()->filled('shopping_cart_service')) {
            $data  = (array)request('shopping_cart_service');
            $total = 0;
            for ($i = 0; $i < count($data['id']); $i++) {
                $total += VatAccessor::netPriceFromVatPrice($data['unit_price'][$i] * $data['quantity'][$i], $data['vat_id'][$i]);
            }

            return round($total, 2);
        }

        return 0;
    }

    public static function getNetFromTaxroom(): int|float
    {
        if (request()->filled('shopping_cart_taxroom')) {
            $data  = (array)request('shopping_cart_taxroom');
            $total = 0;
            for ($i = 0; $i < count($data['room_id']); $i++) {
                $total += $data['price_ht'][$i];
            }

            return round($total, 2);
        }

        return 0;
    }

    public static function getVatFromAccommodation(): int|float
    {
        if (request()->filled('shopping_cart_accommodation')) {
            $data  = (array)request('shopping_cart_accommodation');
            $total = 0;
            for ($i = 0; $i < count($data['room_id']); $i++) {
                $total += $data['vat'][$i];
            }

            return round($total, 2);
        }

        return 0;
    }

    public static function getVatFromService(): int|float
    {
        if (request()->filled('shopping_cart_service')) {
            $data  = (array)request('shopping_cart_service');
            $total = 0;
            for ($i = 0; $i < count($data['id']); $i++) {
                $total += VatAccessor::vatForPrice($data['unit_price'][$i] * $data['quantity'][$i], $data['vat_id'][$i]);
            }

            return round($total, 2);
        }

        return 0;
    }

    public static function getVatFromTaxroom(): int|float
    {
        if (request()->filled('shopping_cart_taxroom')) {
            $data  = (array)request('shopping_cart_taxroom');
            $total = 0;
            for ($i = 0; $i < count($data['room_id']); $i++) {
                $total += $data['vat'][$i];
            }

            return round($total, 2);
        }

        return 0;
    }

    public static function getPecFromAccommodation(): int|float
    {
        $total = 0;
        if (request()->filled('shopping_cart_accommodation')) {
            $data = (array)request('shopping_cart_accommodation');
            for ($i = 0; $i < count($data['date']); $i++) {
                if ( ! $data['pec_enabled'][$i]) {
                    continue;
                }
                $total += ($data['pec_allocation_ht'][$i] + $data['pec_allocation_vat'][$i]) * $data['quantity'][$i];
            }
        }

        return round($total, 2);
    }

    public static function getPecFromService(): int|float
    {
        $total = 0;
        if (request()->filled('shopping_cart_service')) {
            $data = (array)request('shopping_cart_service');
            for ($i = 0; $i < count($data['id']); $i++) {
                if ( ! $data['pec_enabled'][$i]) {
                    continue;
                }
                $total += $data['unit_price'][$i] * $data['quantity'][$i];
            }
        }

        return round($total, 2);
    }

    public static function getPecFromTaxroom(): int|float
    {
        $total = 0;
        if (request()->filled('shopping_cart_taxroom')) {
            $data = (array)request('shopping_cart_taxroom');
            for ($i = 0; $i < count($data['room_id']); $i++) {
                if ( ! $data['pec_enabled'][$i]) {
                    continue;
                }
                $total += $data['pec_allocation_ht'][$i] + $data['pec_allocation_vat'][$i];
            }
        }

        return round($total, 2);
    }

    public static function getBeneficiaryEventContact($event, $request): ?int
    {
        switch ($request->validated('order.client_type')) {
            case OrderClientType::CONTACT->value:
                return EventContact::where('user_id', $request->validated('order.contact_id'))->where('event_id', $event->id)->value('id');

            case OrderClientType::GROUP->value :
                $main_contact_id = EventGroup::where("group_id", $request->validated('order.group_id'))->where("event_id", $event->id)->value('main_contact_id');
                if ($main_contact_id) {
                    return EventContact::where('user_id', $main_contact_id)->where('event_id', $event->id)->value('id');
                }

                return null;
            default:
                return null;
        }
    }

    public static function pecEnabled(): bool
    {
        return request('pec_enabled') == 1;
    }

    public static function getPecEligibleServices(): array
    {
        return self::getPecEligibleItems(OrderCartType::SERVICE->value);
    }

    public static function getPecEligibleAccommodation(): array
    {
        return [
            'rooms'   => self::getPecAccommdationEligibleItems(),
            'taxroom' => self::getPecEligibleItems(OrderCartType::TAXROOM->value),
        ];
    }

    public static function getPecEligibleItems(string $type = OrderCartType::SERVICE->value): array
    {
        $key = match ($type) {
            OrderCartType::TAXROOM->value => 'room_id',
            default => 'id'
        };

        if ( ! in_array($type, OrderCartType::keys())) {
            return [];
        }

        if ( ! request()->filled('shopping_cart_'.$type)) {
            return [];
        }
        $data     = [];
        $iterable = (array)request('shopping_cart_'.$type);


        for ($i = 0; $i < count($iterable[$key]); $i++) {
            if ( ! $iterable['pec_enabled'][$i]) {
                continue;
            }
            $pec_allocation            = $type = OrderCartType::SERVICE->value
                ? $iterable['unit_price'][$i] // Prestas à 100%
                : $iterable['pec_allocation_ht'][$i] + $iterable['pec_allocation_vat'][$i];
            $data[$iterable[$key][$i]] = [
                'unit_price'     => $iterable['unit_price'][$i],
                'pec_allocation' => $pec_allocation,
                'quantity'       => $iterable['quantity'][$i],
                'vat_id'         => $iterable['vat_id'][$i],
            ];
        }

        return $data;
    }

    public static function getPecAccommdationEligibleItems(): array
    {
        $type = OrderCartType::ACCOMMODATION->value;
        $key  = 'room_id';

        if ( ! request()->filled('shopping_cart_'.$type)) {
            return [];
        }
        $data     = [];
        $iterable = (array)request('shopping_cart_'.$type);


        for ($i = 0; $i < count($iterable[$key]); $i++) {
            if ( ! $iterable['pec_enabled'][$i]) {
                continue;
            }
            $data[] = [
                'room_id'        => $iterable['room_id'][$i],
                'date'           => $iterable['date'][$i],
                'unit_price'     => $iterable['unit_price'][$i],
                'pec_allocation' => $iterable['pec_allocation_ht'][$i] + $iterable['pec_allocation_vat'][$i],
                'quantity'       => $iterable['quantity'][$i],
                'vat_id'         => $iterable['vat_id'][$i],
            ];
        }

        return $data;
    }

    public static function getClientType(): string
    {
        return request('order.client_type');
    }

    public static function isGroup(): bool
    {
        return self::getClientType() == OrderClientType::GROUP;
    }

    public static function getClientId(): int
    {
        return request('order.contact_id');
    }


}
