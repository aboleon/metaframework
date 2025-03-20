<?php

namespace App\Notifications;

use App\Mail\PasswordNotification;
use App\Models\User;
use MetaFramework\Services\Passwords\PasswordBroker;
use MetaFramework\Traits\Responses;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendPasswordNotification
{
    use Responses;

    public function __construct(
        public PasswordBroker $broker,
        public User           $user
    )
    {
    }

    public function __invoke(): self
    {

        try {
            Mail::to($this->user->email)->send(new PasswordNotification($this->broker->printPublicPassword(), $this->user));
            $this->responseSuccess("Le mot de passe a été envoyé à ".$this->user->email);
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this;
    }
}
