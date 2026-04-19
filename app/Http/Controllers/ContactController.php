<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function create(): View
    {
        return view('contact');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'subject' => ['required', 'string', 'max:180'],
            'message' => ['required', 'string', 'max:10000'],
        ]);

        $to = config('mail.contact.address');
        if (! is_string($to) || $to === '') {
            return back()
                ->withInput()
                ->withErrors(['email' => __('Contact is not configured. Please try again later.')]);
        }

        Mail::to($to)->send(new ContactMessageMail(
            senderName: $validated['name'],
            senderEmail: $validated['email'],
            senderPhone: $validated['phone'] ?? null,
            subjectLine: $validated['subject'],
            bodyText: $validated['message'],
        ));

        return redirect()
            ->route('contact')
            ->with('status', __('Thank you — your message has been sent. We’ll get back to you as soon as we can.'));
    }
}
