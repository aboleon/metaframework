<?php

namespace App\View\Components;

use App\Models\Order\RoomNote;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class RoomNoteRow extends Component
{
    public string $identifier;

    /**
     * Create a new component instance.
     */
    public function __construct(public RoomNote $model)
    {
        $this->identifier = Str::random();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.roomnotes-row');
    }
}
