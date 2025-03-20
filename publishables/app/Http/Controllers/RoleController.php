<?php

namespace App\Http\Controllers;

use App\Models\{
    User,
    UserRole};
use Illuminate\Contracts\Support\Renderable;

class RoleController extends Controller
{
    public function index(): Renderable
    {
        return view('roles.index')->with([
            'data' => (new User)->userTypes(),
            'roles' => UserRole::selectRaw('count(user_id) as users, role_id')->groupBy('role_id')->pluck('users','role_id')->toArray()
        ]);
    }
}
