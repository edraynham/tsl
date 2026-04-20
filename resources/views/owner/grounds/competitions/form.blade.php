@extends('layouts.app')

@php
    $editing = $competition->exists;
    $disciplineOptions = $disciplines->map(fn ($d) => [
        'id' => (int) $d->id,
        'code' => (string) ($d->code ?? ''),
        'name' => (string) $d->name,
    ])->values()->all();
    $rawDisciplineId = old('discipline_id', $competition->discipline_id);
    $selectedDisciplineId = $rawDisciplineId !== null && $rawDisciplineId !== '' ? (int) $rawDisciplineId : null;
@endphp

@section('title', ($editing ? 'Edit' : 'Add').' competition — '.$ground->name)

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:py-10">
        <nav class="mb-6 text-sm">
            <a href="{{ route('account.competitions.index', ['ground' => $ground->slug]) }}" class="font-medium text-forest hover:text-forest-light">← {{ __('My competitions') }}</a>
        </nav>

        @include('account._tabs', ['active' => 'competitions'])

        <h1 class="font-serif text-2xl font-semibold text-forest">{{ $editing ? 'Edit competition' : 'New competition' }}</h1>
        <p class="mt-1 text-sm text-stone-600">{{ $ground->name }}</p>

        @if (session('status'))
            <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('status') }}</div>
        @endif

        <form
            method="post"
            action="{{ $editing ? route('account.competitions.update', $competition) : route('account.competitions.store') }}"
            class="mt-8 space-y-5"
        >
            @csrf
            @if ($editing)
                @method('PUT')
            @else
                <input type="hidden" name="shooting_ground_id" value="{{ $ground->id }}">
            @endif

            <div>
                <label class="block text-sm font-medium text-stone-700" for="title">Title</label>
                <input
                    type="text"
                    name="title"
                    id="title"
                    value="{{ old('title', $competition->title) }}"
                    required
                    class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-2.5 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700" for="slug">Slug (optional)</label>
                <input
                    type="text"
                    name="slug"
                    id="slug"
                    value="{{ old('slug', $competition->slug) }}"
                    class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-2.5 font-mono text-sm text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                >
                <p class="mt-1 text-xs text-stone-500">Leave blank to generate from the title.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700" for="summary">Summary</label>
                <textarea
                    name="summary"
                    id="summary"
                    rows="4"
                    class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-2.5 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                >{{ old('summary', $competition->summary) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700" for="starts_at">Starts</label>
                <input
                    type="datetime-local"
                    name="starts_at"
                    id="starts_at"
                    value="{{ old('starts_at', $competition->starts_at?->format('Y-m-d\TH:i')) }}"
                    required
                    class="mt-1 w-full max-w-md rounded-xl border border-stone-200 bg-white px-4 py-2.5 font-mono text-sm text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                >
            </div>

            <div
                class="mt-1"
                data-discipline-combobox
                data-i18n-no-match="{{ __('No matching disciplines') }}"
            >
                <label class="block text-sm font-medium text-stone-700" for="discipline_search">{{ __('Discipline') }}</label>
                <script type="application/json" data-discipline-json>@json($disciplineOptions)</script>
                <input type="hidden" name="discipline_id" value="{{ $selectedDisciplineId ?? '' }}" data-discipline-hidden>
                <div class="relative mt-1 flex flex-wrap items-stretch gap-2 sm:flex-nowrap">
                    <div class="relative min-w-0 flex-1">
                        <input
                            type="text"
                            id="discipline_search"
                            value=""
                            autocomplete="off"
                            role="combobox"
                            aria-autocomplete="list"
                            aria-expanded="false"
                            aria-controls="discipline-suggestions-list"
                            data-discipline-input
                            placeholder="{{ __('Search by code or name…') }}"
                            class="w-full rounded-xl border border-stone-200 bg-white px-4 py-2.5 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                        >
                        <ul
                            id="discipline-suggestions-list"
                            data-discipline-suggestions
                            class="absolute left-0 right-0 top-full z-30 mt-1 max-h-56 overflow-auto rounded-xl border border-stone-200 bg-white py-1 shadow-lg ring-1 ring-stone-900/5 hidden"
                            role="listbox"
                            aria-label="{{ __('Disciplines') }}"
                        ></ul>
                    </div>
                    <button
                        type="button"
                        data-discipline-clear
                        class="shrink-0 rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm font-semibold text-stone-700 shadow-sm transition hover:border-stone-400 hover:bg-stone-50"
                    >
                        {{ __('Clear') }}
                    </button>
                </div>
                <p class="mt-1 text-xs text-stone-500">{{ __('Optional. Shown on the public calendar and used for event search.') }}</p>
                @error('discipline_id')
                    <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-start gap-3 rounded-xl border border-stone-200 bg-cream-dark/30 px-4 py-3">
                <input
                    type="checkbox"
                    name="cpsa_registered"
                    id="cpsa_registered"
                    value="1"
                    @checked(old('cpsa_registered', $competition->cpsa_registered))
                    class="mt-1 size-4 rounded border-stone-300 text-forest focus:ring-forest"
                >
                <div>
                    <label class="text-sm font-medium text-stone-800" for="cpsa_registered">CPSA registered</label>
                    <p class="mt-0.5 text-xs text-stone-600">Tick if this is an official CPSA registered competition.</p>
                </div>
            </div>

            <fieldset class="rounded-xl border border-stone-200 bg-white px-4 py-4 shadow-sm">
                <legend class="text-sm font-semibold text-forest">{{ __('Online registration') }}</legend>
                <p class="mt-1 text-xs text-stone-600">{{ __('Closed: no booking page. Open: one shared list of registrations. Squadded: squad start times and booking setup are under Manage squads.') }}</p>
                @php
                    $regFmt = old('registration_format', $competition->registration_format ?? \App\Models\Competition::REGISTRATION_CLOSED);
                @endphp
                <div class="mt-4 space-y-3">
                    <label class="flex cursor-pointer items-start gap-3">
                        <input type="radio" name="registration_format" value="{{ \App\Models\Competition::REGISTRATION_CLOSED }}" class="mt-1 text-forest focus:ring-forest" @checked($regFmt === \App\Models\Competition::REGISTRATION_CLOSED)>
                        <span class="text-sm text-stone-800"><span class="font-medium">{{ __('Closed') }}</span> — {{ __('No public registration form.') }}</span>
                    </label>
                    <label class="flex cursor-pointer items-start gap-3">
                        <input type="radio" name="registration_format" value="{{ \App\Models\Competition::REGISTRATION_OPEN }}" class="mt-1 text-forest focus:ring-forest" @checked($regFmt === \App\Models\Competition::REGISTRATION_OPEN)>
                        <span class="text-sm text-stone-800"><span class="font-medium">{{ __('Open') }}</span> — {{ __('Single list; no fixed limit online.') }}</span>
                    </label>
                    <label class="flex cursor-pointer items-start gap-3">
                        <input type="radio" name="registration_format" value="{{ \App\Models\Competition::REGISTRATION_SQUADDED }}" class="mt-1 text-forest focus:ring-forest" @checked($regFmt === \App\Models\Competition::REGISTRATION_SQUADDED)>
                        <span class="text-sm text-stone-800"><span class="font-medium">{{ __('Squadded') }}</span> — {{ __('Shooters book by squad; set squad start times under Manage squads.') }}</span>
                    </label>
                </div>
                @error('registration_format')
                    <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </fieldset>

            @if ($editing)
                <div class="flex flex-wrap gap-3 rounded-xl border border-stone-200 bg-stone-50/80 px-4 py-4 text-sm">
                    <a href="{{ route('account.competitions.squads.edit', $competition) }}" class="font-medium text-forest underline decoration-forest/30 underline-offset-2 hover:text-forest-light">{{ __('Manage squads') }}</a>
                    <span class="text-stone-300" aria-hidden="true">·</span>
                    <a href="{{ route('account.competitions.registrations.index', $competition) }}" class="font-medium text-forest underline decoration-forest/30 underline-offset-2 hover:text-forest-light">{{ __('View registrations') }}</a>
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-stone-700" for="external_url">More info URL</label>
                <input
                    type="url"
                    name="external_url"
                    id="external_url"
                    value="{{ old('external_url', $competition->external_url) }}"
                    class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-2.5 text-sm text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                >
            </div>

            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <button type="submit" class="rounded-xl bg-forest px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-light">
                {{ $editing ? 'Save changes' : 'Create competition' }}
            </button>
        </form>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/owner-competition-form-entry.js')
@endpush
