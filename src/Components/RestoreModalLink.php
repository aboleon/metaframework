<?php

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
    )
    {
        $this->title = $this->title ?: __('aboleon-framework.restore');
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('aboleon-framework::components.restore-modal-link');
    }
}
