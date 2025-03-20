<?php

namespace App\View\Components;

use App\Models\DictionnaryEntry;
use App\Models\EventManager\Sellable;
use App\Models\Order\Cart\ServiceCart;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class OrderServiceRow extends Component
{
    public string $identifier;
    public int $qty;
    public ?DictionnaryEntry $group;
    public ?Sellable $sellable;


    /**
     * Create a new component instance.
     */
    public function __construct(
        public ServiceCart $cart,
        public Collection  $services,
        public bool        $invoiced = false,
        public int         $pec_enabled = 0,
        public array $pecbooked = []
    )
    {
        $this->identifier = Str::random();
        $this->qty = $this->cart->quantity ?: 1;
        $this->sellable = $this->cart?->id ? $services->where('id', $this->cart->service_id)->first() : null;
        $this->group = $this->sellable?->event->services->where('id', $this->sellable->service_group)->first();
        if ($this->sellable) {
            $this->pec_enabled = $this->cart?->id ? (bool)$this->cart->total_pec : (int)$this->sellable->pec_eligible;
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.order-service-row');
    }
}
