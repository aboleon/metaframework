<?php

namespace App\View\Components;

use App\Models\Event;
use App\Models\Order\RefundItem;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class RefundPrintRow extends Component
{
    public string $identifier;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public RefundItem $model,
        public Event $event,
        public string $uuid,
        public int $iteration,
        public int $total
    )
    {
        $this->identifier = Str::random();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.refund-print-row');
    }
}
