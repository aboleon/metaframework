<?php

namespace App\Mail;

use App\Interfaces\Mailer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailerMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(public Mailer $mailed)
    {
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        $sendable = $this
            ->from(['address' => config('mail.from.address'), 'name' => config('app.name')])
            ->to($this->mailed->email())
            ->subject($this->mailed->subject())
            ->view($this->mailed->view())->with('mailed', $this->mailed);

        if (method_exists($this->mailed, 'attachments')) {
            foreach ($this->mailed->attachments() as $attachment) {

                if (!is_array($attachment)) {
                    continue;
                }

                $keys = array_keys($attachment);
                if (count(array_intersect(['as', 'file', 'mime'], $keys)) != 3) {
                    continue;
                }

                if (array_key_exists('type', $attachment) && $attachment['type'] === 'binary') {
                    $sendable->attachData($attachment['file'], $attachment['as']);
                } else {
                    $sendable->attach($attachment['file'], [
                        'as' => $attachment['as'],
                        'mime' => $attachment['mime']
                    ]);
                }
            }
        }

        return $sendable;
    }
}
