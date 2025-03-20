<?php

namespace App\View\Components;

use App\Accessors\OrderAccessor;
use App\Models\DictionnaryEntry;
use App\Models\EventManager\Sellable;
use App\Models\Order\Cart\ServiceCart;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\Component;

class InvoiceRowService extends Component
{
    public ?DictionnaryEntry $group;
    public ?Sellable $sellable;


    /**
     * Create a new component instance.
     */
    public function __construct(
        public ServiceCart $cart,
        public OrderAccessor $orderAccessor,
        public Collection  $services
    )
    {
        $this->sellable = $services->where('id', $this->cart->service_id)->first();
        $this->group = $this->sellable?->event->services->where('id', $this->sellable->service_group)->first();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.invoice-row-service');
    }
}
