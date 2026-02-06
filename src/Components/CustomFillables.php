<?php

declare(strict_types=1);

namespace MetaFramework\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;

class CustomFillables extends Component
{
    public function __construct(
        public array $values,
        public Model $model,
    ) {}

    public function render(): Renderable
    {
        return view('mfw::components.custom-fillables');
    }
}
