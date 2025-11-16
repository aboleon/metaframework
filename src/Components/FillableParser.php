<?php

namespace MetaFramework\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\View\Component;
use MetaFramework\Polyglote\Interfaces\TranslatableInterface;

class FillableParser extends Component
{
    public function __construct(
        public object $model,
        public string $locale,
        public ?string $datakey = null,
        public array $fillables = [],
        public bool $disabled = false,
        public array $parsed = [],
        public bool $fallbacklocale = false
    ) {
        if ( ! $this->parsed) {
            $this->fillables = $this->model instanceof TranslatableInterface ? $this->model->getTranslatableProperties() : ((array)$this->fillables ?: []);
        }
    }

    public function render(): Renderable
    {
        return view('mfw::components.fillables-parser');
    }
}
