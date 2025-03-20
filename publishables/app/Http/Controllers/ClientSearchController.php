<?php

namespace App\Http\Controllers;

use App\Models\ClientSearch;
use Illuminate\Contracts\Support\Renderable;

class ClientSearchController extends Controller
{
    public function index(): Renderable
    {
        return view('accounts.index', [
            'data' => (new ClientSearch)->search()
        ]);
    }
}
