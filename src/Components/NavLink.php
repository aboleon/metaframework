<?php

declare(strict_types=1);

namespace MetaFramework\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\View\Component;

class NavLink extends Component
{
    public function __construct(
        public string $route,
        public string $title,
        public string $icon = '',
        public string $className='',
        public string $target='_self'
    ) {}

    public function render(): Renderable
    {
        return view('mfw::components.nav-link');
    }
}
