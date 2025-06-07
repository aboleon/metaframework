<?php

namespace MetaFramework\Components;

use Illuminate\View\Component;

class ResponseMessages extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public string $id = 'mfw-messages',
        public string $ajax = '',
    ) {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('mfw::components.response-messages');
    }
}
