<?php

declare(strict_types=1);

namespace MetaFramework\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class RestoreModalLink extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $reference,
        public ?string $title = null,
    ) {
        $this->title = $this->title ?: __('mfw::mfw.restore');
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('mfw::components.restore-modal-link');
    }
}
