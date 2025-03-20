<?php

namespace App\View\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\View\Component;

class MailTemplate extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function render(): Renderable
    {
        return view('layouts.mailtemplate');
    }
}
