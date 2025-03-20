<?php

namespace App\View\Components;

use App\Models\EventManager\Accommodation;
use App\Models\Hotel;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class OldOrderAccommodationRow extends Component
{
    public string $identifier;
    private ?Accommodation $accommodation;
    public array $room_groups = [];
    public array $rooms = [];
    public bool $printDate = true;
    public string $room_label = '';
    public string $room_category = '';
    public string $hotel = '';
    public string $printableDate = '';
    public int $quantity = 1;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public array $cart,
        public string $date,
        public int $iteration,
        public array $hotels = []
    )
    {
        $this->identifier = Str::random();
        $this->printableDate = Carbon::createFromFormat('Y-m-d',$this->date)->format('d/m/Y');

        $this->accommodation = Accommodation::find($this->cart['event_hotel_id'][$iteration]);

        if ($this->accommodation) {
            $this->room_groups = $this->accommodation->roomGroups->pluck('name', 'id')->toArray();
            $this->rooms = $this->accommodation->roomGroups->load('rooms.room')->pluck('rooms')->flatten()->pluck('room.name','id')->toArray();
        }



        $this->room_label = $this->rooms[$this->cart['room_id'][$iteration]] ?? 'NC';
        $this->room_category = $this->room_groups[$this->cart['room_group_id'][$iteration]] ?? 'Inconnue';
        $this->hotel = $this->hotels[$this->cart['event_hotel_id'][$iteration]];
/*
        d($this->room_groups);
        de($this->rooms);
*/
        $this->quantity = $this->cart['quantity'][$iteration];
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.old-order-accommodation-row');
    }
}
