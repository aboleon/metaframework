<?php

namespace App\View\Components;

use App\Models\EventManager\Accommodation\Room;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class ContingentConfig extends Component
{
    public ?Room $room;

    public function __construct(
        public \App\Models\EventManager\Accommodation\ContingentConfig $config,
        public array $services,
        public Collection $rooms,
        public string $row,
        public int $rowspan = 0,
        public bool $deletable = false
    )
    {
        $this->room = $this->rooms->where('id', $this->config->room_id)->first();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.contingent-config');
    }
}
