<?php

declare(strict_types=1);

namespace MetaFramework\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\View\Component;

class BtnSave extends Component
{
    public string $label = '';

    public ?string $back = null;

    public function __construct(string $label = '', ?string $back = null)
    {
        $this->label = $label ?: __('mfw::mfw.save');
        $this->back = $back;
    }

    public function render(): Renderable
    {
        return view('mfw::components.btn-save');
    }
}
