<?php

namespace App\Mail;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportTicketReplied extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public SupportTicket $ticket) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.support_from_address', env('SUPPORT_FROM_ADDRESS', 'support@' . parse_url(config('app.url'), PHP_URL_HOST))),
                config('app.name')
            ),
            subject: 'Risposta al tuo ticket #' . $this->ticket->id . ': ' . $this->ticket->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.support.replied',
        );
    }
}
