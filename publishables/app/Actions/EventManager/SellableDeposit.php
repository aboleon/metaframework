<?php

namespace App\Actions\EventManager;

use App\Http\Requests\EventManager\SellableServiceRequest;
use App\Models\EventManager\Sellable;

class SellableDeposit
{

    public function __construct(
        private Sellable               $sellable,
        private SellableServiceRequest $request,
        private bool                   $store = false
    )
    {
    }

    public function __invoke(): void
    {
        $this->manageDeposit();
    }

    private function manageDeposit(): void
    {
        $hasDeposit = $this->request->has('sellable_has_deposit');

        if (!$hasDeposit && !$this->store) {
            $this->sellable->deposit()->delete();
        }
        if ($hasDeposit) {
            $deposit = [];
            $deposit['amount'] = $this->request->get('sellable_deposit');
            $deposit['vat_id'] = $this->request->get('sellable_deposit_vat_id');
            $this->sellable->deposit()->updateOrCreate(['event_sellable_service_id' => $this->sellable->id], $deposit);

        }

    }

}
