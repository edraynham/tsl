<?php

namespace Tests\Feature;

use App\Mail\InstructorNewMessageMail;
use App\Models\Instructor;
use App\Models\InstructorMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InstructorContactMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_contact_form_saves_message_and_sends_notification_email(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'coach@example.com',
            'email_verified_at' => now(),
            'registration_roles_completed_at' => now(),
        ]);

        $instructor = Instructor::query()->create([
            'user_id' => $user->id,
            'name' => 'Coach Taylor',
            'slug' => 'coach-taylor',
            'headline' => null,
            'bio' => null,
            'city' => 'Bath',
            'county' => 'Somerset',
            'photo_url' => null,
            'website' => null,
        ]);

        $response = $this->post(route('contact.store'), [
            'name' => 'Sam Visitor',
            'email' => 'sam@example.com',
            'phone' => '07123 456789',
            'subject' => 'Coaching enquiry',
            'message' => 'Hi, I would like to book a session next month.',
            'instructor_slug' => $instructor->slug,
            'skill_level' => 'intermediate',
        ]);

        $response->assertRedirect(route('instructors.show', $instructor));
        $this->assertDatabaseHas('instructor_messages', [
            'instructor_id' => $instructor->id,
            'sender_name' => 'Sam Visitor',
            'sender_email' => 'sam@example.com',
            'skill_level' => 'intermediate',
        ]);

        $saved = InstructorMessage::query()->first();
        $this->assertNotNull($saved);
        $this->assertSame('Hi, I would like to book a session next month.', $saved->message);

        Mail::assertSent(InstructorNewMessageMail::class, function (InstructorNewMessageMail $mail) use ($instructor) {
            return $mail->hasTo('coach@example.com')
                && $mail->instructor->is($instructor)
                && str_contains($mail->accountUrl, route('account.instructor', absolute: true));
        });
    }
}
