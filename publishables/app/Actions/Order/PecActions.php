<?php

namespace App\Actions\Order;

use App\Accessors\OrderRequestAccessor;
use App\Actions\EventManager\GrantActions;
use App\Models\{Event, EventContact, EventManager\Grant\Quota, Order, Order\Cart\ServiceCart, PecDistribution};
use App\Services\Grants\QuotaType;
use App\Services\Pec\{PecDistributionResult, PecFinder, PecParser, PecType};
use MetaFramework\Accessors\VatAccessor;
use MetaFramework\Traits\Responses;

class PecActions
{
    use Responses;

    protected PecDistributionResult $pecDistributionResult;
    protected PecParser $pecParser;
    protected ?EventContact $eventContact = null;
    protected ?Order $order = null;
    protected Event $event;
    private ?int $frontCartId = null;


    public function __construct()
    {
        $this->pecDistributionResult = new PecDistributionResult();
    }

    public function setFrontCartId(int $cartId): self
    {
        $this->frontCartId = $cartId;

        return $this;
    }

    public function getFrontCartId(): ?int
    {
        return $this->frontCartId;
    }

    /*
     * if (OrderRequestAccessor::pecEnabled())
     * @return array<PecParser|PecDistributionResult>
     */
    public function findPecForBackOfficeOrder(): self
    {
        if ( ! $this->eventContact) {
            $this->setEventContactFromOrderRequest();
        }

        $this->pecParser();

        if ($this->pecParser->hasGrants($this->eventContact->id)) {
            //    de(OrderRequestAccessor::getPecEligibleAccommodation(), 'OrderRequestAccessor::getTotalAccommodationPecFromRequest()');
            $this->pecFinder();
        }

        return $this;
    }

    public function pecFinder(): self
    {
        $pecFinder = new PecFinder();
        $pecFinder->setEventContact($this->eventContact);
        $pecFinder->setServices(OrderRequestAccessor::getPecEligibleServices());
        $pecFinder->setGrants($this->pecParser->getGrantsFor($this->eventContact->id));
        $pecFinder->setAccommodationTotal(OrderRequestAccessor::getTotalAccommodationPecFromRequest());
        $pecFinder->setAccommodationEligibles(OrderRequestAccessor::getPecEligibleAccommodation());
        $pecFinder->askForProcessingFees($this->shouldPayProcessingFee());

        $this->pecDistributionResult = $pecFinder->filterGrants();

        return $this;
    }

    public function shouldPayProcessingFee(): bool
    {
        return $this->eventContact->pec_fees_apply && $this->userHasNotPaidProcessingFees();
    }

    public function userHasNotPaidProcessingFees(): bool
    {
        return PecDistribution::query()
            ->where([
                'event_contact_id' => $this->eventContact->id,
                'type'             => PecType::PROCESSING_FEE->value,
            ])
            ->doesntExist();
    }

    public function getPecParser(): PecParser
    {
        return $this->pecParser;
    }

    public function getPecDistributionResult(): PecDistributionResult
    {
        return $this->pecDistributionResult;
    }

