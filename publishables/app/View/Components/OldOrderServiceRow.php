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

class OldOrderServiceRow extends Component
{
    public string $identifier;
    public int $qty;
    public ?DictionnaryEntry $group;
    public Sellable $sellable;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public Collection  $services,
        public array $cart,
        public int $iteration
    )
    {
        $this->sellable = $services->where('id', $this->cart['id'][$this->iteration])->first();
        $this->identifier = Str::random();
        $this->qty = $this->cart['quantity'][$this->iteration];
        $this->group = $this->sellable?->event->services->where('id', $this->sellable->service_group)->first();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.old-order-service-row');
    }
}
