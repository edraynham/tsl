<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\MagicLoginLinkMail;
use App\Models\LoginToken;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MagicLoginController extends Controller
{
    public function create(Request $request): RedirectResponse|View
    {
        if ($user = $request->user()) {
            if ($user->hasVerifiedEmail() && $user->registration_roles_completed_at === null) {
                return redirect()->route('register.roles');
            }

            return redirect()->route('home');
        }

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $email = $validated['email'];

        $throttleKey = 'magic-login:'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return back()
                ->withInput()
                ->withErrors(['email' => __('Too many attempts. Please try again in a minute.')]);
        }

        RateLimiter::hit($throttleKey, 60);

        $plain = LoginToken::createForEmail($email);
        $url = route('login.verify', ['token' => $plain], absolute: true);

        Mail::to($email)->send(new MagicLoginLinkMail($url));

        return back()->with('status', __('Check your email for a sign-in link.'));
    }

    public function verify(Request $request, string $token): RedirectResponse
    {
        LoginToken::query()->where('expires_at', '<', now())->delete();

        $row = LoginToken::findValidPlainToken($token);
        if ($row === null) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => __('This sign-in link is invalid or has expired.')]);
        }

        $local = Str::before($row->email, '@');
        $derivedFirst = Str::title(str_replace(['.', '_'], ' ', $local));

        $user = User::query()->firstOrCreate(
            ['email' => $row->email],
            [
                'first_name' => $derivedFirst,
                'last_name' => '',
                'password' => null,
                'is_shooter' => false,
                'is_organiser' => false,
                'registration_roles_completed_at' => null,
            ],
        );

        $row->delete();

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        $redirect = $user->isGroundOwner()
            ? route('owner.dashboard', absolute: false)
            : route('home', absolute: false);

        return redirect()->intended($redirect);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
