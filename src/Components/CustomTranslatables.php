<?php

declare(strict_types=1);

namespace MetaFramework\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;

class CustomTranslatables extends Component
{
    public function __construct(
        public array $values,
        public Model $model,
        public string $locale
    ) {
        //
    }

    public function render(): Renderable
    {
        return view('mfw::components.custom-translatables');
    }
}
