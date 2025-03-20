<?php

namespace App\View\Components;

use App\Models\Dictionnary;
use App\Models\DictionnaryEntry;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\View\Component;

class DicoRowLooper extends Component
{
    public function __construct(
        public DictionnaryEntry $item,
        public ?Dictionnary     $dictionnary,
        public int              $level = 0,
        public                  $can_delete = false
    )
    {
        //
    }

    public function render(): Renderable
    {
        return view('components.dico-row-looper');
    }
}
