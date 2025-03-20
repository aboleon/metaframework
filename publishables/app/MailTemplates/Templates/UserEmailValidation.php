<?php

namespace App\MailTemplates\Templates;

use App\MailTemplates\Contracts\Template;
use App\MailTemplates\Traits\MailTemplate;
use App\Models\User;

class UserEmailValidation implements Template
{
    use MailTemplate;

    private User $user;
    private string $verification_url;

    public function signature(): string
    {
        return 'user_email_validation';
    }

    public function params(User $user, string $verification_url):static
    {
        $this->user = $user;
        $this->verification_url = $verification_url;
        return $this;
    }

    public function variables(): array
    {
        return [
            'first_name' => 'Prénom',
            'verification_url' => 'Lien de confirmation'
        ];
    }

    public function first_name(): string
    {
        return $this->user->first_name;
    }

    public function verification_url(): string
    {
        return $this->verification_url;
    }

}
