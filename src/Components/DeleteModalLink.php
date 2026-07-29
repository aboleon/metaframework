<?php

declare(strict_types=1);

namespace MetaFramework\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DeleteModalLink extends Component
{
    private string $params_as_string = ' ';

    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $reference,
        public ?string $title = null,
        public array $params = []
    ) {
        $this->title = $this->title ?: __('mfw::mfw.delete');

        if ($this->params) {
            foreach ($this->params as $param => $setting) {
                $this->params_as_string .= $param.'="'.$setting.'" ';
            }
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('mfw::components.delete-modal-link')->with('params_as_string', rtrim($this->params_as_string));
    }
}
