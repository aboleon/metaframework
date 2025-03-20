<?php

namespace App\Notifications;

use App\Mail\PasswordForgotten;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class ResetPasswordNotification extends Notification
{

    /**
     * Get the notification's channels.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable)
    {
        $resetUrl = $this->resetUrl($notifiable);

        return (new PasswordForgotten($notifiable->getEmailForPasswordReset(),$resetUrl));
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function resetUrl($notifiable)
    {
        return url(route('password.reset', [
            'token' => app('auth.password.broker')->createToken($notifiable),
            'email' => $notifiable->getEmailForPasswordReset()
        ], false));
    }

}
