<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(Request $request): RedirectResponse|View
    {
        if ($user = $request->user()) {
            if (! $user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }
            if ($user->registration_roles_completed_at === null) {
                return redirect()->route('register.roles');
            }

            return redirect()->route('home');
        }

        return view('auth.register');
    }

    /**
     * Step 1: name and email only — verification email is sent; roles are chosen after verification.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        if (User::query()->where('email', $validated['email'])->exists()) {
            return redirect()
                ->route('login', ['email' => $validated['email']])
                ->with('status', __('An account with this email already exists. Sign in with a magic link instead.'));
        }

        $user = User::query()->create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => null,
            'is_shooter' => false,
            'is_organiser' => false,
            'registration_roles_completed_at' => null,
        ]);

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice');
    }

    /**
     * Step 3: after email verification — choose shooter / organiser (at least one).
     */
    public function editRoles(Request $request): RedirectResponse|View
    {
        $user = $request->user();
        abort_unless($user && $user->hasVerifiedEmail(), 403);
        if ($user->registration_roles_completed_at !== null) {
            return redirect()->route('home');
        }

        return view('auth.register-roles');
    }

    public function updateRoles(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $user->hasVerifiedEmail(), 403);
        if ($user->registration_roles_completed_at !== null) {
            return redirect()->route('home');
        }

        $validated = $request->validate([
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['in:shooter,organiser'],
        ], [
            'roles.required' => 'Please select whether you are a shooter, a ground owner / organiser, or both.',
            'roles.min' => 'Please select at least one option.',
        ]);

        $roles = $validated['roles'];

        $user->forceFill([
            'is_shooter' => in_array('shooter', $roles, true),
            'is_organiser' => in_array('organiser', $roles, true),
            'registration_roles_completed_at' => now(),
        ])->save();

        return redirect()
            ->route('home')
            ->with('status', __('You’re all set — welcome to :app.', ['app' => config('app.name')]));
    }
}
