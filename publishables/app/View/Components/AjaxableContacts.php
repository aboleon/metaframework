<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;

class AjaxableContacts extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $queryTag,
        public Model $model,
        public ?Collection $contacts
    )
    {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.ajaxable-contacts');
    }
}
