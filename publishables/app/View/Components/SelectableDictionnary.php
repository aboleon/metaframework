<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SelectableDictionnary extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public string $key,
        public string $name,
        public ?int $affected,
    )
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.selectable-dictionnary');
    }
}
