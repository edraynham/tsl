<?php

namespace App\Mail;

use App\Models\Instructor;
use App\Models\InstructorMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InstructorNewMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Instructor $instructor,
        public InstructorMessage $messageRecord,
        public string $accountUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New instructor enquiry — '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.instructor-new-message',
        );
    }
}
