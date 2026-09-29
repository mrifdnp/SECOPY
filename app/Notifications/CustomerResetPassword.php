<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Mail\ResetPasswordMail;

class CustomerResetPassword extends Notification
{
    use Queueable;

    protected string $url;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $token, string $email)
    {
        $this->url = url(route('customer.password.reset', [
            'token' => $token,
            'email' => $email,
        ], false));
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        return (new ResetPasswordMail($this->url, $notifiable->email));
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
