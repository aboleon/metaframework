<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class InvoiceableLink extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $type,
        public string $identifier,
        public string $title,
        public string $btnClass = 'btn-secondary',
        public string $icon = 'file-pdf',
    )
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.buttons.invoiceable-link');
    }
}
