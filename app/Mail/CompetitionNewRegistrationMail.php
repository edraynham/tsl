<?php

namespace App\Mail;

use App\Models\Competition;
use App\Models\CompetitionRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class CompetitionNewRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, CompetitionRegistration>|array<int, CompetitionRegistration>  $registrations
     */
    public function __construct(
        public Competition $competition,
        public Collection|array $registrations,
    ) {
        $this->registrations = $registrations instanceof Collection
            ? $registrations
            : collect($registrations);
    }

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
