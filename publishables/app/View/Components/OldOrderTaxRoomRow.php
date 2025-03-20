<?php

namespace App\View\Components;

use App\Accessors\Dictionnaries;
use App\Models\EventManager\Accommodation;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class OldOrderTaxRoomRow extends Component
{
    public string $identifier;
    private ?Accommodation $accommodation;
    public array $room_groups = [];
    public string $printableDate = '';
    public string $room_label = '';
    public string $room_category = '';
    public int $capacity = 0;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public array $cart,
        public int   $iteration,
        public array $hotels = [],
        public bool  $invoiced = false)
    {
    }


    /**
     * Get the view / contents that represent the component.
     */
    public
    function render(): View|Closure|string
    {
        return view('components.order-tax-room-row');
    }
}
