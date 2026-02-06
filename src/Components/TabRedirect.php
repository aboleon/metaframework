<?php

declare(strict_types=1);

namespace MetaFramework\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\View\Component;

/**
 * Usage:
 *
 * @depends \src\Traits\Responses
 * 1. blade file, inside form tag : <x-mfw::tab-redirect />
 * 2. controller : $this->tabRedirect();
 */
class TabRedirect extends Component
{
    public function __construct(
        public string $selector = '.mfw-tab',
    ) {}

    public function render(): Renderable
    {
        return view('mfw::components.tab-redirect');
    }
}
