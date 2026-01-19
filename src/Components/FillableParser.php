<?php

namespace MetaFramework\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\View\Component;
use MetaFramework\Polyglote\Interfaces\TranslatableInterface;

class FillableParser extends Component
{
    public function __construct(
        public object $model,
        public string $locale,
        public ?string $datakey = null,
        public array $fillables = [],
        public bool $disabled = false,
        public array $parsed = [],
        public bool $fallbacklocale = false
    ) {
        if ( ! $this->parsed) {
            $this->fillables = $this->model instanceof TranslatableInterface ? $this->model->getTranslatableProperties() : ((array)$this->fillables ?: []);
        }

        $normalized = [];
        foreach ($this->fillables as $key => $value) {
            if (is_string($value)) {
                if (is_int($key)) {
                    $key = $value;
                }

                $normalized[$key] = [
                    'label' => $value,
                    'type' => 'input',
                ];
                continue;
            }

            if ($value === null && is_string($key)) {
                $normalized[$key] = [
                    'label' => $key,
                    'type' => 'input',
                ];
                continue;
            }

            $normalized[$key] = $value;
        }

        $this->fillables = $normalized;
    }

    public function render(): Renderable
    {
        return view('mfw::components.fillables-parser');
    }
}