    /**
     * Enregistre la distribution du grant par postes
     * dans la table pec_distribution
     *
     * @return $this
     */
    public function registerPecDistributionResult(): self
    {
        if ($this->pecDistributionResult->isNotCovered()) {
            $this->responseError("La PEC ne peut pas être couverte par de grants disponibles.");

            return $this;
        }


        $models = [];

        $services       = $this->pecDistributionResult->getServices();
        $accommodation  = $this->pecDistributionResult->getAccommodation();
        $processing_fee = $this->pecDistributionResult->getProcessingFee();

        //    de($accommodation, 'Accommodation in PecActions');

        if ($services) {
            foreach ($services as $service) {
                $models[] = (new PecDistribution([
                    'grant_id'         => $service['grant_id'],
                    'event_contact_id' => $this->eventContact->id,
                    'front_cart_id'    => $this->getFrontCartId(),
                    'unit_price'       => $service['cost']['unit_price'],
                    'quantity'         => $service['cost']['quantity'],
                    'type'             => PecType::SERVICE->value,
                    'total_net'        => VatAccessor::netPriceFromVatPrice($service['cost']['unit_price'] * $service['cost']['quantity'], $service['cost']['vat_id']),
                    'total_vat'        => VatAccessor::vatForPrice($service['cost']['unit_price'] * $service['cost']['quantity'], $service['cost']['vat_id']),
                    'vat_id'           => $service['cost']['vat_id'],
                    'shoppable_id'     => $service['id'],
                ]));
            }
        }
        // Save Room line

        if (isset($accommodation['items']['rooms'])) {
            foreach ($accommodation['items']['rooms'] as $room_id => $room) {
                $models[] = $this->producePecDistrubutionAccommodationLine(grant_id: $accommodation['grant_id'], room: $room, room_id: $room_id);
            }
        }

        // Save Taxroom line
        if (isset($accommodation['items']['taxroom'])) {
            foreach ($accommodation['items']['taxroom'] as $room_id => $room) {
                $models[] = $this->producePecDistrubutionAccommodationLine(grant_id: $accommodation['grant_id'], room: $room, room_id: $room_id, type: PecType::TAXROOM->value);
            }
        }

        if ($processing_fee) {
            $vat_id   = $processing_fee['vat_id'] ?? VatAccessor::defaultRate()->id;
            $models[] = (new PecDistribution([
                'grant_id'         => $processing_fee['grant_id'],
                'event_contact_id' => $this->eventContact->id,
                'front_cart_id'    => $this->getFrontCartId(),
                'unit_price'       => $processing_fee['cost'],
                'quantity'         => 1,
                'type'             => PecType::PROCESSING_FEE->value,
                'total_net'        => VatAccessor::netPriceFromVatPrice($processing_fee['cost'] * 1, $vat_id),
                'total_vat'        => VatAccessor::vatForPrice($processing_fee['cost'] * 1, $vat_id),
                'vat_id'           => $vat_id,
            ]));
        }

        if ($this->order) {
            $this->order->pecDistributions()->saveMany($models);
        } else {
            # Saving from front cart where order is still not known
            collect($models)->each(fn($model) => $model->save());
        }
        // Reset Grant elibility
        (new GrantActions())->updateEligibleStatusForContacts($this->event);

        if ( ! $this->frontCartId) {
            $this->responseSuccess("La PEC a été enregistrée.");
        }

        return $this;
    }

    public function setPecDistributionResult(PecDistributionResult $result): self
    {
        $this->pecDistributionResult = $result;

        return $this;
    }

    public function setPecParser(PecParser $parser): self
    {
        $this->pecParser = $parser;

        return $this;
    }

