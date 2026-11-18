<?php

namespace App\Mail;

use App\Models\CooperativeRegister;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CooperativeRegistrationReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public CooperativeRegister $cooperative) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'We received your cooperative registration',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.cooperative.registration-received',
        );
    }
}
