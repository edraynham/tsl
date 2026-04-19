@extends('layouts.app')

@section('title', 'My account — '.config('app.name'))

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6">
        <h1 class="font-serif text-3xl font-semibold text-forest">My account</h1>
        <p class="mt-2 text-sm text-stone-600">
            Create a new account or sign in with a magic link — we don’t use passwords.
        </p>

        <div class="mt-10 grid gap-6 sm:grid-cols-2 sm:gap-8">
            <div class="flex flex-col rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
                <h2 class="font-serif text-xl font-semibold text-forest">Register</h2>
                <p class="mt-2 flex-1 text-sm leading-relaxed text-stone-600">
                    New to {{ config('app.name') }}? Add your name and email — we’ll verify your address, then you can say whether you shoot, run a ground, or both.
                </p>
                <a
                    href="{{ route('register') }}"
                    class="mt-6 inline-flex w-full items-center justify-center rounded-xl bg-forest px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-light"
                >
                    Create an account
                </a>
            </div>

            <div class="flex flex-col rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
                <h2 class="font-serif text-xl font-semibold text-forest">Sign in</h2>
                <p class="mt-2 flex-1 text-sm leading-relaxed text-stone-600">
                    Already registered? Enter your email and we’ll send you a one-time link to sign in.
                </p>
                <a
                    href="{{ route('login') }}"
                    class="mt-6 inline-flex w-full items-center justify-center rounded-xl border border-stone-200 bg-cream px-4 py-3 text-sm font-semibold text-forest shadow-sm transition hover:bg-stone-100"
                >
                    Email me a link
                </a>
            </div>
        </div>
    </div>
@endsection
