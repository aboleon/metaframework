<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Contact extends Mailable
{
    use Queueable, SerializesModels;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
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
            ->from(['address'=> config('mail.from.address'), 'name'=> config('app.name')])
            ->replyTo([
                'address' => request('email')
            ])
            ->subject('Message Site Internet')
            ->view('mails.contact');
    }
}
