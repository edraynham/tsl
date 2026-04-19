@extends('layouts.app')

@section('title', 'Create account — '.config('app.name'))

@section('content')
    <div class="mx-auto max-w-md px-4 py-16 sm:px-6">
        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-stone-500">Step 1 of 2</p>
        <h1 class="mt-2 font-serif text-3xl font-semibold text-forest">Create an account</h1>
        <p class="mt-2 text-sm text-stone-600">
            Enter your name and email. We’ll send a link to verify your address — then you can tell us whether you’re a shooter, a ground owner / organiser, or both. Sign-in is by magic link; no password.
        </p>

        <form method="post" action="{{ route('register') }}" class="mt-8 space-y-5">
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-1">
                    <label for="first_name" class="block text-sm font-medium text-stone-700">First name</label>
                    <input
                        type="text"
                        name="first_name"
                        id="first_name"
                        value="{{ old('first_name') }}"
                        required
                        autocomplete="given-name"
                        class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                    >
                    @error('first_name')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sm:col-span-1">
                    <label for="last_name" class="block text-sm font-medium text-stone-700">Last name</label>
                    <input
                        type="text"
                        name="last_name"
                        id="last_name"
                        value="{{ old('last_name') }}"
                        required
                        autocomplete="family-name"
                        class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                    >
                    @error('last_name')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-stone-700">Email</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="email"
                    class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                >
                @error('email')
                    <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="w-full rounded-xl bg-forest py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-light"
            >
                Continue
            </button>
        </form>

        <p class="mt-8 text-center text-sm text-stone-600">
            Already have an account?
            <a href="{{ route('login') }}" class="font-semibold text-forest underline decoration-forest/30 underline-offset-2 hover:text-forest-light">Sign in</a>
            (magic link)
        </p>
    </div>
@endsection
