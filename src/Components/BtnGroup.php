<?php

declare(strict_types=1);

namespace MetaFramework\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\View\Component;

class BtnGroup extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public array $values,
        public string $name,
        public int|string|null $affected,
        public ?string $label = '',
    ) {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): Renderable
    {
        return view('mfw::components.btn-group');
    }
}
