@extends('layouts.app')

@section('title', __('Register').' — '.$competition->title)

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
        <nav class="mb-8 text-sm">
            <a href="{{ route('competitions.show', $competition) }}" class="font-medium text-forest hover:text-forest-light">← {{ $competition->title }}</a>
        </nav>

        <h1 class="font-serif text-3xl font-semibold tracking-tight text-forest sm:text-4xl">{{ __('Register for this event') }}</h1>
        <p class="mt-2 text-stone-600">
            <a href="{{ route('grounds.show', $ground) }}" class="font-medium text-forest underline decoration-forest/30 underline-offset-2 hover:text-forest-light">{{ $ground->name }}</a>
        </p>

        @if ($competition->registration_format === \App\Models\Competition::REGISTRATION_OPEN)
            <div class="mt-8 rounded-xl border border-stone-200/90 bg-cream-dark/40 px-5 py-4">
                <h2 class="text-sm font-semibold text-forest">{{ __('Event time') }}</h2>
                <p class="mt-2 text-stone-800">
                    <time datetime="{{ $competition->starts_at->toIso8601String() }}">
                        {{ $competition->starts_at->format('l j F Y') }}
                        <span class="text-stone-600">· {{ $competition->starts_at->format('g:ia') }}</span>
                    </time>
                </p>
                <p class="mt-3 text-sm text-stone-600">
                    @if ($competition->open_max_participants === null)
                        {{ __('Places: no fixed limit online.') }}
                    @else
                        @php $free = max(0, $competition->open_max_participants - $openTaken); @endphp
                        {{ __('Places taken: :taken / :max (:free free)', ['taken' => $openTaken, 'max' => $competition->open_max_participants, 'free' => $free]) }}
                    @endif
                </p>
            </div>
        @elseif ($competition->registration_format === \App\Models\Competition::REGISTRATION_SQUADDED)
            <div class="mt-8 overflow-x-auto rounded-xl border border-stone-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-stone-200 text-left text-sm">
                    <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-wide text-stone-600">
                        <tr>
                            <th class="px-4 py-3">{{ __('Squad') }}</th>
                            <th class="px-4 py-3">{{ __('Start time') }}</th>
                            <th class="px-4 py-3">{{ __('Capacity') }}</th>
                            <th class="px-4 py-3">{{ __('Free slots') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 text-stone-800">
                        @foreach ($squads as $squad)
                            @php
                                $taken = (int) $squad->registrations_count;
                                $cap = $squad->capacity();
                                $free = max(0, $cap - $taken);
                            @endphp
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $squad->label() }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $squad->starts_at->timezone('Europe/London')->format('D j M, g:ia') }}</td>
                                <td class="px-4 py-3 tabular-nums">{{ $taken }} / {{ $cap }}</td>
                                <td class="px-4 py-3 tabular-nums">{{ $free }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @php
            $canSubmitOpen = $competition->registration_format === \App\Models\Competition::REGISTRATION_OPEN && $competition->openHasFreeSlots();
            $canSubmitSquadded = $competition->registration_format === \App\Models\Competition::REGISTRATION_SQUADDED
                && $competition->squaddedHasAnyFreeSlot();
        @endphp

        @if (! $canSubmitOpen && $competition->registration_format === \App\Models\Competition::REGISTRATION_OPEN)
            <p class="mt-8 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">{{ __('This event is fully booked.') }}</p>
        @elseif (! $canSubmitSquadded && $competition->registration_format === \App\Models\Competition::REGISTRATION_SQUADDED)
            <p class="mt-8 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">{{ __('All squads are full.') }}</p>
        @else
            <form method="post" action="{{ route('competitions.book.store', $competition) }}" class="mt-10 space-y-5">
                @csrf

                @if ($competition->registration_format === \App\Models\Competition::REGISTRATION_SQUADDED)
                    @php
                        $firstAvailableSquad = $squads->first(fn ($s) => (int) $s->registrations_count < $s->capacity());
                    @endphp
                    <fieldset>
                        <legend class="block text-sm font-medium text-stone-700">{{ __('Choose a squad') }}</legend>
                        <div class="mt-3 space-y-2">
                            @foreach ($squads as $squad)
                                @php
                                    $taken = (int) $squad->registrations_count;
                                    $cap = $squad->capacity();
                                    $full = $taken >= $cap;
                                    $checked = (int) old('competition_squad_id') === $squad->id
                                        || (old('competition_squad_id') === null && $firstAvailableSquad && $firstAvailableSquad->is($squad));
                                @endphp
                                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-stone-200 px-4 py-3 has-[:disabled]:cursor-not-allowed has-[:disabled]:opacity-60">
                                    <input
                                        type="radio"
                                        name="competition_squad_id"
                                        value="{{ $squad->id }}"
                                        class="mt-1 text-forest focus:ring-forest"
                                        @checked($checked)
                                        @disabled($full)
                                    >
                                    <span class="text-sm">
                                        <span class="font-medium text-stone-900">{{ $squad->label() }}</span>
                                        <span class="block text-stone-600">{{ $squad->starts_at->timezone('Europe/London')->format('l j M Y, g:ia') }}</span>
                                        @if ($full)
                                            <span class="mt-1 block text-xs font-medium text-red-800">{{ __('Full') }}</span>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('competition_squad_id')
                            <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </fieldset>
                @endif

                <div>
                    <label for="cpsa_number" class="block text-sm font-medium text-stone-700">{{ __('CPSA number') }}</label>
                    <input
                        type="text"
                        name="cpsa_number"
                        id="cpsa_number"
                        value="{{ old('cpsa_number') }}"
                        required
                        autocomplete="off"
                        class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                    >
                    @error('cpsa_number')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="entrant_name" class="block text-sm font-medium text-stone-700">{{ __('Name') }}</label>
                    <input
                        type="text"
                        name="entrant_name"
                        id="entrant_name"
                        value="{{ old('entrant_name') }}"
                        required
                        autocomplete="name"
                        class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                    >
                    @error('entrant_name')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-stone-700">{{ __('Email') }}</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="telephone" class="block text-sm font-medium text-stone-700">{{ __('Telephone') }}</label>
                    <input
                        type="tel"
                        name="telephone"
                        id="telephone"
                        value="{{ old('telephone') }}"
                        required
                        autocomplete="tel"
                        class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                    >
                    @error('telephone')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="rounded-xl bg-forest px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-light">
                    {{ __('Submit registration') }}
                </button>
            </form>
        @endif
    </div>
@endsection
