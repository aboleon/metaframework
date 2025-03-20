<?php

namespace App\Traits;


use App\Accessors\EventContactAccessor;
use App\Accessors\OrderRequestAccessor;
use App\Actions\Order\PecActions;
use App\Models\Event;
use MetaFramework\Accessors\VatAccessor;
use MetaFramework\Traits\Responses;

trait OrderPecTrait
{

    use Responses;

    private PecActions $pec;
    private bool $evaluatePec = false;
    private float|int $amountPecNet = 0;
    private float|int $amountPecVat = 0;

    protected function evaluatePossiblePec(Event $event): void
    {
        if (OrderRequestAccessor::pecEnabled() && !OrderRequestAccessor::isGroup()) {
            $this->pec = (new PecActions())
                ->setEvent($event)
                ->setEventContactFromOrderRequest();

            $eventContactAccessor = (new EventContactAccessor())->setEventContact($this->pec->getEventContact());

            if ($eventContactAccessor->hasPaidGrantDeposit()) {
                $this->evaluatePec = true;

                $this->pec->findPecForBackOfficeOrder();

                if ($this->pec->getPecDistributionResult()->isNotCovered()) {
                    $this->evaluatePec = false;
                }
            } else {
                $this->responseWarning("Ce participant est PEC mais n'a pas encoré payé de caution. PEC non-appliquée.");
            }
        }
    }


    /**
     * Helper for getting the PEC subtotal
     *
     * @return void
     */
    protected function pecComputeAmounts(): void
    {
        if ($this->evaluatePec && $this->pec->getPecDistributionResult()->isCovered()) {
            // de($this->pec->getPecDistributionResult());
            foreach ($this->pec->getPecDistributionResult()->getServices() as $service) {
                $amount             = $service['cost']['unit_price'] * $service['cost']['quantity'];
                $this->amountPecNet += VatAccessor::netPriceFromVatPrice($amount, $service['cost']['vat_id']);
                $this->amountPecVat += VatAccessor::vatForPrice($amount, $service['cost']['vat_id']);
            }

            $accommodation = $this->pec->getPecDistributionResult()->getAccommodation();
            // de($accommodation, 'sdsds');
            $this->computeAccommodationAmounts($accommodation, 'rooms');
            $this->computeAccommodationAmounts($accommodation, 'taxroom');
        }
    }


    /**
     * Helper for calculation accomodation PEC subtotals
     *
     * @param  array   $accommodation
     * @param  string  $type
     *
     * @return void
     */
    protected function computeAccommodationAmounts(array $accommodation, string $type): void
    {
        if (isset($accommodation['items'][$type])) {
            foreach ($accommodation['items'][$type] as $room) {
                $vat_id             = $room['vat_id'] ?: VatAccessor::defaultRate()?->id;
                $this->amountPecNet += VatAccessor::netPriceFromVatPrice($room['pec_allocation'] * $room['quantity'], $vat_id);
                $this->amountPecVat += VatAccessor::vatForPrice($room['pec_allocation'] * $room['quantity'], $vat_id);
            }
        }
    }

    protected function pecAuthorized(): bool
    {
        return $this->evaluatePec && $this->pec->getPecDistributionResult()->isCovered();
    }


    protected function pecResponse(): void
    {
        if ($this->evaluatePec) {
            $this->pushMessages(
                $this->pec
                    ->registerPecDistributionResult()
                    ->registerQuotas(),
            );
        }
    }

    protected function setOrderPecState(): void
    {
        if ($this->evaluatePec) {
            $this->pec->setOrder($this->order);
            $this->order->pecDistribution = $this->pec->getPecDistributionResult();
        }

        $this->order->pecAuthorized = $this->pecAuthorized();
    }

    public function getPecAmounts(): array
    {
        return [
            'vat' => $this->amountPecVat,
            'net' => $this->amountPecNet,
        ];
    }

    public function getComputedPecTotal(): int|float
    {
        return array_sum($this->getPecAmounts());
    }

    public function getPecNet(): int|float
    {
        return $this->getPecAmounts()['net'];
    }

    public function getPecVat(): int|float
    {
        return $this->getPecAmounts()['vat'];
    }
}
