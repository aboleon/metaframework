<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SelectOpt extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public array $values,
        public string $name,
        public mixed $affected,
        public string $label = '',
        public bool $nullable = true,
        public bool $disablename = false,
        public string $defaultselecttext = ''
    )
    {
        $this->defaultselecttext = $this->defaultselecttext ?: '---  '. trans('ui.select_option') .' ---';
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.select-opt');
    }
}
