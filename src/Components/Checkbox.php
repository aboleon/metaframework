<?php

namespace MetaFramework\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use MetaFramework\Functions\Helpers;

class Checkbox extends Component
{
    public string $id;
    public bool $isSelected = false;

    public function __construct(
        public int|string|null $value,
        public string $name,
        public mixed $affected = null,
        public string|null $label = '',
        public string $class = '',
        public bool $switch = false,
        public array $params = [],
        public bool $randomize = true,
    ) {
        if (is_bool($this->affected)) {
            $this->isSelected = $this->affected;
        } else {
            if ( ! $this->affected instanceof Collection) {
                $this->affected = collect($this->affected);
            }
            $this->isSelected = $this->affected->contains($this->value);
        }

        $this->id   = Helpers::generateInputId($this->name.($this->randomize ? '_'.Str::random(8) : ''));
        $this->name = Helpers::generateInputName($this->name);
    }

    public function render(): Renderable
    {
        return view('mfw::components.checkbox');
    }
}
