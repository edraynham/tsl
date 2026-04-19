@extends('layouts.app')

@section('title', 'Verify email — '.config('app.name'))

@section('content')
    <div class="mx-auto max-w-md px-4 py-16 sm:px-6">
        <h1 class="font-serif text-3xl font-semibold text-forest">Verify your email</h1>
        <p class="mt-3 text-sm leading-relaxed text-stone-600">
            Thanks for registering. We’ve sent a verification link to <span class="font-medium text-stone-800">{{ auth()->user()->email }}</span>. Click the link to confirm your address — then we’ll ask whether you’re a shooter, a ground owner / organiser, or both.
        </p>

        @if (session('status'))
            <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">
                {{ session('status') }}
            </div>
        @endif

        <div class="mt-8 space-y-4 rounded-xl border border-stone-200 bg-cream-dark/40 px-5 py-4 text-sm text-stone-700">
            <p class="font-medium text-forest">Didn’t get the email?</p>
            <p>Check spam or promotions, then resend the verification message.</p>
            <form method="post" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="mt-2 text-sm font-semibold text-forest underline decoration-forest/30 underline-offset-2 hover:text-forest-light">
                    Resend verification email
                </button>
            </form>
        </div>

        <p class="mt-10 text-center text-sm text-stone-600">
            <a href="{{ route('home') }}" class="font-medium text-forest hover:text-forest-light">Continue to the site</a>
            <span class="text-stone-400"> · </span>
            <form method="post" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="font-medium text-stone-600 underline decoration-stone-300 underline-offset-2 hover:text-forest">Sign out</button>
            </form>
        </p>
    </div>
@endsection
