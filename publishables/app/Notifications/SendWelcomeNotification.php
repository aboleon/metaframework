<?php

namespace App\Notifications;

use App\Mail\Welcome;
use App\Models\UserRegistration;
use MetaFramework\Traits\Responses;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendWelcomeNotification
{
    use Responses;


    public function __construct(
        public UserRegistration $instance
    )
    {
    }

    public function __invoke(): array
    {

        try {
            Mail::to($this->instance->account->email)->send(new Welcome($this->instance));
        } catch (Throwable $e) {
            $this->responseException($e);
            report($e);
        }

        return $this->fetchResponse();
    }
}
