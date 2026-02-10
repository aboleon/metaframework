<?php

declare(strict_types=1);

namespace MetaFramework\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class RolesManager extends Component
{
    public function __construct(public Collection $roles) {}

    public function render(): Renderable
    {
        return view('mfw::components.roles-manager');
    }
}
