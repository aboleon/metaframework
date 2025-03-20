<?php

namespace App\View\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\View\Component;

class PdfLayout extends Component
{
    public function render(): Renderable
    {
        return view('layouts.pdf');
    }
}
