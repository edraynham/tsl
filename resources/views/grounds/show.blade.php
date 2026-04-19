@extends('layouts.app')

@section('title', $ground->name.' — '.config('app.name'))

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
        <nav class="mb-8 text-sm">
            <a href="{{ route('grounds.index') }}" class="font-medium text-forest hover:text-forest-light">← All grounds</a>
        </nav>

        <article>
            <div class="mb-8 overflow-hidden rounded-2xl bg-stone-100 ring-1 ring-stone-200/80">
                <div class="aspect-[21/9] max-h-[320px] sm:aspect-[2/1]">
                    <img
                        src="{{ $ground->coverPhotoUrl() }}"
                        alt="{{ $ground->name }}"
                        class="size-full object-cover"
                        fetchpriority="high"
                    >
                </div>
            </div>

            <h1 class="font-serif text-3xl font-semibold tracking-tight text-forest">{{ $ground->name }}</h1>

            @if ($ground->full_address || $ground->postcode)
                <p class="mt-4 text-stone-700">
                    @if ($ground->full_address)
                        {{ $ground->full_address }}
                    @endif
                    @if ($ground->postcode && $ground->full_address && ! str_contains($ground->full_address, $ground->postcode))
                        <br><span class="text-stone-500">{{ $ground->postcode }}</span>
                    @elseif ($ground->postcode)
                        {{ $ground->postcode }}
                    @endif
                </p>
            @endif

            @if ($ground->openingHours->isNotEmpty() || $ground->opening_hours)
                <div class="mt-6 rounded-xl border border-stone-200/90 bg-cream-dark/40 px-5 py-4">
                    <h2 class="text-sm font-semibold text-forest">Opening hours</h2>
                    @if ($ground->openingHours->isNotEmpty())
                        <dl class="mt-3 space-y-2 text-sm text-stone-700">
                            @foreach ($ground->openingHours->groupBy('weekday') as $weekday => $slots)
                                <div class="flex flex-col gap-0.5 sm:flex-row sm:gap-3">
                                    <dt class="shrink-0 font-medium text-stone-600 sm:w-28">
                                        {{ \App\Models\OpeningHour::WEEKDAY_LABELS[$weekday] ?? 'Day '.$weekday }}
                                    </dt>
                                    <dd class="leading-relaxed">
                                        @foreach ($slots as $slot)
                                            @if ($slot->opens_at && $slot->closes_at)
                                                <span>{{ $slot->opens_at->format('g:ia') }}–{{ $slot->closes_at->format('g:ia') }}</span>
                                            @elseif (! $slot->opens_at && ! $slot->closes_at)
                                                <span class="text-stone-500">Closed</span>
                                            @else
                                                <span>{{ $slot->opens_at?->format('g:ia') }}{{ $slot->closes_at ? '–'.$slot->closes_at->format('g:ia') : '' }}</span>
                                            @endif
                                            @if (! $loop->last)
                                                <span class="text-stone-400"> · </span>
                                            @endif
                                        @endforeach
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    @else
                        <div class="mt-2 whitespace-pre-line text-sm leading-relaxed text-stone-700">{{ $ground->opening_hours }}</div>
                    @endif
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

                @if ($weather ?? null)
                    <div class="mt-6 overflow-hidden rounded-2xl border border-sky-200/80 bg-gradient-to-br from-sky-50 via-white to-cream-dark/40 px-5 py-5 shadow-sm ring-1 ring-sky-100/80">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-sm font-semibold text-forest">Weather now</h2>
                                <p class="mt-1 text-xs text-stone-500">At this location · updates every ~15 minutes</p>
                            </div>
                            <div class="flex flex-wrap items-baseline gap-2 sm:justify-end">
                                <span class="font-serif text-4xl font-semibold tabular-nums text-forest">{{ $weather['temp_c'] }}°</span>
                                <span class="text-lg font-medium text-stone-500">C</span>
                            </div>
                        </div>
                        <p class="mt-3 text-sm font-medium text-stone-800">{{ $weather['summary'] }}</p>
                        <p class="mt-2 text-sm text-stone-600">
                            Wind {{ $weather['wind_mph'] }} mph from {{ $weather['wind_from'] }}
                        </p>
                        <p class="mt-4 text-[11px] leading-relaxed text-stone-400">
                            <a href="https://open-meteo.com/" class="font-medium text-stone-500 underline decoration-stone-300 underline-offset-2 hover:text-forest" target="_blank" rel="noopener noreferrer">Weather data: Open-Meteo</a>
                            (non-commercial use)
                        </p>
                    </div>
                @endif

                <div class="mt-6">
                    <h2 class="sr-only">Location map</h2>
                    <div class="overflow-hidden rounded-2xl bg-stone-100 ring-1 ring-stone-200/80">
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
                            class="font-medium text-forest underline decoration-forest/30 underline-offset-2 hover:text-forest-light"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Open in OpenStreetMap
                        </a>
                        <a
                            href="https://www.google.com/maps?q={{ $mapQuery }}"
                            class="font-medium text-forest underline decoration-forest/30 underline-offset-2 hover:text-forest-light"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Open in Google Maps
                        </a>
                    </p>
                </div>
            @endif

            <div class="mt-6 flex flex-wrap gap-2">
                @if ($ground->has_practice)
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-900">Practice</span>
                @endif
                @if ($ground->has_lessons)
                    <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-medium text-sky-900">Lessons</span>
                @endif
                @if ($ground->has_competitions)
                    <span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-medium text-violet-900">Competitions</span>
                @endif
            </div>

            @if ($ground->disciplines->isNotEmpty())
                <div class="mt-6">
                    <h2 class="text-sm font-semibold text-forest">Disciplines</h2>
                    <ul class="mt-3 flex flex-wrap gap-2">
                        @foreach ($ground->disciplines as $disc)
                            <li class="rounded-full border border-stone-200 bg-white px-3 py-1.5 text-xs font-medium text-stone-800 shadow-sm">
                                <span class="font-mono font-semibold text-forest">{{ $disc->code }}</span>
                                <span class="text-stone-500"> · </span>{{ $disc->name }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($ground->facilities->isNotEmpty())
                <div class="mt-6">
                    <h2 class="text-sm font-semibold text-forest">Facilities</h2>
                    <ul class="mt-3 flex flex-wrap gap-2">
                        @foreach ($ground->facilities as $facility)
                            <li class="rounded-full bg-stone-100 px-3 py-1.5 text-xs font-medium text-stone-800 ring-1 ring-stone-200/90">
                                {{ $facility->name }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($ground->description)
                <div class="prose prose-stone mt-8 max-w-none">
                    <p class="whitespace-pre-wrap text-stone-700 leading-relaxed">{{ $ground->description }}</p>
                </div>
            @endif

            <dl class="mt-10 space-y-6 border-t border-stone-200 pt-8">
                @if ($ground->practice_notes || $ground->has_practice)
                    <div>
                        <dt class="text-sm font-semibold text-forest">Practice</dt>
                        <dd class="mt-1 text-stone-600">{{ $ground->practice_notes ?: '—' }}</dd>
                    </div>
                @endif
                @if ($ground->lesson_notes || $ground->has_lessons)
                    <div>
                        <dt class="text-sm font-semibold text-forest">Lessons</dt>
                        <dd class="mt-1 text-stone-600">{{ $ground->lesson_notes ?: '—' }}</dd>
                    </div>
                @endif
                @if ($ground->competition_notes || $ground->has_competitions)
                    <div>
                        <dt class="text-sm font-semibold text-forest">Competitions</dt>
                        <dd class="mt-1 text-stone-600">{{ $ground->competition_notes ?: '—' }}</dd>
                    </div>
                @endif
            </dl>

            @if ($ground->events_urls && count($ground->events_urls) > 0)
                <div class="mt-10">
                    <h2 class="text-sm font-semibold text-forest">Events &amp; fixtures</h2>
                    <ul class="mt-3 list-inside list-disc space-y-2 text-sm">
                        @foreach ($ground->events_urls as $url)
                            <li>
                                <a href="{{ $url }}" class="text-forest underline decoration-forest/30 underline-offset-2 hover:text-forest-light" target="_blank" rel="noopener noreferrer">{{ $url }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($ground->website || $ground->facebook_url || $ground->instagram_url)
                <nav class="mt-10 border-t border-stone-200 pt-8" aria-label="Website and social media">
                    <ul class="flex flex-wrap items-center gap-2">
                        @if ($ground->website)
                            <li>
                                <a
                                    href="{{ $ground->website }}"
                                    class="inline-flex size-11 items-center justify-center rounded-full text-stone-500 transition hover:bg-cream-dark/80 hover:text-forest"
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
                                    class="inline-flex size-11 items-center justify-center rounded-full text-stone-500 transition hover:bg-cream-dark/80 hover:text-forest"
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
                                    class="inline-flex size-11 items-center justify-center rounded-full text-stone-500 transition hover:bg-cream-dark/80 hover:text-forest"
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
        </article>
    </div>
@endsection
