<?php

namespace MetaFramework\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Str;
use Illuminate\View\Component;

use function Symfony\Component\Translation\t;

class TranslatableTabs extends Component
{
    private string $default_id = 'tab_translateble';

    public function __construct(
        public object $model,
        public ?string $id = null,
        public ?string $datakey = null,
        public array $fillables = [],
        public array $pluck = [],
        public bool $disabled = false
    )
    {
        $this->fillables = $this->fillables ?: $this->model->fillables;
        if ($this->pluck) {
            $this->fillables = array_filter($this->fillables, fn($item) => in_array($item, $this->pluck), ARRAY_FILTER_USE_KEY);

        }

         $this->id = !$this->id ? $this->default_id .'_' . Str::random() : $this->id;
    }

    public function render(): Renderable
    {
        return view('mfw::components.translatable-tabs');
    }
}
