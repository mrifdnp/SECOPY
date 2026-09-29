<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Mail\InvoiceCreatedMail;

class InvoiceCreatedNotification extends Notification
{
    use Queueable;
    protected $transaction;

    /**
     * Create a new notification instance.
     */
    public function __construct($transaction)
    {
        $this->transaction = $transaction;
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
        return (new InvoiceCreatedMail(
            $this->transaction,
            env('MAIL_TO_ADDRESS', 'secobysekenliving.adm@gmail.com')
        ));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'invoice_id' => $this->transaction->invoice_id,
            'total'      => $this->transaction->total,
        ];
    }
}
