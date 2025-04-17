<?php

namespace MetaFramework\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\View\Component;

class SimpleModal extends Component
{
    /**
     * @param  string       $text          // Link text
     * @param  string       $id            // Modal ID
     * @param  string       $title         // Modal question
     * @param  string|null  $body          // Modal body text
     * @param  string|null  $confirm       // confirm btn text
     * @param  string|null  $cancel        // cancel btn text
     * @param  string       $confirmclass  // confirm btn class
     */

    public function __construct(
        public string $text,
        public string $id,
        public string $title,
        public ?string $linktitle = null,
        public ?int $modelid = null,
        public ?string $identifier = null,
        public ?string $class = null,
        public ?string $confirm = null,
        public ?string $callback = null,
        public ?string $onshow = null,
        public ?string $body = null,
        public ?string $cancel = null,
        public string $confirmclass = 'btn-success',
    ) {
        if ( ! $this->confirm) {
            $this->confirm = "<i class='fa-solid fa-check'></i>".__('mfw.confirm');
        }
        if ( ! $this->cancel) {
            $this->cancel = __('mfw.cancel');
        }
    }

    public function render(): Renderable
    {
        return view('mfw::components.simple-modal');
    }
}
