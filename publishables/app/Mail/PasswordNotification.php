<?php

namespace App\Mail;

use App\Accessors\Cached;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordNotification extends Mailable
{
    use Queueable, SerializesModels;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        public string $password,
        public User $user)
    {
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this
            ->to($this->user->email)
            ->from(Cached::settings('email_notification'))
            ->subject('Votre mot de passe')
            ->view('mails.password-notification');
    }


}
