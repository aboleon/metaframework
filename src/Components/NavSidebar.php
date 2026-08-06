<?php

declare(strict_types=1);

namespace MetaFramework\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\View\Component;
use MetaFramework\Navigation\NavigationItem;
use MetaFramework\Navigation\PanelNavigation;

class NavSidebar extends Component
{
    public function __construct(
        private readonly PanelNavigation $navigation,
        public string $id = 'mfw-nav-sidebar',
        public ?string $logoUrl = null,
        public ?string $logoAlt = null,
    ) {
        $this->logoUrl ??= asset('media/logo.png');
        $this->logoAlt ??= (string) config('app.name');
    }

    public function render(): Renderable
    {
        /** @var array<int, NavigationItem> $items */
        $items = $this->navigation->items();

        return view('mfw::components.nav-sidebar', compact('items'));
    }
}
