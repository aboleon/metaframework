<?php

namespace App\View\Components;

use App\Accessors\OrderAccessor;
use App\Enum\OrderAmendedType;
use App\Models\EventManager\Accommodation;
use App\Models\Order;
use App\Models\Order\Cart\AccommodationCart;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class InvoiceRowAccommodation extends Component
{
    private ?Accommodation $accommodation;
    public array $room_groups = [];
    public bool $printDate = true;
    public string $printableDate = '';
    public ?AccommodationCart $amendedcart = null;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public AccommodationCart $cart,
        public OrderAccessor $orderAccessor,
        public array $hotels = [],
        public ?Order $amendedorder = null,
        public string $style = '',
        public string $title = 'Hébergement',
        public bool $isamended = false,
    ) {
        $this->printableDate = $cart->date->format('d/m/Y');
        $this->accommodation = Accommodation::find($this->cart->event_hotel_id);

        if ($this->accommodation) {
            $this->room_groups = $this->accommodation->roomGroups->pluck('name', 'id')->toArray();
        }

        if ($this->amendedorder) {
            if ($this->amendedorder->amend_type == OrderAmendedType::CART->value) {
                $this->amendedcart = $this->amendedorder->accommodation->filter(fn($item) => $item->id == $this->cart->amended_cart_id)->first();
            }
        }


    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.invoice-row-accommodation');
    }
}