    public function setOrder(Order $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function setEvent(Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function setEventContact(EventContact $contact): self
    {
        $this->eventContact = $contact;

        return $this;
    }

    public function pecParser(): self
    {
        $this->pecParser = new PecParser($this->event, collect()->push($this->eventContact));
        $this->pecParser->calculate();

        return $this;
    }

    /**
     * Filtre les matches qui sont sujets à des quotas dans les critères d'éligibilité
     * pour les enregistrer dans la table grant_quota
     *
     * @return array
     */
    public function quotaMatches(): array
    {
        $controlDataSet = QuotaType::keys();

        $filteredItems = [];

        foreach ($this->pecDistributionResult->getOverview() as $grant) {
            foreach ($this->pecParser->getEligibilityFor($this->eventContact, $grant['grant_id'])->getMatches() as $item) {
                if ($item['quota'] == 1 && in_array($item['type'], $controlDataSet)) {
                    $item['grant_id']                                   = $grant['grant_id'];
                    $filteredItems[$item['type'].'-'.$item['grant_id']] = $item;
                }
            }
        }

        return $filteredItems;
    }

    public function registerQuotas(): self
    {
        $matches = $this->quotaMatches();

        if ( ! $matches) {
            //$this->responseLog('No quota matches to register');
            return $this;
        }

        $data = [];

        foreach ($matches as $match) {
            $data[] = [
                'order_id' => $this->order->id,
                'grant_id' => $match['grant_id'],
                'type'     => $match['type'],
                'value'    => $match['value'],
                'geo_type' => $match['geo_type'] ?? null,
            ];
        }

        Quota::query()->insert($data);

        return $this;
    }

    /**
     * Remettre la PEC hébergement à niveau depuis une suppression
     *
     * @param  int  $amount
     *
     * @return $this
     */
    public function resetAccommodationPec(Order\Cart\AccommodationCart $cart): self
    {
        $this->order
            ->pecDistributions()
            ->where('type', PecType::ACCOMMODATION->value)
            ->where('shoppable_id', $cart->room_id)
            ->delete();


        $this->removeDistributionWhitoutItems();

        return $this;
    }

    /**
     * Remettre la PEC Taxroom à niveau depuis une suppression
     *
     * @param  int  $amount
     *
     * @return $this
     */
    public function resetTaxRoomPec(Order\Cart\TaxRoomCart $cart): self
    {
        $this->order
            ->pecDistributions()
            ->where('type', PecType::TAXROOM->value)
            ->where('shoppable_id', $cart->room_id)
            ->delete();


        $this->removeDistributionWhitoutItems();

        return $this;
    }

    public function hasPossiblePec(): bool
    {
        return $this->pecParser->hasGrants($this->eventContact->id);
    }

    /**
     * Remettre la PEC prestations à niveau depuis une suppression
     *
     * @param  ServiceCart  $cart
     *
     * @return $this
     */
    public function resetServicePec(Order\Cart\ServiceCart $cart): self
    {
        if ( ! $cart->total_pec) {
            return $this;
        }


        $this->order
            ->pecDistributions()
            ->where('type', PecType::SERVICE->value)
            ->where('shoppable_id', $cart->service_id)
            ->delete();


        $this->removeDistributionWhitoutItems();

        return $this;
    }

    private function removeDistributionWhitoutItems(): self
    {
        $orderPecCount = $this->order->pecDistributions()->count();

        if ($orderPecCount == 0) {
            $this->order->pecQuota()->delete();
        } else {
            if ($orderPecCount < 2 && $this->order->pecDistributions()->first()->type == PecType::PROCESSING_FEE->value) {
                $this->order->pecDistributions()->delete();
                $this->order->pecQuota()->delete();
            }
        }

        // Reset Grant elibility
        (new GrantActions())->updateEligibleStatusForContacts($this->order->event);


        $this->responseSuccess("La PEC a été ajustée.");

        return $this;
    }

    /**
     * Generate model of pec distrubution for saving
     *
     * @param  int    $grant_id
     * @param  array  $room
     * @param  int    $room_id
     *
     * @return PecDistribution
     */
    private function producePecDistrubutionAccommodationLine(int $grant_id, array $room, int $room_id, string $type = PecType::ACCOMMODATION->value): PecDistribution
    {
        return (new PecDistribution([
            'grant_id'         => $grant_id,
            'event_contact_id' => $this->eventContact->id,
            'front_cart_id'    => $this->getFrontCartId(),
            'unit_price'       => $room['unit_price'],
            'quantity'         => $room['quantity'],
            'type'             => $type,
            'total_net'        => VatAccessor::netPriceFromVatPrice($room['pec_allocation'] * $room['quantity'], $room['vat_id']),
            'total_vat'        => VatAccessor::vatForPrice($room['pec_allocation'] * $room['quantity'], $room['vat_id']),
            'vat_id'           => $room['vat_id'],
            'shoppable_id'     => $room_id,
        ]));
    }

    public function setEventContactFromOrderRequest(): self
    {
        $this->eventContact = $this->event->contacts()->where('user_id', OrderRequestAccessor::getClientId())->first();

        return $this;
    }

    public function getEventContact(): EventContact
    {
        return $this->eventContact;
    }

}
