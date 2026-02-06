<?php

declare(strict_types=1);

namespace MetaFramework\Components;

use Illuminate\View\Component;

class Pagination extends Component
{
    public function __construct(public object $object) {}

    public function render()
    {
        return view('mfw::components.pagination');
    }
}
