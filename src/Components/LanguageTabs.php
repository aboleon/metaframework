<?php

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
        public ?string $active = null
    )
    {
        //
    }

    public function render(): Renderable
    {
        return view('mfw::components.language-tabs');
    }
}
