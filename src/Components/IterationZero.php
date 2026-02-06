<?php

declare(strict_types=1);

namespace MetaFramework\Components;

use Illuminate\View\Component;

class IterationZero extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public int $count = 0
    ) {}

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('mfw::components.iteration-zero');
    }
}
