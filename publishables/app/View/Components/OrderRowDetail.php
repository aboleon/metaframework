<?php

namespace App\View\Components;

use App\Accessors\OrderAccessor;
use App\Models\EventContact;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\Component;
use MetaFramework\Accessors\Prices;

class OrderRowDetail extends Component
{
    public array $hotels = [];
    public array $totals = [];
    public ?Collection $accommodationCart = null;
    public ?Collection $serviceCart = null;
    public OrderAccessor $orderAccessor;
    public bool $wasAmended = false;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public Order $order,
        public Collection $services,
        public ?EventContact $eventContact = null,
    ) {
        $this->orderAccessor     = new OrderAccessor($order);
        $this->totals            = $this->orderAccessor->getOrderTotals();
        $this->serviceCart       = $this->orderAccessor->serviceCart();
        $this->accommodationCart = $this->orderAccessor->accommodationCart();


        if ($this->accommodationCart) {
            $this->hotels = \App\Printers\Event\Accommodation::simpleRecap(
                accommodationCart: $this->accommodationCart->load('eventHotel.hotel', 'eventHotel.roomGroups', 'room.room'),
                showPec: true
            );
        }
    }

    public function price(): string
    {
        return Prices::readableFormat($this->orderAccessor->isOrator() ? 0 : $this->totals['net'] + $this->totals['vat']);
    }

    public function paid(): string
    {
        return Prices::readableFormat($this->orderAccessor->isOrator() ? 0 : $this->order->payments->sum('amount'));
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()//: View|Closure|string
    {
        return view('components.order-row-detail');
    }

    public function hasPartialCancellation()
    {
        if (is_null($this->order->cancelled_at) && $this->orderAccessor->hasPartialCancellation()->isNotEmpty()) {
            return '<span class="d-block mb-2 smaller badge rounded-pill mfw-status offline">Partiellement annulé</span>';
        }
    }
}
