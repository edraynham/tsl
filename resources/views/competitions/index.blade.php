@extends('layouts.app')

@section('title', 'Competitions — '.config('app.name'))

@section('content')
    @php
        $disciplineFilter = is_array($disciplineFilter ?? null) ? $disciplineFilter : [];
    @endphp
    <div class="relative overflow-hidden bg-cream">
        {{-- Ambient shapes --}}
        <div class="pointer-events-none absolute -left-32 top-20 h-96 w-96 rounded-full bg-forest/[0.04] blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -right-24 bottom-40 h-80 w-80 rounded-full bg-amber-200/25 blur-3xl" aria-hidden="true"></div>

        <div class="relative mx-auto max-w-6xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
            <header class="mx-auto max-w-3xl text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Calendar</p>
                <h1 class="mt-3 font-serif text-4xl font-semibold tracking-tight text-forest sm:text-5xl">
                    Competitions <span class="text-stone-400">&</span> opens
                </h1>
                <p class="mt-4 text-lg leading-relaxed text-stone-600">
                    Fixtures from grounds across the UK — sporting, skeet, and charity days. Follow through to each ground for entries and full details.
                </p>
                @if ($competitions->isNotEmpty())
                    <p class="mt-6 inline-flex items-center gap-2 rounded-full border border-stone-200/90 bg-white/80 px-4 py-2 text-sm text-stone-600 shadow-sm backdrop-blur-sm">
                        <span class="font-semibold tabular-nums text-forest">{{ $competitions->count() }}</span>
                        <span>{{ \Illuminate\Support\Str::plural('event', $competitions->count()) }} listed</span>
                    </p>
                @endif
            </header>

            <div class="mt-10 mb-10" data-competitions-geo>
                <form method="get" action="{{ route('competitions.index') }}" class="mx-auto flex max-w-3xl flex-col gap-3 rounded-2xl border border-stone-200/90 bg-white/90 p-4 shadow-sm backdrop-blur-sm sm:p-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:gap-3">
                        <div class="min-w-0 flex-1 text-left">
                            <label for="competitions-near" class="text-xs font-semibold uppercase tracking-wider text-stone-500">Postcode or place</label>
                            <input
                                type="search"
                                name="near"
                                id="competitions-near"
                                value="{{ $near }}"
                                placeholder="e.g. GL54, Manchester"
                                autocomplete="street-address"
                                class="mt-1.5 w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-800 shadow-inner placeholder:text-stone-400 focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                            >
                        </div>
                        <div class="text-left sm:w-52 sm:shrink-0">
                            <label for="competitions-cpsa" class="text-xs font-semibold uppercase tracking-wider text-stone-500">CPSA</label>
                            <select
                                name="cpsa"
                                id="competitions-cpsa"
                                class="mt-1.5 w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-800 shadow-inner focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                            >
                                <option value="" @selected($cpsaFilter === null)>All events</option>
                                <option value="1" @selected($cpsaFilter === '1')>CPSA registered only</option>
                                <option value="0" @selected($cpsaFilter === '0')>Not CPSA registered</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-left">
                        <p class="text-xs font-semibold uppercase tracking-wider text-stone-500">Discipline</p>
                        <div class="mt-1.5 max-h-40 overflow-y-auto rounded-xl border border-stone-200 bg-white/90 p-3 shadow-inner sm:max-h-36">
                            <div class="grid gap-2 sm:grid-cols-2">
                                @foreach ($allDisciplines as $d)
                                    <label class="flex cursor-pointer items-start gap-2.5 text-sm text-stone-700">
                                        <input
                                            type="checkbox"
                                            name="discipline[]"
                                            value="{{ $d->id }}"
                                            class="mt-0.5 rounded border-stone-300 text-forest focus:ring-forest"
                                            @checked(in_array($d->id, $disciplineFilter, true))
                                        >
                                        <span><span class="font-medium text-forest">{{ $d->code }}</span> — {{ $d->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <p class="mt-2 text-xs leading-relaxed text-stone-500">Shows events tagged with any of the disciplines selected.</p>
                    </div>
                    @if (request('user_lat'))
                        <input type="hidden" name="user_lat" value="{{ request('user_lat') }}">
                    @endif
                    @if (request('user_lng'))
                        <input type="hidden" name="user_lng" value="{{ request('user_lng') }}">
                    @endif
                    <div class="flex flex-wrap gap-2 sm:shrink-0">
                        <button type="submit" class="inline-flex min-h-11 flex-1 items-center justify-center rounded-xl bg-forest px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-light sm:flex-none">
                            Search
                        </button>
                        <button
                            type="button"
                            id="competitions-near-me"
                            class="inline-flex min-h-11 flex-1 items-center justify-center rounded-xl border border-stone-300 bg-cream-dark/50 px-5 py-3 text-sm font-semibold text-forest shadow-sm transition hover:border-stone-400 hover:bg-white sm:flex-none"
                        >
                            Near me
                        </button>
                    </div>
                </form>
                @if ($sortByDistance || $near !== '' || request('user_lat') || ($cpsaFilter ?? null) !== null || $disciplineFilter !== [])
                    <p class="mx-auto mt-3 max-w-3xl text-center text-sm text-stone-600">
                        @if ($sortByDistance)
                            <span class="text-forest">Nearest events first</span>
                            @if ($hasUserCoords ?? false)
                                <span class="text-stone-500"> · using your location</span>
                            @elseif ($near !== '')
                                <span class="text-stone-500"> · from “{{ $near }}”</span>
                            @endif
                            <span class="text-stone-400"> · </span>
                        @elseif ($textOnlyFilter ?? false)
                            <span>Matching “{{ $near }}”</span>
                            <span class="text-stone-400"> · </span>
                        @endif
                        @if (($cpsaFilter ?? null) === '1')
                            <span>CPSA registered only</span>
                            <span class="text-stone-400"> · </span>
                        @elseif (($cpsaFilter ?? null) === '0')
                            <span>Not CPSA registered</span>
                            <span class="text-stone-400"> · </span>
                        @endif
                        @if ($disciplineFilter !== [])
                            @php
                                $selectedDisciplineLabels = $allDisciplines->whereIn('id', $disciplineFilter)->pluck('name');
                            @endphp
                            <span>Discipline: {{ $selectedDisciplineLabels->implode(', ') }}</span>
                            <span class="text-stone-400"> · </span>
                        @endif
                        <a href="{{ route('competitions.index') }}" class="font-medium text-forest underline decoration-forest/30 underline-offset-2 hover:text-forest-light">Clear search</a>
                    </p>
                @endif
                <p class="mx-auto mt-2 max-w-3xl text-center text-xs text-stone-500">
                    Straight-line distance to each ground. “Near me” asks your browser for location.
                </p>
            </div>

            @if ($competitions->isEmpty())
                <div class="mx-auto mt-16 max-w-lg rounded-2xl border border-dashed border-stone-300 bg-white/60 px-8 py-14 text-center">
                    @if ($textOnlyFilter ?? false)
                        <p class="font-serif text-xl text-forest">No matches</p>
                        <p class="mt-2 text-sm text-stone-600">
                            Nothing listed for “{{ $near }}”. Try another postcode or place, use “Near me”, adjust filters, or clear the search.
                        </p>
                        <a
                            href="{{ route('competitions.index') }}"
                            class="mt-6 inline-flex items-center justify-center rounded-full bg-forest px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-light"
                        >
                            Clear search
                        </a>
                    @elseif (($cpsaFilter ?? null) !== null || $disciplineFilter !== [])
                        <p class="font-serif text-xl text-forest">No matches</p>
                        <p class="mt-2 text-sm text-stone-600">
                            No events match your filters. Try widening discipline selection, setting CPSA to “All events”, or clear the search.
                        </p>
                        <a
                            href="{{ route('competitions.index') }}"
                            class="mt-6 inline-flex items-center justify-center rounded-full bg-forest px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-light"
                        >
                            Clear search
                        </a>
                    @else
                        <p class="font-serif text-xl text-forest">No competitions yet</p>
                        <p class="mt-2 text-sm text-stone-600">
                            Check back soon, or browse our directory of shooting grounds.
                        </p>
                        <a
                            href="{{ route('grounds.index') }}"
                            class="mt-6 inline-flex items-center justify-center rounded-full bg-forest px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-light"
                        >
                            Clay grounds
                        </a>
                    @endif
                </div>
            @else
                @if ($featured)
                    @php
                        $g = $featured->shootingGround;
                        $img = $g->coverPhotoUrl();
                    @endphp
                    <article class="relative mt-14 overflow-hidden rounded-3xl bg-forest shadow-xl ring-1 ring-stone-900/10">
                        <div class="absolute inset-0">
                            <img src="{{ $img }}" alt="" class="size-full object-cover opacity-40">
                            <div class="absolute inset-0 bg-gradient-to-br from-forest via-forest/95 to-forest/80"></div>
                        </div>
                        <div class="relative grid gap-8 p-8 sm:p-10 lg:grid-cols-12 lg:gap-10 lg:p-12">
                            <div class="lg:col-span-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-200/90">Next up</p>
                                <time
                                    datetime="{{ $featured->starts_at->toIso8601String() }}"
                                    class="mt-4 block font-serif text-5xl font-semibold leading-none tracking-tight text-white sm:text-6xl"
                                >
                                    <span class="block text-3xl text-white/90 sm:text-4xl">{{ $featured->starts_at->format('D') }}</span>
                                    <span class="mt-1 block">{{ $featured->starts_at->format('j') }}</span>
                                    <span class="mt-2 block text-2xl font-medium text-white/85 sm:text-3xl">{{ $featured->starts_at->format('M Y') }}</span>
                                </time>
                                @if ($featured->isMultiDay())
                                    <p class="mt-4 text-sm text-white/75">
                                        Until {{ $featured->ends_at->format('j M Y') }}
                                    </p>
                                @endif
                            </div>
                            <div class="lg:col-span-8 lg:flex lg:flex-col lg:justify-center">
                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                    @if ($featured->disciplineDisplay())
                                        <span class="inline-flex w-fit rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-amber-100 ring-1 ring-white/20">
                                            {{ $featured->disciplineDisplay() }}
                                        </span>
                                    @endif
                                    @if ($featured->cpsa_registered)
                                        <span class="inline-flex w-fit rounded-full bg-amber-400/25 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-amber-100 ring-1 ring-amber-200/40">
                                            CPSA registered
                                        </span>
                                    @else
                                        <span class="inline-flex w-fit rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-white/80 ring-1 ring-white/15">
                                            Not CPSA registered
                                        </span>
                                    @endif
                                </div>
                                <h2 class="mt-4 font-serif text-2xl font-semibold leading-snug text-white sm:text-3xl">
                                    <a href="{{ route('competitions.show', $featured) }}" class="transition hover:text-white/95 hover:underline">
                                        {{ $featured->title }}
                                    </a>
                                </h2>
                                <p class="mt-2 text-base text-white/85">
                                    <a href="{{ route('grounds.show', $g) }}" class="font-medium underline decoration-white/30 underline-offset-4 transition hover:decoration-white">
                                        {{ $g->name }}
                                    </a>
                                    @if ($g->county)
                                        <span class="text-white/70"> · {{ $g->county }}</span>
                                    @endif
                                </p>
                                @if ($sortByDistance && $featured->getAttribute('distance_miles') !== null)
                                    <p class="mt-2 text-sm text-amber-100/90">{{ $featured->distance_miles }} mi</p>
                                @endif
                                @if ($featured->summary)
                                    <p class="mt-4 max-w-2xl text-sm leading-relaxed text-white/80">
                                        {{ $featured->summary }}
                                    </p>
                                @endif
                                <div class="mt-6 flex flex-wrap gap-3">
                                    <a
                                        href="{{ route('competitions.show', $featured) }}"
                                        class="inline-flex items-center justify-center rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-forest shadow-sm transition hover:bg-cream"
                                    >
                                        Event page
                                    </a>
                                    <a
                                        href="{{ route('grounds.show', $g) }}"
                                        class="inline-flex items-center justify-center rounded-full border border-white/40 bg-white/10 px-5 py-2.5 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/20"
                                    >
                                        Ground details
                                    </a>
                                    @if ($featured->external_url)
                                        <a
                                            href="{{ $featured->external_url }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center justify-center rounded-full border border-white/35 bg-white/10 px-5 py-2.5 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/20"
                                        >
                                            Official info
                                            <svg class="ml-1.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </article>
                @endif

                <div class="mt-20 space-y-20">
                    @foreach ($grouped as $ym => $monthCompetitions)
                        @php
                            $monthStart = \Carbon\Carbon::createFromFormat('!Y-m', $ym)->startOfMonth();
                            $monthLabel = $monthStart->format('F Y');
                        @endphp
                        <section aria-labelledby="month-{{ $ym }}">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                                <h2 id="month-{{ $ym }}" class="font-serif text-2xl font-semibold text-forest">
                                    <span class="leather-underline">{{ $monthLabel }}</span>
                                </h2>
                                <p class="text-sm text-stone-500">{{ $monthCompetitions->count() }} {{ \Illuminate\Support\Str::plural('event', $monthCompetitions->count()) }}</p>
                            </div>

                            <ul class="mt-8 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                                @foreach ($monthCompetitions as $c)
                                    @php
                                        $ground = $c->shootingGround;
                                        $cover = $ground->coverPhotoUrl();
                                    @endphp
                                    <li class="group relative flex flex-col overflow-hidden rounded-2xl border border-stone-200/90 bg-white shadow-sm ring-1 ring-stone-100 transition hover:-translate-y-0.5 hover:shadow-md">
                                        <div class="relative h-36 overflow-hidden">
                                            <img
                                                src="{{ $cover }}"
                                                alt=""
                                                class="size-full object-cover transition duration-500 group-hover:scale-105"
                                                loading="lazy"
                                            >
                                            <div class="absolute inset-0 bg-gradient-to-t from-stone-900/70 via-stone-900/20 to-transparent"></div>
                                            <div class="absolute bottom-3 left-3 right-3 flex items-end justify-between gap-2">
                                                <time
                                                    datetime="{{ $c->starts_at->toIso8601String() }}"
                                                    class="rounded-lg bg-white/95 px-2.5 py-1.5 text-center shadow-sm backdrop-blur-sm"
                                                >
                                                    <span class="block text-[10px] font-semibold uppercase tracking-wider text-stone-500">{{ $c->starts_at->format('D') }}</span>
                                                    <span class="block font-serif text-lg font-semibold tabular-nums text-forest">{{ $c->starts_at->format('j M') }}</span>
                                                </time>
                                                @if ($c->disciplineDisplay())
                                                    <span class="max-w-[55%] truncate rounded-full bg-forest/90 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-white">
                                                        {{ $c->disciplineDisplay() }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex flex-1 flex-col p-5">
                                            <h3 class="font-serif text-lg font-semibold leading-snug text-forest group-hover:text-forest-light">
                                                <a href="{{ route('competitions.show', $c) }}" class="after:absolute after:inset-0 after:content-['']">
                                                    <span class="relative">{{ $c->title }}</span>
                                                </a>
                                            </h3>
                                            <p class="mt-2 flex flex-wrap gap-2">
                                                @if ($c->cpsa_registered)
                                                    <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-amber-900 ring-1 ring-amber-200/80">
                                                        CPSA registered
                                                    </span>
                                                @else
                                                    <span class="inline-flex rounded-full bg-stone-100 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-stone-600 ring-1 ring-stone-200/90">
                                                        Not CPSA registered
                                                    </span>
                                                @endif
                                            </p>
                                            <p class="mt-1 text-sm text-stone-600">
                                                <a href="{{ route('grounds.show', $ground) }}" class="relative z-10 hover:underline">{{ $ground->name }}</a>
                                                @if ($ground->county)
                                                    <span class="text-stone-400">· {{ $ground->county }}</span>
                                                @endif
                                            </p>
                                            @if ($sortByDistance && $c->getAttribute('distance_miles') !== null)
                                                <p class="mt-1 text-xs font-medium tabular-nums text-stone-500">{{ $c->distance_miles }} mi</p>
                                            @endif
                                            @if ($c->summary)
                                                <p class="mt-3 line-clamp-3 flex-1 text-sm leading-relaxed text-stone-600">
                                                    {{ $c->summary }}
                                                </p>
                                            @endif
                                            <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-stone-100 pt-4">
                                                @if ($c->external_url)
                                                    <a
                                                        href="{{ $c->external_url }}"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        class="relative z-10 text-xs font-semibold uppercase tracking-wide text-forest underline decoration-forest/30 underline-offset-4 hover:decoration-forest"
                                                    >
                                                        More info
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
