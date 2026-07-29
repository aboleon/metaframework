<?php

declare(strict_types=1);

namespace MetaFramework\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\View\Component;

class ModalActions extends Component
{
    public function __construct(
        public string $reference,
        public string $title,
        public string $icon = '',
        public string $class = 'btn-danger'
    ) {
        $this->title = $this->title ?? __('mfw::mfw.deletion');
    }

    public function render(): Renderable
    {
        return view('mfw::components.modal-actions');
    }
}
