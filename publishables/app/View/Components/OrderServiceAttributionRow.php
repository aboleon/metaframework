<?php

namespace App\View\Components;

use App\Models\DictionnaryEntry;
use App\Models\Event;
use App\Models\EventManager\Sellable;
use App\Models\Order\Cart\ServiceCart;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class OrderServiceAttributionRow extends Component
{
    public string $identifier;
    public int $distributed_qty;
    public int $qty = 1;
    public ServiceCart $cart;
    public bool $multiple = false;
    public DictionnaryEntry $group;
    public Sellable $sellable;


    /**
     * Create a new component instance.
     */
    public function __construct(
        public Collection $grouped,
        public Event $event,
        public Collection $services,
        public array $distributed = []
    )
    {
        $this->identifier = Str::random();

        $this->cart = $grouped->first();
        $this->sellable = $services->where('id', $this->cart->service_id)->first();
        $this->group = $this->sellable->event->services->where('id', $this->sellable->service_group)->first();
        $this->multiple = $this->grouped->count() > 1;
        $this->qty = $this->grouped->sum('quantity');
        $this->distributed_qty = $this->distributed[$this->cart->service_id] ?? 0;

    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.order-service-attribution-row');
    }
}
