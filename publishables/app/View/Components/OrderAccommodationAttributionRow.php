<?php

namespace App\View\Components;

use App\Models\Event;
use App\Models\EventManager\Accommodation\Room;
use App\Models\Order\Cart\AccommodationAttribution;
use App\Models\Order\Cart\AccommodationCart;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class OrderAccommodationAttributionRow extends Component
{
    private ?\App\Models\EventManager\Accommodation $accommodation;
    public ?Room $room;

    public string $identifier;
    public int $distributed_qty = 0;
    public int $qty = 1;
    public bool $multiple = false;


    /**
     * Create a new component instance.
     */
    public function __construct(
        public AccommodationAttribution  $attribution,
        public Event             $event,
        public array             $hotels,
        public Collection        $rooms,
        public array             $distributed = []
    )
    {
        $this->identifier = Str::random();
        $this->room = $this->rooms->where('id', $this->cart->room_id)->first();
        $this->qty = $this->cart->quantity;
        $this->distributed_qty = $this->cart->attributions->sum('quantity');
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.order-accommodation-attribution-row');
    }
}
