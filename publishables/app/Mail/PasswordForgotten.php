<?php

namespace App\Mail;

use App\Accessors\Cached;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordForgotten extends Mailable
{
    use Queueable, SerializesModels;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        public string $email,
        public string $reset_url)
    {
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->to($this->email)
            ->from(Cached::settings('email_notification'))
            ->subject('Votre mot de passe a été réinitialisé')
            ->view('mails.password-forgotten');
    }


}
