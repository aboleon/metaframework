<?php

namespace App\View;

use App\Models\Order;
use App\Models\Order\Note;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class OrderNote extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public Note $note, public Order $order)
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.order-note');
    }
}
