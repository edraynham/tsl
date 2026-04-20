@extends('layouts.app')

@section('title', 'Verify email — '.config('app.name'))

@section('content')
    <div class="mx-auto max-w-lg px-4 py-16 sm:px-6">
        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-stone-500">Step 2 of 2</p>
        <h1 class="mt-2 font-serif text-3xl font-semibold text-forest">Check your inbox</h1>
        <p class="mt-3 text-sm leading-relaxed text-stone-600">
            We sent a verification link to
            <span class="font-semibold text-stone-800">{{ auth()->user()->email }}</span>.
            Open that email and click the link to activate your account.
        </p>

        @if (session('status'))
            <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">
                Verification email sent. Please check your inbox.
            </div>
        @endif

        <div class="mt-8 rounded-2xl border border-stone-200 bg-cream-dark/50 p-6">
            <p class="text-sm font-semibold text-forest">Didn’t get the email?</p>
            <ul class="mt-3 space-y-2 text-sm text-stone-700">
                <li>Check your spam, junk, or promotions folder.</li>
                <li>Make sure your email address is correct.</li>
                <li>Resend the message if it has not arrived after a minute.</li>
            </ul>
            <form method="post" action="{{ route('verification.send') }}">
                @csrf
                <button
                    type="submit"
                    class="mt-5 inline-flex w-full items-center justify-center rounded-xl bg-forest px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-light"
                >
                    Resend verification email
                </button>
            </form>
        </div>

        <p class="mt-8 rounded-xl border border-stone-200/80 bg-white/80 px-4 py-3 text-center text-xs text-stone-600">
            You can close this page after verifying and return to continue setup.
        </p>

        <p class="mt-8 text-center text-sm text-stone-600">
            <a href="{{ route('home') }}" class="font-medium text-forest underline decoration-forest/30 underline-offset-2 hover:text-forest-light">Continue to the site</a>
            <span class="text-stone-400"> | </span>
            <form method="post" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="font-medium text-stone-600 underline decoration-stone-300 underline-offset-2 hover:text-forest">Sign out</button>
            </form>
        </p>
    </div>
@endsection
