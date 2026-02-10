<?php

declare(strict_types=1);

namespace MetaFramework\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use MetaFramework\Models\RoleGroup;

class RolesManager extends Component
{
    public Collection $groups;

    public function __construct(public Collection $roles)
    {
        $this->groups = RoleGroup::query()->orderBy('key')->get();
    }

    public function render(): Renderable
    {
        return view('mfw::components.roles-manager');
    }
}
