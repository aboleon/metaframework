<?php

namespace App\View\Components;

use App\Models\EventManager\Accommodation;
use App\Models\Order\Cart\TaxRoomCart;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class InvoiceRowTaxcart extends Component
{
    private ?Accommodation $accommodation;
    public array $room_groups = [];

    /**
     * Create a new component instance.
     */
    public function __construct(
        public TaxRoomCart $cart,
        public array       $hotels = []
    )
    {
        $this->accommodation = \App\Models\EventManager\Accommodation::find($this->cart->event_hotel_id);

        if ($this->accommodation) {
            $this->room_groups = $this->accommodation->roomGroups->pluck('name', 'id')->toArray();
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.invoice-row-taxcart');
    }
}
