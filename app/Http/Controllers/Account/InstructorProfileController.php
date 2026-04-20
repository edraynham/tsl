<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InstructorProfileController extends Controller
{
    public function show(Request $request): View
    {
        $instructor = $request->user()->instructorProfile;
        if ($instructor !== null) {
            $instructor->load(['messages' => fn ($q) => $q->latest()]);
        }

        return view('account.instructor', compact('instructor'));
    }

    public function edit(Request $request): View
    {
        $instructor = $request->user()->instructorProfile;
        abort_if($instructor === null, 404);
        $this->authorize('update', $instructor);

        return view('account.instructor-edit', compact('instructor'));
    }

    public function update(Request $request): RedirectResponse
    {
        $instructor = $request->user()->instructorProfile;
        abort_if($instructor === null, 404);
        $this->authorize('update', $instructor);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('instructors', 'slug')->ignore($instructor->id),
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            ],
            'headline' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:50000'],
            'city' => ['nullable', 'string', 'max:255'],
            'county' => ['nullable', 'string', 'max:255'],
            'photo_url' => ['nullable', 'string', 'max:2048'],
            'website' => ['nullable', 'string', 'max:2048'],
        ]);

        $instructor->update($validated);

        return redirect()
            ->route('account.instructor')
            ->with('status', __('Profile saved.'));
    }
}
