<?php

namespace MetaFramework\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\View\Component;
use MetaFramework\Models\Forms;

class Form extends Component
{
    public function __construct(
        public ?Forms $form,
        public string $label = '',
        public string $btn = ''
    )
    {
        //
    }

    public function render(): ?Renderable
    {
        return $this->form ? view('mfw::components.form') : null;
    }
}
