@extends('layouts.app')

@section('title', 'About — '.config('app.name'))

@php
    $heroImg = 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=2400&q=80';
    $splitImg = 'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?auto=format&fit=crop&w=1200&q=80';
    $cardA = 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=900&q=80';
    $cardB = 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=900&q=80';
    $cardC = 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=900&q=80';
@endphp

@section('content')
    {{-- Hero --}}
    <section class="relative overflow-hidden">
        <div class="absolute inset-0">
            <img src="{{ $heroImg }}" alt="" class="size-full object-cover" fetchpriority="high">
            <div class="absolute inset-0 bg-gradient-to-r from-forest/95 via-forest/80 to-forest/55"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-stone-900/50 to-transparent"></div>
        </div>
        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8">
            <nav class="mb-12 text-sm">
                <a href="{{ route('home') }}" class="font-medium text-white/90 underline decoration-white/30 underline-offset-4 transition hover:text-white">← Home</a>
            </nav>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-200/90">The Shoot List</p>
            <h1 class="mt-4 max-w-3xl font-serif text-4xl font-semibold tracking-tight text-white sm:text-5xl lg:text-6xl">
                Built for everyone who loves clays
            </h1>
            <p class="mt-6 max-w-xl text-lg leading-relaxed text-white/85">
                We’re shooters too. {{ config('app.name') }} exists to make it easier to find grounds, plan comps, and spend more time on the peg — not buried in spreadsheets and outdated posts.
            </p>
        </div>
    </section>

    {{-- Intro --}}
    <section class="border-b border-stone-200/80 bg-cream px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-stone-500">Our story</p>
            <p class="mt-4 text-lg leading-relaxed text-stone-700">
                Tracking down reliable information on shoots and competitions shouldn’t feel like a chore. We created {{ config('app.name') }} as a hand-curated home for UK clay shooting — from casual practice days to registered fixtures — so you can see what’s on, where it is, and how to get involved.
            </p>
        </div>
    </section>

    {{-- Split: image + copy --}}
    <section class="bg-white px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-12 lg:grid-cols-2 lg:items-center lg:gap-16">
            <div class="overflow-hidden rounded-3xl bg-stone-100 shadow-lg ring-1 ring-stone-200/80">
                <img
                    src="{{ $splitImg }}"
                    alt="Clay shooting at a rural ground"
                    class="aspect-[4/3] w-full object-cover sm:aspect-[5/4]"
                    loading="lazy"
                >
            </div>
            <div>
                <h2 class="font-serif text-3xl font-semibold text-forest sm:text-4xl">Curated, not crowded</h2>
                <p class="mt-4 text-base leading-relaxed text-stone-700">
                    Every ground and listing is chosen and maintained with care. We focus on clarity — addresses, disciplines, and what to expect — so whether you’re new to the sport or chasing selection points, you’re not guessing.
                </p>
                <p class="mt-4 text-base leading-relaxed text-stone-700">
                    Less noise, more shooting. That’s the idea.
                </p>
                <ul class="mt-8 space-y-3 text-stone-700">
                    <li class="flex gap-3">
                        <span class="mt-1.5 size-2 shrink-0 rounded-full bg-forest" aria-hidden="true"></span>
                        <span>UK-wide directory of shooting grounds with maps and contact details</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-1.5 size-2 shrink-0 rounded-full bg-forest" aria-hidden="true"></span>
                        <span>Competition calendar with search, distance sorting, and CPSA context where it matters</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-1.5 size-2 shrink-0 rounded-full bg-forest" aria-hidden="true"></span>
                        <span>Ground owners can keep their own pages fresh — hours, events, and fixtures</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    {{-- Three cards with photos --}}
    <section class="border-t border-stone-200/80 bg-cream px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-stone-500">What you’ll find</p>
                <h2 class="mt-3 font-serif text-3xl font-semibold text-forest sm:text-4xl">Everything in one place</h2>
                <p class="mt-4 text-stone-600">
                    Three pillars we care about — so you can plan a day out or a full season without the runaround.
                </p>
            </div>

            <div class="mt-14 grid gap-8 md:grid-cols-3">
                <article class="flex flex-col overflow-hidden rounded-2xl border border-stone-200/90 bg-white shadow-sm ring-1 ring-stone-100">
                    <div class="aspect-[16/10] overflow-hidden">
                        <img src="{{ $cardA }}" alt="" class="size-full object-cover transition duration-500 hover:scale-105" loading="lazy">
                    </div>
                    <div class="flex flex-1 flex-col p-6">
                        <h3 class="font-serif text-xl font-semibold text-forest">Grounds directory</h3>
                        <p class="mt-2 flex-1 text-sm leading-relaxed text-stone-600">
                            Search by place, filter by discipline and facilities, and see distance from your location when you need it.
                        </p>
                        <a href="{{ route('grounds.index') }}" class="mt-8 inline-flex items-center text-sm font-semibold text-forest underline decoration-forest/30 underline-offset-4 hover:text-forest-light">
                            Browse grounds →
                        </a>
                    </div>
                </article>

                <article class="flex flex-col overflow-hidden rounded-2xl border border-stone-200/90 bg-white shadow-sm ring-1 ring-stone-100">
                    <div class="aspect-[16/10] overflow-hidden">
                        <img src="{{ $cardB }}" alt="" class="size-full object-cover transition duration-500 hover:scale-105" loading="lazy">
                    </div>
                    <div class="flex flex-1 flex-col p-6">
                        <h3 class="font-serif text-xl font-semibold text-forest">Competitions &amp; opens</h3>
                        <p class="mt-2 flex-1 text-sm leading-relaxed text-stone-600">
                            A calendar of fixtures with dates, venues, and filters — including CPSA-registered events when listed.
                        </p>
                        <a href="{{ route('competitions.index') }}" class="mt-8 inline-flex items-center text-sm font-semibold text-forest underline decoration-forest/30 underline-offset-4 hover:text-forest-light">
                            View calendar →
                        </a>
                    </div>
                </article>

                <article class="flex flex-col overflow-hidden rounded-2xl border border-stone-200/90 bg-white shadow-sm ring-1 ring-stone-100">
                    <div class="aspect-[16/10] overflow-hidden">
                        <img src="{{ $cardC }}" alt="" class="size-full object-cover transition duration-500 hover:scale-105" loading="lazy">
                    </div>
                    <div class="flex flex-1 flex-col p-6">
                        <h3 class="font-serif text-xl font-semibold text-forest">Straight answers</h3>
                        <p class="mt-2 flex-1 text-sm leading-relaxed text-stone-600">
                            We’re not here to drown you in ads — we want accurate, useful listings you can trust on the way to the ground.
                        </p>
                        <a href="{{ route('home') }}" class="mt-8 inline-flex items-center text-sm font-semibold text-forest underline decoration-forest/30 underline-offset-4 hover:text-forest-light">
                            Back to home →
                        </a>
                    </div>
                </article>
            </div>
        </div>
    </section>

    {{-- Pull quote --}}
    <section class="bg-forest px-4 py-14 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl text-center">
            <p class="font-serif text-2xl font-medium leading-snug text-white sm:text-3xl">
                Whether you’re chasing a personal best or a quiet Sunday round, the right information makes all the difference.
            </p>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-cream px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto flex max-w-3xl flex-col items-center justify-between gap-8 rounded-3xl border border-stone-200/90 bg-white/90 p-8 shadow-sm sm:flex-row sm:p-10">
            <div class="text-center sm:text-left">
                <h2 class="font-serif text-2xl font-semibold text-forest sm:text-3xl">Ready to explore?</h2>
                <p class="mt-2 text-stone-600">Jump into the directory or the competition calendar — whichever brings you closer to your next round.</p>
            </div>
            <div class="flex shrink-0 flex-col gap-3 sm:items-end">
                <a
                    href="{{ route('grounds.index') }}"
                    class="inline-flex min-w-[12rem] items-center justify-center rounded-xl bg-forest px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-light"
                >
                    Clay grounds
                </a>
                <a
                    href="{{ route('competitions.index') }}"
                    class="inline-flex min-w-[12rem] items-center justify-center rounded-xl border border-stone-300 bg-cream-dark/50 px-6 py-3 text-sm font-semibold text-forest transition hover:border-stone-400 hover:bg-white"
                >
                    Competitions
                </a>
            </div>
        </div>
    </section>
@endsection
