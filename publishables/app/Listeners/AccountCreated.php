<?php

namespace App\Listeners;

use App\Events\AccountRegistered;
use App\Notifications\SendWelcomeNotification;

class AccountCreated
{
    public function handle(AccountRegistered $event): void
    {
        (new SendWelcomeNotification($event->instance))();
    }
}
