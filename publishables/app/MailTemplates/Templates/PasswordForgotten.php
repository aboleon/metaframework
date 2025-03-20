<?php

namespace App\MailTemplates\Templates;

use App\MailTemplates\Contracts\Template;
use App\MailTemplates\Traits\MailTemplate;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;

class PasswordForgotten implements Template
{
    use MailTemplate;

    public User $notifiable;

    public function signature(): string
    {
        return 'password_forgotten';
    }

    public function params(User $notifiable):static
    {
        $this->notifiable = $notifiable;
        return $this;
    }

    public function variables(): array
    {
        return [
            'user_email' => 'E-mail du compte',
            'reset_url' => 'Lien de réinitialisation',
        ];
    }

    public function user_email(): string
    {
        return request('email');
    }

    public function reset_url(): string
    {
        return  url(route('password.reset', [
            'token' => app('auth.password.broker')->createToken($this->notifiable),
            'email' => $this->notifiable->getEmailForPasswordReset(),
            'lg' => app()->getLocale()
        ], false));

    }


}
