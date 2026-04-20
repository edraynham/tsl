<?php

namespace App\Mail;

use App\Models\Competition;
use App\Models\CompetitionRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompetitionNewRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Competition $competition,
        public CompetitionRegistration $registration,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New competition entry — '.$this->competition->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.competition-new-registration',
        );
    }
}
