@extends('layouts.app')

@section('title', 'Page not found — '.config('app.name'))

@section('content')
    <div class="relative overflow-hidden border-b border-stone-200/80 bg-gradient-to-b from-cream via-cream to-stone-100/40 px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
        <div class="pointer-events-none absolute -right-20 top-10 h-64 w-64 rounded-full bg-forest/[0.06] blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -left-16 bottom-0 h-48 w-48 rounded-full bg-amber-200/30 blur-3xl" aria-hidden="true"></div>

        <div class="relative mx-auto max-w-2xl text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-stone-500">Error 404</p>

            <div class="mx-auto mt-8 flex justify-center" aria-hidden="true">
                <svg class="size-28 text-orange-400 drop-shadow-sm sm:size-32" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="60" cy="60" r="56" stroke="currentColor" stroke-width="3" opacity="0.35" />
                    <circle cx="60" cy="60" r="40" stroke="currentColor" stroke-width="3" opacity="0.55" />
                    <circle cx="60" cy="60" r="22" fill="currentColor" opacity="0.9" />
                    <circle cx="60" cy="60" r="8" fill="#f9f7f2" />
                </svg>
            </div>

            <h1 class="mt-10 font-serif text-4xl font-semibold tracking-tight text-forest sm:text-5xl">
                This page went wide right
            </h1>
            <p class="mx-auto mt-4 max-w-lg text-lg leading-relaxed text-stone-600">
                We can’t find that URL — it might still be in the air, or someone called for the wrong bird.
                Either way, nothing’s broken on the line; this link just isn’t on the peg.
            </p>

            <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
                <a
                    href="{{ route('home') }}"
                    class="inline-flex items-center justify-center rounded-full bg-forest px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-light"
                >
                    Back to home
                </a>
                <a
                    href="{{ route('grounds.index') }}"
                    class="inline-flex items-center justify-center rounded-full border border-stone-300 bg-white px-6 py-3 text-sm font-semibold text-forest shadow-sm transition hover:border-stone-400 hover:bg-stone-50"
                >
                    Clay grounds
                </a>
                <a
                    href="{{ route('competitions.index') }}"
                    class="inline-flex items-center justify-center rounded-full px-5 py-3 text-sm font-medium text-stone-600 transition hover:text-forest"
                >
                    Competitions
                </a>
            </div>

            <p class="mt-12 text-sm italic text-stone-500">
                “If at first you don’t succeed — adjust your lead and try again.”
            </p>
        </div>
    </div>
@endsection
