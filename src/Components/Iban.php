<?php

declare(strict_types=1);

namespace MetaFramework\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\View\Component;

class Iban extends Component
{
    public function __construct(
        public string $name,
        public ?string $value = null,
        public ?string $label= null,
        public ?string $class = null,
        public bool $required = false
    ) {
        $this->class = rtrim('iban-validator' . ' ' . $this->class);
    }

    public function render(): Renderable
    {
        return view('mfw::components.iban');
    }
}
