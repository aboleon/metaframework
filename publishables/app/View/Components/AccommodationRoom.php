<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AccommodationRoom extends Component
{
    public function __construct(public \App\Models\EventManager\Accommodation\Room $room)
    {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.accommodation-room');
    }
}
