<?php

namespace App\Actions\Order;

use App\Models\PecDistribution;
use App\Services\Pec\{PecFinder};
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class PecActionsFront extends PecActions
{
    private array $frontBookedServices = [];
    private array $frontBookedAccommodation = [];
    private mixed $frontBookedTaxroom = [];

    public function __construct()
    {
        parent::__construct();
    }

    public function findPec(): self
    {
        $pecFinder = new PecFinder();
        $pecFinder->setEventContact($this->eventContact);
        $pecFinder->setServices($this->getFrontBookedServices());
        $pecFinder->setGrants($this->pecParser->getGrantsFor($this->eventContact->id));
        $pecFinder->setAccommodationTotal($this->getFrontBookedAccommodationTotal());
        $pecFinder->setAccommodationEligibles($this->getFrontBookedAccommodation());
        $pecFinder->askForProcessingFees($this->shouldPayProcessingFee());

        $this->pecDistributionResult = $pecFinder->filterGrants();

        return $this;

    }

    public function parseFrontBookedServices(EloquentCollection $services): self
    {
        if ($services->isNotEmpty()) {

            $this->frontBookedServices = $services->filter(function ($item) {
                return !empty($item->total_pec);
            })->mapWithKeys(function ($item) {
                return [
                    $item->id => [
                        'unit_price' => $item->unit_ttc,
                        'quantity' => $item->quantity,
                        'vat_id' => $item->vat_id,
                    ],
                ];
            })->toArray();
        }

        return $this;
    }

    public function getFrontBookedServices(): array
    {
        return $this->frontBookedServices;
    }

    public function getFrontBookedAccommodation(): array
    {
        return [
            'rooms' => $this->frontBookedAccommodation,
            'taxroom' => $this->frontBookedTaxroom,
        ];
    }

    public function getFrontBookedAccommodationTotal(): int|float
    {
        return $this->frontBookedAccommodation ? array_sum(array_column($this->frontBookedAccommodation, 'unit_price')) : 0;
    }

    public function parseFrontBookedAccomodation(EloquentCollection $stays): self
    {
        if ($stays->isNotEmpty()) {

            $this->frontBookedAccommodation = $stays
                ->filter(fn($item) => $item->total_pec > 0)
                ->flatMap(function ($item) {
                    return collect($item->meta_info['pec_per_night'])
                        ->map(function ($unit_price, $date) use ($item) {
                            return [
                                'room_id' => $item->meta_info['room_id'],
                                'date' => $date,
                                'unit_price' => $unit_price,
                                'quantity' => 1,
                                'pec_allocation' => $unit_price, #doublon avec unit price
                                'vat_id' => $item->meta_info['vat_id'],
                            ];
                        })
                        ->filter(fn($entry) => !empty($entry['unit_price']));
                })
                ->values()
                ->toArray();

            // Add taxes
            $this->getBookedTaxroom($stays);

        }

        return $this;
    }

    private function getBookedTaxroom(EloquentCollection $stays): void
    {
        $this->frontBookedTaxroom = $stays
            ->filter(fn($item) => $item->total_pec > 0)
            ->map(fn($item) => [
                'room_id' => $item->meta_info['room_id'],
                'unit_price' => $item->meta_info['processing_fee_ttc'],
                'pec_allocation' => $item->meta_info['processing_fee_ttc'], #doublon avec unit price
                'quantity' => 1,
                'vat_id' => $item->meta_info['processing_fee_vat_id'],
            ],
            )->toArray();

    }
}
