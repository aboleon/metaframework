<?php

namespace MetaFramework\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use MetaFramework\Functions\Helpers;

class Textarea extends Component
{
    private string $id;
    private string $validation_id;

    public function __construct(
        public string       $name,
        public ?string      $value = null,
        public ?string      $label = null,
        public string|array $class = '',
        public array        $params = [],
        public int          $height = 200,
        public bool         $required = false,
        public bool         $readonly = false,
        public bool         $randomize = false
    )
    {
        $this->id = Helpers::generateInputId($this->name. ($this->randomize ? '_'.Str::random(8):''));
        $this->validation_id = Helpers::generateValidationId($this->name);
        $this->name = Helpers::generateInputName($this->name);
        if (array_key_exists('height', $this->params)) {
            $this->height = $this->params['height'];
        }
    }

    public function render(): Renderable
    {
        return view('mfw::components.textarea')->with([
            'id' => $this->id,
            'validation_id' => $this->validation_id,
        ]);
    }
}
