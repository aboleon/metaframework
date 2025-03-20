<?php

namespace App\Actions\Order;

use App\Accessors\OrderAccessor as OrderAccessor;
use App\Accessors\Pec;
use App\Enum\GrantFundingRecordCategory;
use App\Models\EventContact;
use App\Models\EventManager\Grant\GrantFundingRecord;
use App\Models\EventManager\Sellable;
use App\Models\Order;
use App\Services\Grants\ParsedGrant;
use MetaFramework\Accessors\VatAccessor;

class FundingRecordsActions
{
    private ParsedGrant $grant;
    private EventContact $eventContact;
    private Order $order;
    private array $differentStays = [];

    public static function register(ParsedGrant $grant, EventContact $ec, Order $order): void
    {
        $action = new self();
        $action->setGrant($grant);
        $action->setEventContact($ec);
        $action->setOrder($order);

        $action->registerFees();
        $action->registerServiceCart();
        $action->registerAccommodationCart();
    }

    public function setGrant(ParsedGrant $grant): self
    {
        $this->grant = $grant;
        return $this;
    }

    public function setEventContact(EventContact $ec): self
    {
        $this->eventContact = $ec;
        return $this;
    }

    public function setOrder(Order $order): self
    {
        $this->order = $order;
        return $this;
    }

    private function registerFees(): void
    {
        if (!Pec::accountHasAlreadyPec($this->eventContact->user_id)) {
            GrantFundingRecord::create([
                'grant_id' => $this->grant->id,
                'event_contact_id' => $this->eventContact->id,
                'order_id' => $this->order->id,
                'shoppable_id' => $this->order->id,
                'shoppable_type' => "grant_processing_fee",
                'shoppable_category' => GrantFundingRecordCategory::GRANT_PROCESSING_FEE->value,
                'amount_ht' => VatAccessor::netPriceFromVatPrice($this->grant->config['pec_fee'], $this->grant->event_pec_config['processing_fees_vat_id']),
                'amount_ttc' => $this->grant->config['pec_fee'],
            ]);
        }
    }

    private function registerServiceCart(): void
    {
        $oa = new OrderAccessor($this->order);
        $serviceCarts = $oa->serviceCart();
        if ($serviceCarts->isNotEmpty()) {
            $serviceCarts->each(function (Order\Cart\ServiceCart $cartService) {
                if ($cartService->total_pec > 0) {
                    $service = $cartService->service;
                    GrantFundingRecord::create([
                        'grant_id' => $this->grant->id,
                        'order_id' => $this->order->id,
                        'event_contact_id' => $this->eventContact->id,
                        'shoppable_id' => $service->id,
                        'shoppable_type' => Sellable::class,
                        'shoppable_category' => GrantFundingRecordCategory::SELLABLE->value,
                        'amount_ht' => VatAccessor::netPriceFromVatPrice($cartService->total_pec, $cartService->vat_id),
                        'amount_ttc' => $cartService->total_pec,
                    ]);
                }
            });
        }
    }

    private function registerAccommodationCart(): void
    {
        $oa = new OrderAccessor($this->order);
        $accommodationCarts = $oa->accommodationCart();
        if ($accommodationCarts->isNotEmpty()) {
            $accommodationCarts->each(function (Order\Cart\AccommodationCart $cartAccommodation) {
                $this->processAccommodationCart($cartAccommodation);
            });

            $this->registerDifferentStays();
        }
    }

    private function processAccommodationCart(Order\Cart\AccommodationCart $cartAccommodation): void
    {
        if ($cartAccommodation->total_pec > 0) {
            $stayKey = $this->generateStayKey($cartAccommodation);
            if (!array_key_exists($stayKey, $this->differentStays)) {
                $this->differentStays[$stayKey] = $cartAccommodation->eventHotel;
            }

            $shoppableId = $this->generateShoppableId($cartAccommodation);

            GrantFundingRecord::create([
                'grant_id' => $this->grant->id,
                'order_id' => $this->order->id,
                'event_contact_id' => $this->eventContact->id,
                'shoppable_id' => $shoppableId,
                'shoppable_type' => "stay",
                'shoppable_category' => GrantFundingRecordCategory::ROOM->value,
                'amount_ht' => VatAccessor::netPriceFromVatPrice($cartAccommodation->total_pec, $cartAccommodation->vat_id),
                'amount_ttc' => $cartAccommodation->total_pec,
            ]);
        }
    }

    private function generateStayKey(Order\Cart\AccommodationCart $cartAccommodation): string
    {
        return implode('-', [
            $cartAccommodation->event_hotel_id,
            $cartAccommodation->room_group_id,
            $cartAccommodation->room_id,
        ]);
    }

    private function generateShoppableId(Order\Cart\AccommodationCart $cartAccommodation): string
    {
        return implode('-', [
            $cartAccommodation->date,
            $cartAccommodation->event_hotel_id,
            $cartAccommodation->room_group_id,
            $cartAccommodation->room_id,
        ]);
    }

    private function registerDifferentStays(): void
    {
        foreach ($this->differentStays as $accommodation) {
            GrantFundingRecord::create([
                'grant_id' => $this->grant->id,
                'order_id' => $this->order->id,
                'event_contact_id' => $this->eventContact->id,
                'shoppable_id' => $accommodation->id,
                'shoppable_type' => "stay_processing_fee",
                'shoppable_category' => GrantFundingRecordCategory::ROOM_PROCESSING_FEE->value,
                'amount_ht' => VatAccessor::netPriceFromVatPrice($accommodation->processing_fee, $accommodation->processing_fee_vat_id),
                'amount_ttc' => $accommodation->processing_fee,
            ]);
        }
    }
}
