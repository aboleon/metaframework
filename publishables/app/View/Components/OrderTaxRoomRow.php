<?php

namespace App\View\Components;

use App\Accessors\Dictionnaries;
use App\Models\EventManager\Accommodation;
use App\Models\Order\Cart\TaxRoomCart;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use MetaFramework\Accessors\VatAccessor;

class OrderTaxRoomRow extends Component
{
    public string $identifier;
    private ?Accommodation $accommodation;
    public array $room_groups = [];
    public string $printableDate = '';
    public string $room_label = '';
    public string $room_category = '';
    public int $capacity = 0;

    public int|float|null $pec_price_net = 0;
    public int|float|null $pec_price_vat = 0;
    public int|float|null $pec_allocation_net = 0;
    public int|float|null $pec_allocation_vat = 0;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public TaxRoomCart $cart,
        public array       $hotels = [],
        public bool        $invoiced = false)
    {

        $this->identifier = Str::random();

        if ($this->cart->id) {
            $this->accommodation = Accommodation::find($this->cart->event_hotel_id);
            if ($this->accommodation) {
                $this->room_groups = $this->accommodation->roomGroups->pluck('name', 'id')->toArray();
            }
            $this->capacity = $this->cart->room->capacity;
            $this->room_label = Dictionnaries::entry('type_chambres', $this->cart->room->room_id)->name . ' x ' . $this->capacity;
            $this->room_category = $this->room_groups[$this->cart->room->room_group_id] ?? 'Inconnue';


            $this->pec_price_net = $cart->amount_pec ? $cart->amount_net : 0;
            $this->pec_price_vat = $cart->amount_pec ?  $cart->amount_vat : 0;

            $this->pec_allocation_vat = $cart->amount_pec ? VatAccessor::vatForPrice($cart->amount_pec, $cart->vat_id) : 0;
            $this->pec_allocation_net = $cart->amount_pec ? VatAccessor::netPriceFromVatPrice($cart->amount_pec, $cart->vat_id) : 0;
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.order-tax-room-row');
    }
}
