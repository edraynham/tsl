<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageMail;
use App\Models\Instructor;
use App\Models\ShootingGround;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function create(Request $request): View
    {
        $claimGround = null;
        $claimSlug = $request->query('claim');
        if (is_string($claimSlug) && $claimSlug !== '') {
            $claimGround = ShootingGround::query()->where('slug', $claimSlug)->first();
        }

        return view('contact', compact('claimGround'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'subject' => [
                'nullable',
                'string',
                'max:180',
                Rule::requiredIf(fn () => ! $request->filled('instructor_slug') && ! $request->filled('ground_slug')),
            ],
            'message' => ['required', 'string', 'max:10000'],
            'instructor_slug' => ['nullable', 'string', Rule::exists('instructors', 'slug')],
            'ground_slug' => ['nullable', 'string', Rule::exists('shooting_grounds', 'slug')],
            'skill_level' => [
                'nullable',
                Rule::requiredIf(fn () => $request->filled('instructor_slug')),
                Rule::in(['beginner', 'intermediate', 'advanced']),
            ],
        ]);

        $to = config('mail.contact.address');
        if (! is_string($to) || $to === '') {
            return back()
                ->withInput()
                ->withErrors(['email' => __('Contact is not configured. Please try again later.')]);
        }

        $subjectLine = trim((string) ($validated['subject'] ?? ''));
        $bodyText = $validated['message'];
        $instructor = null;

        $skillLevelLabels = [
            'beginner' => __('Beginner'),
            'intermediate' => __('Intermediate'),
            'advanced' => __('Advanced'),
        ];

        if (! empty($validated['instructor_slug'])) {
            $instructor = Instructor::query()->where('slug', $validated['instructor_slug'])->first();
            if ($instructor !== null) {
                if ($subjectLine === '') {
                    $subjectLine = __('Enquiry');
                }
                $subjectLine = '[Instructor: '.$instructor->name.'] '.$subjectLine;
                $skill = $validated['skill_level'] ?? null;
                if ($skill !== null && isset($skillLevelLabels[$skill])) {
                    $bodyText = __('Skill level').': '.$skillLevelLabels[$skill]."\n\n".$bodyText;
                }
                $bodyText .= "\n\n---\n".__('This enquiry was sent from the instructor profile:')."\n".route('instructors.show', $instructor);
            }
        }

        if (! empty($validated['ground_slug'])) {
            $claimGround = ShootingGround::query()->where('slug', $validated['ground_slug'])->first();
            if ($claimGround !== null) {
                $claimSubject = $subjectLine === '' ? __('Listing claim') : $subjectLine;
                $subjectLine = '[Claim ground: '.$claimGround->name.'] '.$claimSubject;
                $bodyText .= "\n\n---\n".__('Ground listing:').' '.$claimGround->name."\n".route('grounds.show', $claimGround);
            }
        }

        Mail::to($to)->send(new ContactMessageMail(
            senderName: $validated['name'],
            senderEmail: $validated['email'],
            senderPhone: $validated['phone'] ?? null,
            subjectLine: $subjectLine,
            bodyText: $bodyText,
        ));

        $status = __('Thank you — your message has been sent. We’ll get back to you as soon as we can.');

        if ($instructor !== null) {
            return redirect()
                ->route('instructors.show', $instructor)
                ->with('status', $status);
        }

        return redirect()
            ->route('contact')
            ->with('status', $status);
    }
}
