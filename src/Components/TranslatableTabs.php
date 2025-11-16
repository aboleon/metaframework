<?php

namespace MetaFramework\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use MetaFramework\Polyglote\Interfaces\TranslatableInterface;

class TranslatableTabs extends Component
{
    private string $default_id = 'tab_translateble';

    public function __construct(
        public TranslatableInterface $model,
        public ?string $id = null,
        public ?string $datakey = null,
        public array $fillables = [],
        public array $pluck = [],
        public bool $disabled = false,
        public bool $fallbacklocale = false,
        public ?string $selectedlocale = null,
    ) {
        $this->fillables = $this->model->getTranslatableProperties();
        if ($this->pluck) {
            $this->fillables = array_filter($this->fillables, fn($item) => in_array($item, $this->pluck), ARRAY_FILTER_USE_KEY);
        }

        $this->id = ! $this->id ? $this->default_id.'_'.Str::random() : $this->id;

        $this->selectedlocale = $this->selectedlocale == null ? app()->getLocale() : $this->selectedlocale;
    }

    public function render(): Renderable
    {
        return view('mfw::components.translatable-tabs');
    }
}
