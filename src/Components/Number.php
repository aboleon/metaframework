<?php

namespace MetaFramework\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use MetaFramework\Functions\Helpers;

class Number extends Component
{
    private string $id;
    private string $validation_id;
    public function __construct(
        public string $name,
        public int|float|null $value = null,
        public int $min = 0,
        public ?int $max = null,
        public int|float|string $step = 'any',
        public string|null $label = '',
        public string $class = '',
        public bool $required = false,
        public bool $readonly = false,
        public array $params = [],
        public bool $randomize = false,
        public string $prefix = ''
    )
    {
        $this->id = Helpers::generateInputId($this->name . ($this->randomize ? '_' . Str::random(8) : ''));
        $this->validation_id = Helpers::generateValidationId($this->name);
        $this->name = Helpers::generateInputName($this->name);
    }

    public function render(): Renderable
    {
        $this->params['min'] = $this->min;
        $this->params['step'] = $this->step;

        if ($this->max) {
            $this->params['max'] = $this->max;
        }

        return view('mfw::components.input')->with([
            'id' => $this->id,
            'validation_id' => $this->validation_id,
            'type' => 'number',
            'label' => $this->label,
            'class' => $this->class,
            'required' => $this->required,
            'readonly' => $this->readonly,
            'value' => $this->value,
            'params' => $this->params,
            'prefix' => $this->prefix
        ]);
    }
}
