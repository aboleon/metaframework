<?php

namespace App\View\Components;

use App\Models\EventManager\Sellable\Price;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class TimeBindedPriceRow extends Component
{
    public string $identifier;


    /**
     * Create a new component instance.
     */
    public function __construct(
        public Price $price,
        public string $prefix,
        public string $callback
    )
    {
        $this->identifier = Str::random();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.time-binded-price-row');
    }
}
