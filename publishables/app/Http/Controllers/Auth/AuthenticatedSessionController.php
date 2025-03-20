<?php

namespace App\Http\Controllers\Auth;

use App\Enum\UserType;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController as FortifyController;
use \Laravel\Fortify\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends FortifyController
{
    public function store(LoginRequest $request)
    {
        // First, call the parent store method to authenticate the user
        $response = parent::store($request);

        // Check the user type after authentication
        $user = Auth::user();
        if ($user->type !== UserType::SYSTEM->value) {
            Auth::logout(); // Log out the user
            return redirect()->route('login')->withErrors(['type' => __('auth.not_authorized')]);
        }

        return $response;
    }
}
