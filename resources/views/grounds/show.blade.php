@extends('layouts.app')

@section('title', $ground->name.' — '.config('app.name'))

@push('head')
    <link href="https://fonts.bunny.net/css?family=newsreader:400,500,600,700|work-sans:300,400,500,600,700&display=swap" rel="stylesheet">
@endpush

@section('content')
    <div class="relative overflow-hidden bg-tsl-surface font-tsl-body text-tsl-on-surface">
        <div class="pointer-events-none absolute -left-32 top-20 h-96 w-96 rounded-full bg-tsl-primary/[0.04] blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -right-24 bottom-40 h-80 w-80 rounded-full bg-tsl-tertiary/[0.06] blur-3xl" aria-hidden="true"></div>

        <div class="relative mx-auto max-w-6xl px-4 py-10 sm:px-8 sm:py-12 xl:max-w-7xl">
            <nav class="mb-10 text-sm" aria-label="Breadcrumb">
                <a
                    href="{{ route('grounds.index', $ground->county ? ['q' => $ground->county] : []) }}"
                    class="font-semibold text-tsl-primary underline decoration-tsl-outline-variant underline-offset-4 transition hover:text-tsl-tertiary"
                >
                    ← {{ __('Shooting grounds in :county', ['county' => $ground->county ?: __('United Kingdom')]) }}
                </a>
            </nav>

            <article>
                <div class="mb-8 overflow-hidden rounded-2xl bg-tsl-surface-container ring-1 ring-tsl-outline-variant/40">
                    <div class="relative aspect-[21/9] w-full sm:aspect-[2/1]">
                        <img
                            src="{{ $ground->coverPhotoUrl() }}"
                            alt="{{ $ground->name }}"
                            class="absolute inset-0 h-full w-full object-cover"
                            fetchpriority="high"
                        >
                    </div>
                </div>

                @php
                    $hasOpeningHours = $ground->hasStructuredWeeklyHours() || filled($ground->opening_hours);
                    $showClaimCta = ($ground->owners_count ?? 0) === 0;
                    $hasSidebar = $weather !== null || $hasOpeningHours || $showClaimCta;
                @endphp

                <div class="@if ($hasSidebar) grid gap-10 lg:grid-cols-12 lg:gap-10 xl:gap-12 lg:items-start @else space-y-8 @endif">
                    @if ($hasSidebar)
                        <div class="min-w-0 space-y-6 lg:col-span-8">
                            @include('grounds.show._lead')
                        </div>

                        <aside class="lg:col-span-4 lg:row-span-2 lg:sticky lg:top-28 lg:self-start space-y-6" aria-label="{{ __('At a glance') }}">
                            @include('grounds.show._opening-hours')

                            @if ($weather !== null)
                                <div class="overflow-hidden rounded-2xl border border-tsl-outline-variant/40 bg-gradient-to-br from-tsl-surface-container-low via-tsl-surface-container-lowest to-tsl-surface-container px-5 py-5 shadow-sm ring-1 ring-tsl-outline-variant/30">
                                    <h2 class="font-tsl-headline text-lg font-semibold text-tsl-primary">Weather now</h2>
                                    <p class="mt-1 text-xs text-tsl-secondary">At this location · updates every ~15 minutes</p>
                                    <div class="mt-4 flex items-baseline gap-2">
                                        <span class="font-tsl-headline text-4xl font-semibold tabular-nums text-tsl-primary">{{ $weather['temp_c'] }}°</span>
                                        <span class="text-lg font-medium text-tsl-secondary">C</span>
                                    </div>
                                    <p class="mt-3 text-sm font-medium text-tsl-on-surface">{{ $weather['summary'] }}</p>
                                    <p class="mt-2 text-sm text-tsl-secondary">
                                        Wind {{ $weather['wind_mph'] }} mph from {{ $weather['wind_from'] }}
                                    </p>
                                    <p class="mt-4 text-[11px] leading-relaxed text-tsl-outline">
                                        <a href="https://open-meteo.com/" class="font-medium text-tsl-secondary underline decoration-tsl-outline-variant underline-offset-2 hover:text-tsl-primary" target="_blank" rel="noopener noreferrer">Weather data: Open-Meteo</a>
                                        (non-commercial use)
                                    </p>
                                </div>
                            @endif

                            @if ($showClaimCta)
                                <div class="rounded-xl border border-dashed border-tsl-outline-variant bg-tsl-surface-container-low px-5 py-5">
                                    <p class="text-sm leading-relaxed text-tsl-secondary">
                                        {{ __('Run this ground? Take over the listing to update hours, events, and details.') }}
                                    </p>
                                    <a
                                        href="{{ route('contact', ['claim' => $ground->slug]) }}"
                                        class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-tsl-primary px-4 py-3 text-center text-sm font-semibold text-white shadow-sm transition hover:bg-tsl-primary-container"
                                    >
                                        {{ __('Claim this ground') }}
                                    </a>
                                </div>
                            @endif
                        </aside>
                    @else
                        <div class="space-y-6">
                            @include('grounds.show._lead')
                        </div>
                    @endif

                    <div class="min-w-0 space-y-8 @if ($hasSidebar) lg:col-span-8 @endif">
            @if ($ground->description)
                <div>
                    <h2 class="font-tsl-headline text-lg font-semibold text-tsl-primary">About</h2>
                    <p class="mt-4 whitespace-pre-wrap text-base leading-relaxed text-tsl-secondary">{{ $ground->description }}</p>
                </div>
            @endif

            @if ($ground->latitude && $ground->longitude)
                @php
                    $mapLat = (float) $ground->latitude;
                    $mapLng = (float) $ground->longitude;
                    $mapQuery = urlencode($mapLat.','.$mapLng);
                    $embedPad = 0.06;
                    $embedBbox = implode(',', [
                        $mapLng - $embedPad,
                        $mapLat - $embedPad,
                        $mapLng + $embedPad,
                        $mapLat + $embedPad,
                    ]);
                    $embedMarker = $mapLat.','.$mapLng;
                    $osmEmbedSrc = 'https://www.openstreetmap.org/export/embed.html?bbox='.rawurlencode($embedBbox).'&layer=mapnik&marker='.rawurlencode($embedMarker);
                @endphp

                <div>
                    <h2 class="font-tsl-headline text-lg font-semibold text-tsl-primary">Map</h2>
                    <div class="mt-3 overflow-hidden rounded-2xl bg-tsl-surface-container ring-1 ring-tsl-outline-variant/40">
                        <iframe
                            class="block h-[min(50vh,420px)] min-h-[280px] w-full border-0"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Map showing {{ $ground->name }}"
                            src="{{ $osmEmbedSrc }}"
                            allowfullscreen
                        ></iframe>
                    </div>
                    <p class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-sm">
                        <a
                            href="https://www.openstreetmap.org/?mlat={{ $mapLat }}&mlon={{ $mapLng }}#map=15/{{ $mapLat }}/{{ $mapLng }}"
                            class="font-semibold text-tsl-primary underline decoration-tsl-outline-variant underline-offset-2 hover:text-tsl-tertiary"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Open in OpenStreetMap
                        </a>
                        <a
                            href="https://www.google.com/maps?q={{ $mapQuery }}"
                            class="font-semibold text-tsl-primary underline decoration-tsl-outline-variant underline-offset-2 hover:text-tsl-tertiary"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Open in Google Maps
                        </a>
                    </p>
                </div>
            @endif

            <div class="flex flex-wrap gap-2">
                @if ($ground->has_practice)
                    <span class="rounded-full bg-tsl-primary-container/15 px-3 py-1 text-xs font-semibold text-tsl-primary ring-1 ring-tsl-outline-variant/50">Practice</span>
                @endif
                @if ($ground->has_lessons)
                    <span class="rounded-full bg-tsl-surface-container-high px-3 py-1 text-xs font-semibold text-tsl-primary ring-1 ring-tsl-outline-variant/50">Lessons</span>
                @endif
                @if ($ground->has_competitions)
                    <span class="rounded-full bg-tsl-surface-container px-3 py-1 text-xs font-semibold text-tsl-tertiary ring-1 ring-tsl-outline-variant/40">Competitions</span>
                @endif
            </div>

            @if ($ground->facilities->isNotEmpty())
                <div class="mt-6">
                    <h2 class="font-tsl-headline text-lg font-semibold text-tsl-primary">Facilities</h2>
                    <ul class="mt-3 flex flex-wrap gap-2">
                        @foreach ($ground->facilities as $facility)
                            <li class="rounded-md border border-tsl-outline-variant/50 bg-tsl-surface-container-low px-2.5 py-1 text-xs font-medium text-tsl-on-surface">
                                {{ $facility->name }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="mt-10 rounded-xl border border-tsl-outline-variant/40 bg-tsl-surface-container-lowest px-5 py-5 shadow-sm ring-1 ring-tsl-outline-variant/20" aria-labelledby="ground-competitions-heading">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <h2 id="ground-competitions-heading" class="font-tsl-headline text-lg font-semibold text-tsl-primary">Competitions &amp; opens</h2>
                    <a href="{{ route('competitions.index') }}" class="text-xs font-semibold text-tsl-primary underline decoration-tsl-outline-variant underline-offset-2 hover:text-tsl-tertiary">
                        Full calendar
                    </a>
                </div>

                @if ($upcomingCompetitions->isEmpty() && $pastCompetitions->isEmpty())
                    <p class="mt-4 text-sm leading-relaxed text-tsl-secondary">
                        No events listed for this ground yet. See the <a href="{{ route('competitions.index') }}" class="font-semibold text-tsl-primary underline decoration-tsl-outline-variant underline-offset-2 hover:text-tsl-tertiary">competitions calendar</a> for fixtures elsewhere.
                    </p>
                @else
                    @if ($upcomingCompetitions->isNotEmpty())
                        <p class="mt-6 text-xs font-semibold uppercase tracking-wider text-tsl-secondary">Upcoming</p>
                        <ul class="mt-2 divide-y divide-tsl-outline-variant/30">
                            @foreach ($upcomingCompetitions as $c)
                                <li class="flex gap-3 py-3 first:pt-0">
                                    <div class="shrink-0 text-center">
                                        <time datetime="{{ $c->starts_at->toIso8601String() }}" class="inline-flex min-w-[3.25rem] flex-col rounded-lg bg-tsl-surface-container-low px-2 py-1.5 ring-1 ring-tsl-outline-variant/40">
                                            <span class="text-[10px] font-semibold uppercase tracking-wide text-tsl-secondary">{{ $c->starts_at->format('D') }}</span>
                                            <span class="font-tsl-headline text-base font-semibold tabular-nums text-tsl-primary">{{ $c->starts_at->format('j M') }}</span>
                                        </time>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('competitions.show', $c) }}" class="font-semibold text-tsl-primary underline decoration-tsl-outline-variant underline-offset-2 transition hover:text-tsl-tertiary">
                                            {{ $c->title }}
                                        </a>
                                        @if ($c->disciplineDisplay())
                                            <p class="mt-0.5 text-xs text-tsl-secondary">{{ $c->disciplineDisplay() }}</p>
                                        @endif
                                        @if ($c->summary)
                                            <p class="mt-1 line-clamp-2 text-sm text-tsl-secondary">{{ $c->summary }}</p>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($pastCompetitions->isNotEmpty())
                        <p class="mt-6 text-xs font-semibold uppercase tracking-wider text-tsl-secondary">Earlier</p>
                        <ul class="mt-2 divide-y divide-tsl-outline-variant/30">
                            @foreach ($pastCompetitions as $c)
                                <li class="flex gap-3 py-3 first:pt-0">
                                    <div class="shrink-0 text-center">
                                        <time datetime="{{ $c->starts_at->toIso8601String() }}" class="inline-flex min-w-[3.25rem] flex-col rounded-lg bg-tsl-surface-container px-2 py-1.5 ring-1 ring-tsl-outline-variant/40">
                                            <span class="text-[10px] font-semibold uppercase tracking-wide text-tsl-outline">{{ $c->starts_at->format('D') }}</span>
                                            <span class="font-tsl-headline text-base font-semibold tabular-nums text-tsl-secondary">{{ $c->starts_at->format('j M y') }}</span>
                                        </time>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('competitions.show', $c) }}" class="font-medium text-tsl-primary underline decoration-tsl-outline-variant underline-offset-2 transition hover:text-tsl-tertiary">
                                            {{ $c->title }}
                                        </a>
                                        @if ($c->disciplineDisplay())
                                            <p class="mt-0.5 text-xs text-tsl-secondary">{{ $c->disciplineDisplay() }}</p>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                @endif
            </section>

            <dl class="mt-10 space-y-6 border-t border-tsl-outline-variant/40 pt-8">
                @if ($ground->practice_notes || $ground->has_practice)
                    <div>
                        <dt class="font-tsl-headline text-sm font-semibold text-tsl-primary">Practice</dt>
                        <dd class="mt-1 text-tsl-secondary">{{ $ground->practice_notes ?: '—' }}</dd>
                    </div>
                @endif
                @if ($ground->lesson_notes || $ground->has_lessons)
                    <div>
                        <dt class="font-tsl-headline text-sm font-semibold text-tsl-primary">Lessons</dt>
                        <dd class="mt-1 text-tsl-secondary">{{ $ground->lesson_notes ?: '—' }}</dd>
                    </div>
                @endif
                @if ($ground->competition_notes || $ground->has_competitions)
                    <div>
                        <dt class="font-tsl-headline text-sm font-semibold text-tsl-primary">Competitions</dt>
                        <dd class="mt-1 text-tsl-secondary">{{ $ground->competition_notes ?: '—' }}</dd>
                    </div>
                @endif
            </dl>

            @if ($ground->website || $ground->facebook_url || $ground->instagram_url)
                <nav class="mt-10 border-t border-tsl-outline-variant/40 pt-8" aria-label="Website and social media">
                    <ul class="flex flex-wrap items-center gap-2">
                        @if ($ground->website)
                            <li>
                                <a
                                    href="{{ $ground->website }}"
                                    class="inline-flex size-11 items-center justify-center rounded-full text-tsl-secondary transition hover:bg-tsl-surface-container-low hover:text-tsl-primary"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    aria-label="Website"
                                >
                                    <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />
                                    </svg>
                                </a>
                            </li>
                        @endif
                        @if ($ground->facebook_url)
                            <li>
                                <a
                                    href="{{ $ground->facebook_url }}"
                                    class="inline-flex size-11 items-center justify-center rounded-full text-tsl-secondary transition hover:bg-tsl-surface-container-low hover:text-tsl-primary"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    aria-label="Facebook"
                                >
                                    <svg class="size-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                    </svg>
                                </a>
                            </li>
                        @endif
                        @if ($ground->instagram_url)
                            <li>
                                <a
                                    href="{{ $ground->instagram_url }}"
                                    class="inline-flex size-11 items-center justify-center rounded-full text-tsl-secondary transition hover:bg-tsl-surface-container-low hover:text-tsl-primary"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    aria-label="Instagram"
                                >
                                    <svg class="size-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z" />
                                    </svg>
                                </a>
                            </li>
                        @endif
                    </ul>
                </nav>
            @endif

            <div class="mt-10 flex flex-wrap gap-3 border-t border-tsl-outline-variant/40 pt-8">
                <a
                    href="{{ route('grounds.index') }}"
                    class="inline-flex items-center justify-center rounded-full bg-tsl-primary px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-tsl-primary-container"
                >
                    Browse directory
                </a>
                <a
                    href="{{ route('competitions.index') }}"
                    class="inline-flex items-center justify-center rounded-full border border-tsl-outline-variant bg-tsl-surface-container-lowest px-6 py-3 text-sm font-semibold text-tsl-primary shadow-sm transition hover:border-tsl-outline hover:bg-tsl-surface-container-low"
                >
                    Competitions calendar
                </a>
            </div>
                    </div>
                </div>
        </article>
        </div>
    </div>
@endsection
