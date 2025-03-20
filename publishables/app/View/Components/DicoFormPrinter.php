<?php

namespace App\View\Components;

use App\Models\DictionnaryEntry;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class DicoFormPrinter extends Component
{
    public function __construct(
        public DictionnaryEntry $item,
        public string           $formTag,
        public ?Collection      $affected,
        public string           $tag = 'div',
        public int              $level = 0,
        public bool             $filter = false,
        public array            $subset = [],
        public bool             $alltranslations = false
    )
    {
        $this->affected = $this->affected ?? collect();
    }

    public function render(): Renderable
    {
        return view('components.dico-form-printer');
    }
}
