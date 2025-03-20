<?php

namespace App\View\Components;

use App\Accessors\Dictionnaries;
use App\Enum\ClientType;
use App\Enum\OrderClientType;
use App\Models\Event;
use App\Models\EventManager\Accommodation;
use App\Models\Order;
use App\Models\Order\Cart\AccommodationCart;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use MetaFramework\Accessors\VatAccessor;
use Throwable;

class OrderAccommodationRow extends Component
{
    public string $identifier;
    private ?Accommodation $accommodation;
    public array $room_groups = [];
    public bool $printDate = true;
    public string $printableDate = '';
    public string $room_label = '';
    public string $room_category = '';
    public int $capacity = 0;
    public int|float|null $pec_price_net = 0;
    public int|float|null $pec_price_vat = 0;
    public int|float|null $pec_allocation_net = 0;
    public int|float|null $pec_allocation_vat = 0;
    public int $to_attribute = 0;
    public int $attributed = 0;
    public bool $lockQuantity = false;

    public bool $canAmendOrCancel = true;


    /**
     * Create a new component instance.
     */
    public function __construct(
        public Event $event,
        public Order $order,
        public AccommodationCart $cart,
        public array $dates = [],
        public array $hotels = [],
        public bool $invoiced = false,
        public bool $amendablemode = false,
        public bool $amendable = false,
        public array $attributions = [],
    ) {
        $this->identifier = Str::random();
        if ($this->cart->id) {
            $this->pec_price_net = $cart->total_pec ? $cart->total_net : 0;
            $this->pec_price_vat = $cart->total_pec ?  $cart->total_vat : 0;
            try {
                $this->printableDate = $cart->date->format('d/m/Y');
            } catch (Throwable) {
                $this->printableDate = 'NC';
            }
            $this->accommodation = Accommodation::find($this->cart->event_hotel_id);

            if ($this->accommodation) {
                $this->room_groups = $this->accommodation->roomGroups->pluck('name', 'id')->toArray();
            }
            $date = $this->cart->getRawOriginal('date');

            if (in_array($date, $this->dates)) {
                $this->printDate = false;
            }
            $this->room_label    = Dictionnaries::entry('type_chambres', $this->cart->room->room_id)->name.' x '.$this->cart->room->capacity;
            $this->room_category = $this->room_groups[$this->cart->room->room_group_id] ?? 'Inconnue';
            $this->capacity      = $this->cart->room->capacity;

            $this->pec_allocation_vat = $cart->total_pec ? VatAccessor::vatForPrice($cart->total_pec / $cart->quantity, $cart->vat_id) : 0;
            $this->pec_allocation_net = $cart->total_pec ? VatAccessor::netPriceFromVatPrice($cart->total_pec / $cart->quantity, $cart->vat_id) : 0;

            $this->getAttributions();
            $this->lockQuantity();

        }
        $this->canAmendOrCancel = ! ($this->order->client_type == OrderClientType::GROUP->value && $this->cart->computedQuantity() > 0);
    }

    private function getAttributions(): void
    {
        if ( ! $this->attributions) {
            return;
        }
        $attribution = collect($this->attributions)->filter(fn($item) => $item->date == $this->cart->getRawOriginal('date') && $item->room_id == $this->cart->room_id)->first();

        if ($attribution) {
            $this->to_attribute = $attribution->total_quantity - $attribution->attributed;
            $this->attributed   = $attribution->attributed;
        }
    }

    private function lockQuantity(): void
    {
        if ( ! $this->attributions) {
            return;
        }

        $this->lockQuantity = $this->to_attribute == 0;
    }


    /**
     * Get the view / contents that represent the component.
     */
    public
    function render(): View|Closure|string
    {
        return view('components.order-accommodation-row');
    }
}
