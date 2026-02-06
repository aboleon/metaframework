<?php

declare(strict_types=1);

namespace MetaFramework\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\View\Component;

class NavOpeningHeader extends Component
{
    public function __construct(
        public string $class='sh-blue-grey',
        public ?string $icon = null,
        public string $title = ''
    ) {
        //
    }

    public function render(): Renderable
    {
        return view('mfw::components.nav-opening-header');
    }
}
