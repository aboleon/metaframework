<?php

namespace App\MailTemplates\Templates;

use App\Interfaces\UserInterface;
use App\MailTemplates\Contracts\Template;
use App\MailTemplates\Traits\MailTemplate;

class WelcomeUserPassword implements Template
{

    use MailTemplate;

    private UserInterface $user;
    private string $password;

    public function signature(): string
    {
        return 'welcome_user_password';
    }

    public function params(UserInterface $user, string $password):static
    {
        $this->user = $user;
        $this->password = $password;

        return $this;
    }

    public function variables(): array
    {
        return [
            'first_name' => 'Prénom',
            'password' => "Mot de passe",
            'email' => "e-mail de connexion",
        ];
    }

    public function first_name(): string
    {
        return $this->user->first_name;
    }

    public function email(): string
    {
        return $this->user->email;
    }

    public function password(): string
    {
        return $this->password;
    }

}
