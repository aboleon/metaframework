<?php

declare(strict_types=1);

namespace MetaFramework\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class EditLink extends Component
{
    public function __construct(public string $route) {}

    public function render(): View|Closure|string
    {
        return view('mfw::components.edit-link');
    }
}
