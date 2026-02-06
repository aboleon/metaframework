<?php

declare(strict_types=1);

namespace MetaFramework\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\View\Component;

class Notice extends Component
{
    public function __construct(
        public ?string $message = null,
        public string $class = ''
    ) {}

    public function render(): Renderable
    {
        return view('mfw::components.notice');
    }
}
