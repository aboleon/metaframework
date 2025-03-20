<?php

namespace App\Http\Controllers;

use App\Accessors\EventAccessor;
use Illuminate\Contracts\Support\Renderable;

class DashboardController extends Controller
{
    public function show(): Renderable
    {
        return view('dashboard');

    }

}
