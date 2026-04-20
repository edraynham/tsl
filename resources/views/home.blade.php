@extends('layouts.app')

@section('title', config('app.name').' — UK clay shooting grounds')

@php
    $heroImage = 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=2400&q=80';
    $heritageImage = 'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?auto=format&fit=crop&w=800&q=80';
@endphp

@section('content')
    {{-- Hero: shorter min-height + svh on small screens; search is a rounded card on mobile, pill on sm+ --}}
    <section class="relative flex min-h-[min(72svh,560px)] flex-col justify-end sm:min-h-[min(82vh,720px)]">
        <div class="absolute inset-0">
            <img
                src="{{ $heroImage }}"
                alt=""
                class="size-full object-cover object-[center_35%] sm:object-center"
                fetchpriority="high"
            >
            <div class="absolute inset-0 bg-gradient-to-t from-stone-900/88 via-stone-900/45 to-stone-900/25 sm:from-stone-900/85 sm:via-stone-900/35 sm:to-stone-900/20"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-7xl px-4 pb-4 pt-[max(5.5rem,env(safe-area-inset-top,0px))] text-center sm:px-6 sm:pb-8 sm:pt-32 lg:px-8">
            <p class="text-[0.6875rem] font-semibold uppercase tracking-[0.18em] text-white/90 sm:text-xs sm:tracking-[0.2em]">The modern estate</p>
            <h1 class="mx-auto mt-3 max-w-4xl text-balance font-serif text-[1.6875rem] font-semibold leading-[1.18] tracking-tight text-white sm:mt-4 sm:text-5xl sm:leading-tight md:text-6xl">
                Discover the finest shooting <span class="italic text-white/95">in</span> the UK
            </h1>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-4xl px-4 pb-[max(1.5rem,env(safe-area-inset-bottom,0px))] pt-2 sm:px-6 sm:pb-12 sm:pt-0 lg:px-8">
            <form
                method="get"
                action="{{ route('grounds.index') }}"
                class="flex flex-col gap-2 rounded-2xl border border-white/25 bg-white p-2 shadow-xl sm:flex-row sm:items-stretch sm:gap-0 sm:rounded-full sm:border-white/20 sm:p-2"
                role="search"
                aria-label="Search directory"
                data-home-search-autocomplete
                data-suggestions-url="{{ route('grounds.suggest') }}"
            >
                <div
                    class="relative z-20 flex min-h-[3rem] flex-1 items-center gap-3 rounded-xl px-3 py-2.5 sm:min-h-[52px] sm:rounded-full sm:px-4 sm:py-0"
                    data-home-search-field
                >
                    <svg class="pointer-events-none size-[1.125rem] shrink-0 text-forest sm:size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    </svg>
                    <input
                        type="search"
                        name="q"
                        id="home-hero-search-q"
                        placeholder="Town, county, or ground"
                        autocomplete="off"
                        enterkeyhint="search"
                        class="min-w-0 flex-1 border-0 bg-transparent text-base text-stone-800 placeholder:text-stone-400 focus:outline-none focus:ring-0 sm:text-sm"
                    >
                </div>
                <button
                    type="submit"
                    class="inline-flex min-h-[3rem] w-full shrink-0 items-center justify-center rounded-xl bg-forest px-5 text-sm font-semibold text-white transition active:bg-forest-light/90 sm:min-h-[52px] sm:w-auto sm:rounded-full sm:px-10"
                >
                    Search directory
                </button>
            </form>
        </div>
    </section>

    {{-- Interactive clay shoot --}}
    <section class="border-b border-stone-200/80 bg-cream px-4 py-14 sm:px-6 lg:px-8" aria-labelledby="clay-game-heading">
        <div class="mx-auto max-w-5xl">
            <div class="text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-stone-500">Try it</p>
                <h2 id="clay-game-heading" class="mt-2 font-serif text-2xl font-semibold text-forest sm:text-3xl">Clay shooting range</h2>
                <p class="mx-auto mt-2 max-w-lg text-sm text-stone-600">
                    Twenty-five clays with varied lines: crossers, loopers, teal, and rabbit targets. How many can you hit?
                </p>
            </div>

            <div
                class="relative mt-8 overflow-hidden rounded-2xl bg-stone-900 shadow-sm ring-1 ring-stone-200/80"
                data-clay-game
            >
                <div
                    id="clay-game-intro"
                    class="absolute inset-0 z-20 flex flex-col items-center justify-center gap-4 bg-stone-950/75 px-6 py-10 text-center backdrop-blur-[2px]"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="clay-game-intro-title"
                    aria-describedby="clay-game-intro-desc"
                >
                    <p id="clay-game-intro-title" class="font-serif text-xl font-semibold text-white sm:text-2xl">
                        @auth
                            @if (trim((string) auth()->user()->first_name) !== '')
                                Fancy 25 Sporting, {{ trim(auth()->user()->first_name) }}?
                            @else
                                25 Sporting
                            @endif
                        @else
                            25 Sporting
                        @endauth
                    </p>
                    <p id="clay-game-intro-desc" class="max-w-sm text-sm leading-relaxed text-stone-300">
                        You get <span class="font-semibold text-white">25 clays</span>, two shots each. Aim in front of each target so your shot intercepts it — left and right crossers, left and right loopers, teal, and rabbit.
                    </p>
                    <button
                        type="button"
                        id="clay-game-play"
                        class="inline-flex min-h-11 items-center justify-center rounded-full bg-white px-8 py-3 text-sm font-semibold text-forest shadow-lg transition hover:bg-cream focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-200 focus-visible:ring-offset-2 focus-visible:ring-offset-stone-950"
                    >
                        Play the game
                    </button>
                </div>
                <div
                    id="clay-game-over"
                    class="absolute inset-0 z-30 hidden flex flex-col items-center justify-center bg-stone-950/92 px-6 py-10 text-center backdrop-blur-[2px]"
                    role="dialog"
                    aria-modal="true"
                    aria-hidden="true"
                    aria-labelledby="clay-game-over-title"
                    aria-describedby="clay-game-over-desc"
                >
                    <div class="w-full max-w-md rounded-2xl border border-white/15 bg-stone-900/75 p-6 shadow-2xl">
                        <div class="mx-auto mb-3 inline-flex items-center gap-2 rounded-full border border-amber-200/35 bg-amber-100/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.12em] text-amber-100">
                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M11.983 1.904a1.25 1.25 0 0 0-3.966 0L7.45 3.703c-.194.58-.736.972-1.348.972H4.14a1.25 1.25 0 0 0-.776 2.227l1.589 1.256c.474.374.673.996.505 1.573l-.57 1.95a1.25 1.25 0 0 0 1.896 1.39L8.5 12.92c.49-.347 1.142-.347 1.632 0l1.716 1.151a1.25 1.25 0 0 0 1.896-1.39l-.57-1.95a1.25 1.25 0 0 1 .505-1.573l1.589-1.256a1.25 1.25 0 0 0-.776-2.227h-1.962a1.42 1.42 0 0 1-1.348-.972l-.567-1.799Z" />
                            </svg>
                            <span id="clay-game-rating">Round complete</span>
                        </div>
                        <p id="clay-game-over-title" class="font-serif text-2xl font-semibold text-white sm:text-3xl">
                            Game over
                        </p>
                        <p id="clay-game-over-desc" class="mx-auto mt-2 max-w-sm text-sm leading-relaxed text-stone-300">
                            You hit <span id="clay-game-final-score" class="font-semibold tabular-nums text-white">0</span> out of <span class="font-semibold text-white">25</span> clays.
                        </p>
                        <div class="mt-5 grid grid-cols-3 gap-2 text-left">
                            <div class="rounded-lg border border-white/10 bg-white/5 px-3 py-2">
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-stone-400">Hits</p>
                                <p id="clay-game-stats-hits" class="mt-1 text-base font-semibold tabular-nums text-white">0</p>
                            </div>
                            <div class="rounded-lg border border-white/10 bg-white/5 px-3 py-2">
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-stone-400">Missed</p>
                                <p id="clay-game-stats-missed" class="mt-1 text-base font-semibold tabular-nums text-white">25</p>
                            </div>
                            <div class="rounded-lg border border-white/10 bg-white/5 px-3 py-2">
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-stone-400">Hit rate</p>
                                <p id="clay-game-stats-rate" class="mt-1 text-base font-semibold tabular-nums text-emerald-300">0%</p>
                            </div>
                        </div>
                        <p id="clay-game-over-message" class="mt-4 text-sm leading-relaxed text-stone-300">
                            Keep your gun moving through the target and call early.
                        </p>
                    </div>
                    <button
                        type="button"
                        id="clay-game-play-again"
                        class="mt-5 inline-flex min-h-11 items-center justify-center rounded-full bg-forest px-8 py-3 text-sm font-semibold text-white shadow-lg transition hover:bg-forest-light focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-200 focus-visible:ring-offset-2 focus-visible:ring-offset-stone-950"
                    >
                        Play again
                    </button>
                </div>
                <div class="pointer-events-none absolute top-3 left-3 z-10 flex flex-wrap items-center gap-x-3 gap-y-1 rounded-lg bg-white/80 px-3 py-1.5 text-sm font-medium text-forest shadow-sm backdrop-blur-sm">
                    <span class="flex items-center gap-1.5">
                        <span class="text-stone-500">Score</span>
                        <span id="clay-game-score" class="font-semibold tabular-nums">0</span>
                    </span>
                    <span class="hidden h-4 w-px bg-stone-300 sm:block" aria-hidden="true"></span>
                    <span class="flex items-center gap-1.5 text-stone-600">
                        <span class="text-stone-500">Clays</span>
                        <span id="clay-game-progress" class="font-semibold tabular-nums"><span id="clay-game-released">0</span><span class="text-stone-400">/</span>25</span>
                    </span>
                    <span class="hidden h-4 w-px bg-stone-300 sm:block" aria-hidden="true"></span>
                    <span class="flex items-center gap-1.5 text-stone-600">
                        <span class="text-stone-500">Clay type</span>
                        <span id="clay-game-type" class="font-semibold">Waiting...</span>
                    </span>
                </div>
                <button
                    type="button"
                    id="clay-game-restart"
                    class="absolute top-3 right-3 z-10 rounded-lg bg-white/80 px-3 py-1.5 text-xs font-semibold text-forest shadow-sm backdrop-blur-sm transition hover:bg-white"
                >
                    Restart
                </button>
                <canvas
                    id="clay-game-canvas"
                    class="block w-full cursor-crosshair touch-manipulation"
                    role="img"
                    aria-label="Clay shooting range. Press Play to begin, then click or tap ahead of the orange clays so your shot meets them in flight."
                ></canvas>
            </div>
            <p class="mt-3 text-center text-xs text-stone-500 sm:text-sm">
                Click or tap to shoot — aim where the clay will be when the shot arrives (lead the target).
            </p>
        </div>
    </section>

    {{-- Featured shooting grounds --}}
    <section class="border-b border-stone-200/80 bg-cream px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-end">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-stone-500">{{ __('Hand picked') }}</p>
                    <h2 class="mt-2 font-serif text-3xl font-semibold text-forest md:text-4xl">{{ __('Featured Shooting Grounds') }}</h2>
                </div>
                <a href="{{ route('grounds.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-forest transition hover:text-forest-light">
                    Explore full directory
                    <span aria-hidden="true">→</span>
                </a>
            </div>

            @if ($premierGrounds->isEmpty())
                <p class="mt-10 rounded-2xl border border-dashed border-stone-300 bg-cream-dark/50 p-10 text-center text-stone-600">
                    Grounds will appear here once the directory is populated.
                    <a href="{{ route('grounds.index') }}" class="font-semibold text-forest underline underline-offset-2">Browse directory</a>
                </p>
            @else
                @php $first = $premierGrounds->first(); @endphp
                <div class="mt-10 grid gap-6 lg:grid-cols-2 lg:gap-8">
                    <a href="{{ route('grounds.show', $first) }}" class="group block overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-stone-200/80 transition hover:shadow-md">
                        <div class="aspect-[16/10] overflow-hidden">
                            <img src="{{ $first->coverPhotoUrl() }}" alt="" class="size-full object-cover transition duration-500 group-hover:scale-[1.02]" loading="lazy">
                        </div>
                        <div class="p-6">
                            <h3 class="font-serif text-xl font-semibold text-forest group-hover:underline">{{ $first->name }}</h3>
                            <p class="mt-1 text-sm text-stone-600">
                                @if ($first->city){{ $first->city }}@endif
                                @if ($first->city && $first->county), @endif
                                {{ $first->county }}
                            </p>
                            @if ($first->postcode)
                                <p class="mt-2 text-xs font-medium uppercase tracking-wide text-stone-500">{{ $first->postcode }}</p>
                            @endif
                            @if ($first->disciplines->isNotEmpty())
                                <p class="mt-4 flex flex-wrap gap-1.5">
                                    @foreach ($first->disciplines->take(8) as $disc)
                                        <span class="rounded-md bg-cream-dark px-2 py-0.5 font-mono text-[10px] font-semibold text-forest">{{ $disc->code }}</span>
                                    @endforeach
                                </p>
                            @endif
                            @if ($first->facilities->isNotEmpty())
                                <p class="mt-3 flex flex-wrap gap-1.5 text-[11px] text-stone-600">
                                    @foreach ($first->facilities->take(5) as $facility)
                                        <span class="rounded-full border border-stone-200 bg-cream px-2 py-0.5">{{ $facility->name }}</span>
                                    @endforeach
                                </p>
                            @endif
                        </div>
                    </a>

                    <div class="flex flex-col gap-6">
                        @foreach ($premierGrounds->skip(1)->values() as $idx => $ground)
                            <a href="{{ route('grounds.show', $ground) }}" class="group flex gap-4 overflow-hidden rounded-2xl bg-white p-3 shadow-sm ring-1 ring-stone-200/80 transition hover:shadow-md sm:gap-5 sm:p-4">
                                <div class="size-28 shrink-0 overflow-hidden rounded-xl sm:size-32">
                                    <img src="{{ $ground->coverPhotoUrl() }}" alt="" class="size-full object-cover transition duration-500 group-hover:scale-[1.05]" loading="lazy">
                                </div>
                                <div class="flex min-w-0 flex-1 flex-col justify-center py-1">
                                    <h3 class="font-serif text-lg font-semibold text-forest group-hover:underline">{{ $ground->name }}</h3>
                                    <p class="mt-1 text-sm text-stone-600">
                                        {{ $ground->city }}{{ $ground->city && $ground->county ? ', ' : '' }}{{ $ground->county }}
                                    </p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @foreach ($ground->disciplines->take(4) as $disc)
                                            <span class="rounded-md bg-cream-dark px-2 py-0.5 font-mono text-[10px] font-semibold text-forest">{{ $disc->code }}</span>
                                        @endforeach
                                        @foreach ($ground->facilities->take(2) as $facility)
                                            <span class="rounded-full border border-stone-200 bg-white px-2 py-0.5 text-[10px] text-stone-700">{{ $facility->name }}</span>
                                        @endforeach
                                        @if ($ground->has_practice)
                                            <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-900">Practice</span>
                                        @endif
                                        @if ($ground->has_competitions)
                                            <span class="rounded-full bg-violet-50 px-2 py-0.5 text-xs font-medium text-violet-900">Comps</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- Upcoming competitions --}}
    <section class="border-b border-stone-200/80 bg-white px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-12 lg:grid-cols-2 lg:gap-16 lg:items-start">
            <div>
                <h2 class="font-serif text-3xl font-semibold text-forest md:text-4xl">Upcoming competitions</h2>
                <p class="mt-4 max-w-md text-stone-600 leading-relaxed">
                    Registered shoots, club days, and opens from grounds across the UK — jump to an event or browse the full calendar.
                </p>
                <a href="{{ route('competitions.index') }}" class="mt-8 inline-flex rounded-lg bg-forest px-6 py-3 text-sm font-semibold text-white transition hover:bg-forest-light">
                    View competition calendar
                </a>
            </div>
            @if ($upcomingCompetitions->isEmpty())
                <div class="rounded-xl border border-dashed border-stone-300 bg-cream/50 px-6 py-10 text-center text-stone-600">
                    <p class="font-medium text-forest">No upcoming events listed yet</p>
                    <p class="mt-2 text-sm">Check back soon, or browse the calendar for the full list.</p>
                    <a href="{{ route('competitions.index') }}" class="mt-4 inline-flex text-sm font-semibold text-forest underline decoration-forest/30 underline-offset-2 hover:text-forest-light">
                        Open competitions
                    </a>
                </div>
            @else
                <ul class="space-y-4">
                    @foreach ($upcomingCompetitions as $competition)
                        @php $ground = $competition->shootingGround; @endphp
                        <li>
                            <a
                                href="{{ route('competitions.show', $competition) }}"
                                class="flex gap-4 rounded-xl border border-stone-200/90 bg-cream/50 p-4 transition hover:border-stone-300 hover:bg-cream/80"
                            >
                                <div class="flex size-14 shrink-0 flex-col items-center justify-center rounded-lg bg-forest text-center text-white">
                                    <span class="text-[10px] font-bold uppercase leading-tight">{{ $competition->starts_at->format('M') }}</span>
                                    <span class="text-lg font-semibold leading-none">{{ $competition->starts_at->format('j') }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-serif font-semibold text-forest">{{ $competition->title }}</p>
                                    <p class="mt-0.5 text-sm text-stone-600">
                                        {{ $ground->name }}
                                        @if ($ground->county)
                                            <span class="text-stone-400">·</span> {{ $ground->county }}
                                        @endif
                                    </p>
                                    <p class="mt-2 inline-flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-wide text-forest-muted">
                                        @if ($competition->cpsa_registered)
                                            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-900 ring-1 ring-amber-200/80">CPSA</span>
                                        @endif
                                        @if ($competition->disciplineDisplay())
                                            <span>{{ $competition->disciplineDisplay() }}</span>
                                        @endif
                                        <span class="inline-flex items-center gap-1 normal-case text-forest">
                                            Details
                                            <span aria-hidden="true">→</span>
                                        </span>
                                    </p>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>

    {{-- Heritage --}}
    <section class="border-b border-stone-200/80 bg-cream px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-12 lg:grid-cols-2 lg:items-center lg:gap-16">
            <div class="relative">
                <div class="aspect-[4/5] max-h-[520px] overflow-hidden rounded-2xl">
                    <img src="{{ $heritageImage }}" alt="" class="size-full object-cover">
                </div>
                <div class="absolute -bottom-4 -right-2 max-w-[min(100%,280px)] rounded-xl bg-forest px-6 py-4 text-white shadow-lg sm:bottom-6 sm:right-4">
                    <p class="font-serif text-lg font-semibold leading-snug">Serving the shooting community since 2018</p>
                </div>
            </div>
            <div>
                <h2 class="font-serif text-3xl font-semibold text-forest md:text-4xl">Heritage meets modern technology</h2>
                <ul class="mt-10 space-y-8">
                    <li class="flex gap-4">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-forest/10 text-forest">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                            </svg>
                        </span>
                        <div>
                            <p class="font-semibold text-forest">Live map directory</p>
                            <p class="mt-1 text-sm leading-relaxed text-stone-600">Find grounds by location, filter by what matters to you, and plan your next visit with confidence.</p>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-forest/10 text-forest">
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                        </span>
                        <div>
                            <p class="font-semibold text-forest">Expert verification</p>
                            <p class="mt-1 text-sm leading-relaxed text-stone-600">Listings built from trusted sources — practice, tuition, and competition info kept as accurate as possible.</p>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-forest/10 text-forest">
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" /></svg>
                        </span>
                        <div>
                            <p class="font-semibold text-forest">Seamless booking</p>
                            <p class="mt-1 text-sm leading-relaxed text-stone-600">Links to grounds and events so you can move from discovery to booking in fewer steps. More integrations coming.</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    {{-- List your ground --}}
    <section class="px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl rounded-2xl bg-forest px-6 py-14 text-center text-white sm:px-12">
            <h2 class="font-serif text-3xl font-semibold sm:text-4xl">List your ground</h2>
            <p class="mx-auto mt-4 max-w-2xl text-sm leading-relaxed text-white/85 sm:text-base">
                Reach thousands of licence holders and guests. Apply to join The Shoot List and showcase practice, tuition, and competitions.
            </p>
            <div class="mt-8 flex justify-center">
                <a href="{{ route('account') }}" class="inline-flex rounded-lg bg-white px-8 py-3 text-sm font-semibold text-forest transition hover:bg-cream">
                    Apply to join
                </a>
            </div>
        </div>
    </section>
@endsection
