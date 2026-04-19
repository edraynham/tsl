@extends('layouts.app')

@section('title', 'Sign in — '.config('app.name'))

@section('content')
    <div class="mx-auto max-w-md px-4 py-16 sm:px-6">
        <h1 class="font-serif text-3xl font-semibold text-forest">Sign in</h1>
        <p class="mt-2 text-sm text-stone-600">
            Enter your email and we’ll send you a one-time link — no password.
            New here?
            <a href="{{ route('register') }}" class="font-semibold text-forest underline decoration-forest/30 underline-offset-2 hover:text-forest-light">Create an account</a>.
        </p>

        @if (session('status'))
            <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">
                {{ session('status') }}
            </div>
        @endif

        <form method="post" action="{{ route('login.store') }}" class="mt-8 space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-stone-700">Email</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    value="{{ old('email', request('email')) }}"
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
                Email me a link
            </button>
        </form>

    </div>
@endsection
