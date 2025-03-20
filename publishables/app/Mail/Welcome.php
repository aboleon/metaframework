<?php

namespace App\Mail;

use App\Accessors\Cached;
use App\Models\Account;
use App\Models\UserRegistration;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Welcome extends Mailable
{
    use Queueable, SerializesModels;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(public UserRegistration $instance)
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
            ->from(Cached::settings('email_notification'))
            ->subject('Confirmation de création de compte')
            ->view('mails.welcome');
    }
}
