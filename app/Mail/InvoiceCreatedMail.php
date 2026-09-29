<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $transaction;
    public $toEmail;

    /**
     * Create a new message instance.
     */
    public function __construct($transaction, $toEmail)
    {
        $this->transaction = $transaction;
        $this->toEmail = $toEmail;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice Baru: ' . $this->transaction->invoice_id,
            to: [$this->toEmail]
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice_created',
            with: [
                'transaction' => $this->transaction,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
