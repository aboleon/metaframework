<?php

declare(strict_types=1);

namespace MetaFramework\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\View\Component;

class DevMenu extends Component
{
    public function render(): Renderable
    {
        return view('mfw::components.dev-menu');
    }
}
