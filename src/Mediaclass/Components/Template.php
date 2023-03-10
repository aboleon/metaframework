<?php

namespace MetaFramework\Mediaclass\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\View\Component;

class Template extends Component
{
    public function __construct(
        public object $model,
        public bool $positions=false,
        public string $group = 'media'
    )
    {
    }

    public function render(): Renderable
    {
        return view('mediaclass::components.template');
    }

}
