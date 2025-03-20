<?php

namespace App\View\Components;

use App\Models\EventContact;
use App\Models\Order;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class OrderCancellationStatus extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public Order $order,
        public array $eventContact
    )
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.order-cancellation-status');
    }
}
