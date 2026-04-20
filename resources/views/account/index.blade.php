@extends('layouts.app')

@section('title', 'My account — '.config('app.name'))

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 sm:py-16">
        <h1 class="font-serif text-3xl font-semibold text-forest">My account</h1>
        <p class="mt-2 text-sm text-stone-600">
            Signed in as <span class="font-medium text-stone-800">{{ auth()->user()->name }}</span>
        </p>

        @include('account._tabs', ['active' => 'profile'])

        <dl class="mt-2 space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-stone-500">Email</dt>
                <dd class="mt-1 text-sm text-stone-800">{{ auth()->user()->email }}</dd>
            </div>
            @if (auth()->user()->hasVerifiedEmail())
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-stone-500">Email status</dt>
                    <dd class="mt-1 text-sm text-emerald-800">Verified</dd>
                </div>
            @else
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-stone-500">Email status</dt>
                    <dd class="mt-1 text-sm text-stone-700">
                        Not verified yet.
                        <a href="{{ route('verification.notice') }}" class="font-semibold text-forest underline decoration-forest/30 underline-offset-2">Resend or open instructions</a>
                    </dd>
                </div>
            @endif
        </dl>

        @if (auth()->user()->hasVerifiedEmail() && auth()->user()->registration_roles_completed_at === null)
            <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
                <span class="font-semibold">One more step:</span>
                tell us how you’ll use the site.
                <a href="{{ route('register.roles') }}" class="font-semibold text-forest underline decoration-forest/30 underline-offset-2">Continue</a>
            </div>
        @endif

        <form method="post" action="{{ route('logout') }}" class="mt-6">
            @csrf
            <button
                type="submit"
                class="inline-flex w-full items-center justify-center rounded-xl bg-forest px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-light sm:w-auto"
            >
                Sign out
            </button>
        </form>
    </div>
@endsection
