<?php

namespace App\Mail;

use App\MailTemplates\Contracts\Template;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Test extends Mailable
{
    use Queueable, SerializesModels;


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this
            ->subject('Test mail')
            ->view('mails.test');

    }
}
