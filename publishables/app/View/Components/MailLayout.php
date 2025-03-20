<?php

namespace App\View\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\View\Component;

class MailLayout extends Component
{

    public ?string $banner;

    public function __construct(?string $banner = null)
    {
        $this->banner = $banner;
    }

    public function render(): Renderable
    {
        return view('layouts.mail');
    }
}
