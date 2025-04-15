<?php

namespace MetaFramework\Mediaclass\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\View\Component;

class Uploadable extends Component
{
    public function __construct(
        public object $model,
        public bool   $positions = false,
        public string $group = 'media',
        public string $size = '',
        public string $label = 'Médias',
        public int|bool $description = true,
        public ?string $cropable = null,
        /**
         * @var int
         * max number of files to be uploaded
         */
        public int $limit = 0,
        /**
         * @var string|null
         * ex 500KB, 5MB (default is 16MB)
         */
        public ?string $maxfilesize = null,
        public array $settings = [],
        public string $icon = 'bi bi-card-image',
        public string $nomedia = ''
    )
    {
        $this->group = $this->settings['group'] ?? $this->group;
        $this->label = $this->settings['label'] ?? $this->label;
        $this->description = $this->description ? 1 : 0;
        $this->nomedia = $this->nomedia ?: __('mediaclass.no_media');
    }


    public function render(): Renderable
    {
        return view('mediaclass::components.uploadable');
    }

}
