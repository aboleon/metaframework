<?php

declare(strict_types=1);

namespace MetaFramework\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\View\Component;

class SimpleModal extends Component
{
    /**
     * @param  string  $text  // Link text
     * @param  string  $id  // Modal ID
     * @param  string  $title  // Modal question
     * @param  string|null  $body  // Modal body text
     * @param  string|null  $confirm  // confirm btn text
     * @param  string|null  $cancel  // cancel btn text
     * @param  string  $confirmclass  // confirm btn class
     */
    public function __construct(
        /**
         * @var string
         *             Attached as class to the confirm button, use to trigger behaviour in a callback function.
         */
        public string $id,

        /**
         * @var string
         *             Modal title.
         */
        public ?string $title = null,

        /**
         * @var string
         *             Modal text.
         */
        public ?string $text = null,

        /**
         * @var string|null
         *                  Bubble link title.
         */
        public ?string $linktitle = null,

        /**
         * @var int|null
         *               ID of the model to be operated on.
         */
        public ?int $modelid = null,

        /**
         * @var string|null
         *                  Identifier attached for various purposes.
         */
        public ?string $identifier = null,

        /**
         * @var string|null
         *                  CSS class to be attached to link button triggering the modal.
         */
        public ?string $class = null,

        /**
         * @var string|null
         *                  Callback function to be executed when confirm button is clicked.
         */
        public ?string $callback = null,

        /**
         * @var string|null
         *                  Callback function to be executed when modal is shown.
         */
        public ?string $onshow = null,

        /**
         * @var string|null
         *                  Modal body text.
         */
        public ?string $body = null,

        /**
         * @var string|null
         *                  Button confirm text.
         */
        public ?string $confirm = null,

        /**
         * @var string|null
         *                  Button cancel text.
         */
        public ?string $cancel = null,

        /**
         * @var string
         *             Button confirm class.
         */
        public string $confirmclass = 'btn-success',
        /**
         * @var string
         *             .modal-sm 300px, default 500px, .modal-lg 800px, .modal-xl 1140px
         */
        public string $modalsize = '',
        /**
         * @var bool
         *           Show or not modal footer
         */
        public bool $footer = true,
    ) {
        if (! $this->confirm) {
            $this->confirm = "<i class='fa-solid fa-check'></i>".__('mfw::mfw.confirm');
        }
        if (! $this->cancel) {
            $this->cancel = __('mfw::mfw.cancel');
        }
    }

    public function render(): Renderable
    {
        return view('mfw::components.simple-modal');
    }
}
