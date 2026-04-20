@extends('layouts.app')

@section('title', $competition->title.' — '.config('app.name'))

@section('content')
    @php
        $cover = $ground->coverPhotoUrl();
    @endphp

    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
        <nav class="mb-8 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm">
            <a href="{{ route('competitions.index') }}" class="font-medium text-forest hover:text-forest-light">← Competitions</a>
            <span class="text-stone-300" aria-hidden="true">·</span>
            <a href="{{ route('grounds.show', $ground) }}" class="text-stone-600 hover:text-forest">{{ $ground->name }}</a>
        </nav>

        <article>
            <div class="mb-8 overflow-hidden rounded-2xl bg-stone-100 ring-1 ring-stone-200/80">
                <div class="aspect-[21/9] max-h-[280px] sm:aspect-[2/1]">
                    <img
                        src="{{ $cover }}"
                        alt=""
                        class="size-full object-cover"
                        fetchpriority="high"
                    >
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if ($competition->disciplineDisplay())
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-500">{{ $competition->disciplineDisplay() }}</p>
                @endif
                @if ($competition->cpsa_registered)
                    <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-amber-900 ring-1 ring-amber-200/80">CPSA registered</span>
                @else
                    <span class="inline-flex rounded-full bg-stone-100 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-stone-600 ring-1 ring-stone-200/90">Not CPSA registered</span>
                @endif
            </div>

            <h1 class="mt-2 font-serif text-3xl font-semibold tracking-tight text-forest sm:text-4xl">{{ $competition->title }}</h1>

            <div class="mt-6 rounded-xl border border-stone-200/90 bg-cream-dark/40 px-5 py-4">
                <h2 class="text-sm font-semibold text-forest">When</h2>
                <p class="mt-2 text-stone-800">
                    <time datetime="{{ $competition->starts_at->toIso8601String() }}">
                        {{ $competition->starts_at->format('l j F Y') }}
                        <span class="text-stone-600">· {{ $competition->starts_at->format('g:ia') }}</span>
                    </time>
                    @if ($competition->isMultiDay() && $competition->ends_at)
                        <span class="block mt-1 text-sm text-stone-600">
                            Until {{ $competition->ends_at->format('l j F Y, g:ia') }}
                        </span>
                    @elseif ($competition->ends_at && ! $competition->isMultiDay())
                        <span class="text-stone-600"> — {{ $competition->ends_at->format('g:ia') }}</span>
                    @endif
                </p>
            </div>

            <div class="mt-6">
                <h2 class="text-sm font-semibold text-forest">Where</h2>
                <p class="mt-2 text-lg text-stone-800">
                    <a href="{{ route('grounds.show', $ground) }}" class="font-medium text-forest underline decoration-forest/30 underline-offset-4 hover:text-forest-light">
                        {{ $ground->name }}
                    </a>
                    @if ($ground->county)
                        <span class="text-stone-600"> · {{ $ground->county }}</span>
                    @endif
                </p>
                @if ($ground->city)
                    <p class="mt-1 text-sm text-stone-600">{{ $ground->city }}</p>
                @endif
            </div>

            @if ($eventWeather ?? null)
                <div class="mt-6 overflow-hidden rounded-xl border border-sky-200/80 bg-gradient-to-br from-sky-50 via-white to-cream-dark/40 px-5 py-5 shadow-sm ring-1 ring-sky-100/80">
                    <h2 class="text-sm font-semibold text-forest">Weather at the ground</h2>
                    <p class="mt-1 text-xs text-stone-500">
                        {{ $eventWeather['is_forecast'] ? __('Forecast') : __('Historical') }}
                        · {{ __('Hour closest to event start') }} ({{ $competition->starts_at->timezone('Europe/London')->format('l j F Y, g:ia') }})
                    </p>
                    <div class="mt-4 flex flex-wrap items-baseline gap-2">
                        <span class="font-serif text-4xl font-semibold tabular-nums text-forest">{{ $eventWeather['temp_c'] }}°</span>
                        <span class="text-lg font-medium text-stone-500">C</span>
                    </div>
                    <p class="mt-3 text-sm font-medium text-stone-800">{{ $eventWeather['summary'] }}</p>
                    <p class="mt-2 text-sm text-stone-600">
                        {{ __('Wind') }} {{ $eventWeather['wind_mph'] }} {{ __('mph from') }} {{ $eventWeather['wind_from'] }}
                    </p>
                    <p class="mt-4 text-[11px] leading-relaxed text-stone-400">
                        <a href="https://open-meteo.com/" class="font-medium text-stone-500 underline decoration-stone-300 underline-offset-2 hover:text-forest" target="_blank" rel="noopener noreferrer">Weather data: Open-Meteo</a>
                        @if ($eventWeather['is_forecast'])
                            ({{ __('forecast') }})
                        @else
                            ({{ __('historical archive') }})
                        @endif
                    </p>
                </div>
            @endif

            @php
                $googleEmbedSrc = null;
                $googleMapsLink = null;
                if ($ground->latitude && $ground->longitude) {
                    $mapLat = (float) $ground->latitude;
                    $mapLng = (float) $ground->longitude;
                    $q = $mapLat.','.$mapLng;
                    $googleEmbedSrc = 'https://www.google.com/maps?q='.rawurlencode($q).'&z=15&output=embed';
                    $googleMapsLink = 'https://www.google.com/maps?q='.rawurlencode($q);
                } elseif ($ground->full_address || $ground->postcode || $ground->city) {
                    $q = $ground->full_address
                        ?: trim(implode(', ', array_filter([$ground->name, $ground->city, $ground->postcode, $ground->county, 'UK'])));
                    $googleEmbedSrc = 'https://www.google.com/maps?q='.rawurlencode($q).'&output=embed';
                    $googleMapsLink = 'https://www.google.com/maps?q='.rawurlencode($q);
                }
            @endphp

            @if ($googleEmbedSrc)
                <div class="mt-6">
                    <h2 class="text-sm font-semibold text-forest">Map</h2>
                    <div class="mt-3 overflow-hidden rounded-2xl bg-stone-100 ring-1 ring-stone-200/80">
                        <iframe
                            class="block h-[min(50vh,420px)] min-h-[280px] w-full border-0"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Google Map showing {{ $ground->name }}"
                            src="{{ $googleEmbedSrc }}"
                            allowfullscreen
                        ></iframe>
                    </div>
                    @if ($googleMapsLink)
                        <p class="mt-3 text-sm">
                            <a
                                href="{{ $googleMapsLink }}"
                                class="font-medium text-forest underline decoration-forest/30 underline-offset-2 hover:text-forest-light"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                Open in Google Maps
                            </a>
                        </p>
                    @endif
                </div>
            @endif

            @if ($competition->summary)
                <div class="prose prose-stone mt-10 max-w-none border-t border-stone-200 pt-8">
                    <h2 class="text-sm font-semibold text-forest">About this event</h2>
                    <p class="mt-3 whitespace-pre-wrap text-base leading-relaxed text-stone-700">{{ $competition->summary }}</p>
                </div>
            @endif

            <div class="mt-10 flex flex-wrap gap-3 border-t border-stone-200 pt-8">
                <a
                    href="{{ route('grounds.show', $ground) }}"
                    class="inline-flex items-center justify-center rounded-full bg-forest px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-light"
                >
                    View shooting ground
                </a>
                @if ($competition->external_url)
                    <a
                        href="{{ $competition->external_url }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center justify-center rounded-full border border-stone-300 bg-white px-6 py-3 text-sm font-semibold text-forest shadow-sm transition hover:border-stone-400 hover:bg-stone-50"
                    >
                        Official information
                        <svg class="ml-2 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                        </svg>
                    </a>
                @endif
                <a
                    href="{{ route('competitions.index') }}"
                    class="inline-flex items-center justify-center rounded-full px-4 py-3 text-sm font-medium text-stone-600 hover:text-forest"
                >
                    All competitions
                </a>
            </div>
        </article>
    </div>
@endsection
