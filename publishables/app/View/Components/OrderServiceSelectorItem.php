<?php

namespace App\View\Components;

use App\Models\DictionnaryEntry;
use App\Models\Event;
use App\Models\EventManager\Sellable;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class OrderServiceSelectorItem extends Component
{
    public DictionnaryEntry $group;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public Sellable $service,
        public Event $event,
        public Collection $families,
        public array $availability,
        public array $pecbooked = []
    )
    {
        $this->group = $this->event->services->where('id', $this->service->service_group)->first();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.order-service-selector-item');
    }
}
