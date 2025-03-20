<?php

namespace App\MailTemplates\Mail;

use App\MailTemplates\Contracts\Template;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailTemplate extends Mailable
{
    use Queueable, SerializesModels;


    public string $mail_subject;
    public string $mail_content;
    public array $attachment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Template $template)
    {
        $this->mail_subject = $template->subject;
        $this->mail_content = $template->content;
        $this->attachment = $template->attachment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        $mail = $this
            ->from(config('mail.from'))
            ->subject($this->mail_subject)
            ->view('mails.mailtemplate');

        if ($this->attachment) {
            $mail->attach($this->attachment['file'], $this->attachment['options']);
        }

        return $mail;

    }
}
