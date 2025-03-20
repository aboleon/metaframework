<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Throwable;

class LoginSuccessListener
{
    public function handle(Login $event): void
    {
        try {
            $user = $event->user;
            $user->last_login_at = now();
            $user->save();
        } catch (Throwable $th) {
            report($th);
        }
    }
}
