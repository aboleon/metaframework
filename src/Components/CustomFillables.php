<?php

namespace Aboleon\MetaFramework\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;

class CustomFillables extends Component
{
    public function __construct(
        public array $values,
        public Model $model,
    )
    {

    }


    public function render(): Renderable
    {
        return view('aboleon-framework::components.custom-fillables');
    }
}
