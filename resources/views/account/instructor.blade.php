@extends('layouts.app')

@section('title', 'My instructor profile — '.config('app.name'))

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
        <h1 class="font-serif text-3xl font-semibold text-forest">My account</h1>
        <p class="mt-2 text-sm text-stone-600">
            Signed in as <span class="font-medium text-stone-800">{{ auth()->user()->name }}</span>
        </p>

        @include('account._tabs', ['active' => 'instructor'])

        @if ($instructor === null)
            <div class="rounded-2xl border border-dashed border-stone-300 bg-white/60 px-6 py-12 text-center">
                <p class="text-stone-700">You don’t have a public instructor profile linked to this account yet.</p>
                <p class="mt-2 text-sm text-stone-500">
                    If you coach or instruct and should appear in our directory, ask an administrator to link your profile.
                </p>
            </div>
        @else
            <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-6 sm:flex-row sm:items-start">
                    @if ($instructor->photo_url)
                        <div class="mx-auto w-full max-w-[200px] shrink-0 overflow-hidden rounded-xl border border-stone-200 bg-stone-100 sm:mx-0">
                            <img src="{{ $instructor->photo_url }}" alt="" class="aspect-square w-full object-cover">
                        </div>
                    @endif
                    <div class="min-w-0 flex-1 text-left">
                        <h2 class="font-serif text-xl font-semibold text-forest">{{ $instructor->name }}</h2>
                        @if ($instructor->headline)
                            <p class="mt-1 text-sm text-stone-600">{{ $instructor->headline }}</p>
                        @endif
                        @if ($instructor->locationLabel() !== '')
                            <p class="mt-3 text-sm text-stone-500">{{ $instructor->locationLabel() }}</p>
                        @endif
                        <p class="mt-6">
                            <a
                                href="{{ route('instructors.show', $instructor) }}"
                                class="inline-flex items-center justify-center rounded-xl border border-stone-200 bg-white px-4 py-2.5 text-sm font-semibold text-forest shadow-sm transition hover:bg-stone-50"
                            >
                                View public profile
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <form method="post" action="{{ route('logout') }}" class="mt-10">
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
