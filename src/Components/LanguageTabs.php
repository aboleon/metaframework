<?php

declare(strict_types=1);

namespace MetaFramework\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\View\Component;

class LanguageTabs extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public string $id = 'tab_translatable',
        public ?string $selectedlocale = null
    ) {
        //
    }

    public function render(): Renderable
    {
        return view('mfw::components.language-tabs');
    }
}
