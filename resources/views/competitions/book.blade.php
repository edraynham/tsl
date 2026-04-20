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
                @if ($competition->open_max_participants === null)
                    <p class="mt-3 text-sm text-stone-600">{{ __('Places: no fixed limit online.') }}</p>
                @else
                    @php
                        $openMax = (int) $competition->open_max_participants;
                        $openPct = $openMax > 0 ? min(100, (int) round(100 * $openTaken / $openMax)) : 0;
                    @endphp
                    <div class="mt-4" role="group" aria-labelledby="book-open-places-label">
                        <div class="mb-1.5 flex items-baseline justify-between gap-3 text-sm text-stone-700">
                            <span id="book-open-places-label" class="font-medium text-forest">{{ __('Places') }}</span>
                            <span class="tabular-nums text-stone-600">{{ $openTaken }} / {{ $openMax }}</span>
                        </div>
                        <div
                            class="h-2.5 w-full overflow-hidden rounded-full bg-stone-200 shadow-inner"
                            role="progressbar"
                            aria-valuemin="0"
                            aria-valuemax="{{ $openMax }}"
                            aria-valuenow="{{ $openTaken }}"
                            aria-labelledby="book-open-places-label"
                        >
                            <div
                                class="h-full min-w-0 rounded-full transition-all {{ $openPct >= 100 ? 'bg-red-700' : ($openPct >= 85 ? 'bg-amber-600' : 'bg-forest') }}"
                                style="width: {{ $openPct }}%"
                            ></div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        @php
            $canSubmitOpen = $competition->registration_format === \App\Models\Competition::REGISTRATION_OPEN && $competition->openHasFreeSlots();
            $canSubmitSquadded = $competition->registration_format === \App\Models\Competition::REGISTRATION_SQUADDED
                && $competition->squaddedHasAnyFreeSlot();
            $squaddedBook = $competition->registration_format === \App\Models\Competition::REGISTRATION_SQUADDED;
        @endphp

        @if (! $canSubmitOpen && $competition->registration_format === \App\Models\Competition::REGISTRATION_OPEN)
            <p class="mt-8 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">{{ __('This event is fully booked.') }}</p>
        @elseif (! $canSubmitSquadded && $competition->registration_format === \App\Models\Competition::REGISTRATION_SQUADDED)
            <div class="mt-8">
                @include('competitions.partials.book-squads-table', ['squads' => $squads, 'interactive' => false])
            </div>
            <p class="mt-8 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">{{ __('All squads are full.') }}</p>
        @else
            <form
                method="post"
                action="{{ route('competitions.book.store', $competition) }}"
                class="mt-10 space-y-5"
                @if ($squaddedBook) data-book-squadded-registration @endif
            >
                @csrf

                @if ($squaddedBook)
                    <fieldset>
                        <legend class="block text-sm font-medium text-stone-700">{{ __('Choose a squad') }}</legend>
                        <p id="book-squad-hint" class="mt-2 text-sm text-stone-600 @if (old('competition_squad_id')) hidden @endif">
                            {{ __('Select a squad in the table to show the registration form.') }}
                        </p>
                        <div class="mt-3">
                            @include('competitions.partials.book-squads-table', [
                                'squads' => $squads,
                                'interactive' => true,
                            ])
                        </div>
                        @error('competition_squad_id')
                            <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </fieldset>
                @endif

                <div
                    id="book-registration-details"
                    class="space-y-5 @if ($squaddedBook && ! old('competition_squad_id')) hidden @endif"
                    @if ($squaddedBook && ! old('competition_squad_id')) aria-hidden="true" @endif
                >
                @if ($squaddedBook)
                    <div>
                        <label for="party_size" class="block text-sm font-medium text-stone-700">{{ __('How many people are you booking for?') }}</label>
                        <select
                            name="party_size"
                            id="party_size"
                            class="mt-1 w-full max-w-xs rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                        >
                            @for ($n = 1; $n <= 12; $n++)
                                <option value="{{ $n }}" @selected((int) old('party_size', 1) === $n)>{{ $n }}</option>
                            @endfor
                        </select>
                        @error('party_size')
                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
                @if (! $squaddedBook)
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
                @else
                    @php
                        $oldEntrants = old('entrants', []);
                        $visibleEntrants = max(1, min(12, (int) old('party_size', 1)));
                    @endphp
                    <div id="book-entrants-blocks" class="space-y-6">
                        @for ($i = 0; $i < 12; $i++)
                            <fieldset
                                class="entrant-block rounded-xl border border-stone-200/90 bg-white px-4 py-5 sm:px-5 @if ($i >= $visibleEntrants) hidden @endif"
                                data-entrant-index="{{ $i }}"
                            >
                                @if ($i === 0)
                                    <legend class="text-base font-semibold text-forest">{{ __('You — booking contact') }}</legend>
                                    <p class="mt-1 text-sm text-stone-600">{{ __("We'll use this email and phone to confirm everyone on this booking.") }}</p>
                                @else
                                    <legend class="text-base font-semibold text-forest">{{ __('Person :number', ['number' => $i + 1]) }}</legend>
                                @endif
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label for="entrant_{{ $i }}_entrant_name" class="block text-sm font-medium text-stone-700">{{ __('Name') }}</label>
                                        <input
                                            type="text"
                                            name="entrants[{{ $i }}][entrant_name]"
                                            id="entrant_{{ $i }}_entrant_name"
                                            value="{{ $oldEntrants[$i]['entrant_name'] ?? '' }}"
                                            autocomplete="name"
                                            @disabled($i >= $visibleEntrants)
                                            class="book-entrant-input mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                                        >
                                        @error('entrants.'.$i.'.entrant_name')
                                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    @if ($i === 0)
                                        <div>
                                            <label for="entrant_0_email" class="block text-sm font-medium text-stone-700">{{ __('Email') }}</label>
                                            <input
                                                type="email"
                                                name="entrants[0][email]"
                                                id="entrant_0_email"
                                                value="{{ $oldEntrants[0]['email'] ?? '' }}"
                                                autocomplete="email"
                                                class="book-entrant-input book-entrant-contact mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                                            >
                                            @error('entrants.0.email')
                                                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label for="entrant_0_telephone" class="block text-sm font-medium text-stone-700">{{ __('Telephone') }}</label>
                                            <input
                                                type="tel"
                                                name="entrants[0][telephone]"
                                                id="entrant_0_telephone"
                                                value="{{ $oldEntrants[0]['telephone'] ?? '' }}"
                                                autocomplete="tel"
                                                class="book-entrant-input book-entrant-contact mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                                            >
                                            @error('entrants.0.telephone')
                                                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    @endif
                                    <div>
                                        <label for="entrant_{{ $i }}_cpsa_number" class="block text-sm font-medium text-stone-700">{{ __('CPSA number') }}</label>
                                        <input
                                            type="text"
                                            name="entrants[{{ $i }}][cpsa_number]"
                                            id="entrant_{{ $i }}_cpsa_number"
                                            value="{{ $oldEntrants[$i]['cpsa_number'] ?? '' }}"
                                            autocomplete="off"
                                            @disabled($i >= $visibleEntrants)
                                            class="book-entrant-input mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                                        >
                                        @error('entrants.'.$i.'.cpsa_number')
                                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </fieldset>
                        @endfor
                    </div>
                    @error('entrants')
                        <p class="text-sm text-red-700">{{ $message }}</p>
                    @enderror
                @endif

                <button type="submit" class="rounded-xl bg-forest px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-light">
                    {{ __('Submit registration') }}
                </button>
                </div>
            </form>
        @endif
    </div>
    @if ($squaddedBook && $canSubmitSquadded)
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var form = document.querySelector('form[data-book-squadded-registration]');
                    if (!form) return;
                    var details = document.getElementById('book-registration-details');
                    var hint = document.getElementById('book-squad-hint');
                    if (!details) return;
                    var radios = form.querySelectorAll('input[name="competition_squad_id"]');
                    var partyUnavailableTitle = @json(__('Not enough free places in this squad.'));
                    function rebuildPartySizeOptions(radio) {
                        var sel = document.getElementById('party_size');
                        if (!sel || !radio) return;
                        var free = parseInt(String(radio.getAttribute('data-free-places') || '0'), 10);
                        if (isNaN(free) || free < 0) {
                            free = 0;
                        }
                        var cap = parseInt(String(radio.getAttribute('data-squad-capacity') || '0'), 10);
                        if (isNaN(cap) || cap < 1) {
                            cap = 1;
                        }
                        cap = Math.min(Math.max(1, cap), 12);
                        var prev = parseInt(String(sel.value || '1'), 10);
                        if (isNaN(prev) || prev < 1) {
                            prev = 1;
                        }
                        sel.innerHTML = '';
                        for (var i = 1; i <= cap; i++) {
                            var opt = document.createElement('option');
                            opt.value = String(i);
                            opt.textContent = String(i);
                            if (i > free) {
                                opt.disabled = true;
                                opt.title = partyUnavailableTitle;
                            }
                            sel.appendChild(opt);
                        }
                        var usable = Math.min(free, cap);
                        var target = Math.min(Math.max(1, prev), usable > 0 ? usable : 1);
                        var chosen = null;
                        for (var v = target; v >= 1; v--) {
                            var o = sel.querySelector('option[value="' + v + '"]');
                            if (o && !o.disabled) {
                                chosen = v;
                                break;
                            }
                        }
                        if (chosen === null) {
                            for (var w = 1; w <= cap; w++) {
                                var o2 = sel.querySelector('option[value="' + w + '"]');
                                if (o2 && !o2.disabled) {
                                    chosen = w;
                                    break;
                                }
                            }
                        }
                        if (chosen !== null) {
                            sel.value = String(chosen);
                        }
                    }
                    function syncEntrantBlocks() {
                        var checked = form.querySelector('input[name="competition_squad_id"]:checked');
                        var sel = document.getElementById('party_size');
                        var party = 0;
                        if (checked && sel) {
                            party = parseInt(String(sel.value || '1'), 10);
                            if (isNaN(party) || party < 1) {
                                party = 1;
                            }
                        }
                        form.querySelectorAll('.entrant-block').forEach(function (fs) {
                            var idx = parseInt(String(fs.getAttribute('data-entrant-index') || '0'), 10);
                            var inputs = fs.querySelectorAll('input');
                            if (!checked || idx >= party) {
                                fs.classList.add('hidden');
                                inputs.forEach(function (inp) {
                                    inp.disabled = true;
                                    inp.removeAttribute('required');
                                });
                                return;
                            }
                            fs.classList.remove('hidden');
                            inputs.forEach(function (inp) {
                                inp.disabled = false;
                            });
                            fs.querySelectorAll('.book-entrant-input').forEach(function (inp) {
                                inp.setAttribute('required', 'required');
                            });
                        });
                    }
                    function sync() {
                        var checked = form.querySelector('input[name="competition_squad_id"]:checked');
                        var partySel = document.getElementById('party_size');
                        if (checked) {
                            rebuildPartySizeOptions(checked);
                            details.classList.remove('hidden');
                            details.removeAttribute('aria-hidden');
                            if (hint) hint.classList.add('hidden');
                            if (partySel) partySel.setAttribute('required', 'required');
                            syncEntrantBlocks();
                        } else {
                            details.classList.add('hidden');
                            details.setAttribute('aria-hidden', 'true');
                            if (hint) hint.classList.remove('hidden');
                            if (partySel) partySel.removeAttribute('required');
                            form.querySelectorAll('.entrant-block input').forEach(function (inp) {
                                inp.disabled = true;
                                inp.removeAttribute('required');
                            });
                        }
                    }
                    radios.forEach(function (r) {
                        r.addEventListener('change', sync);
                        r.addEventListener('input', sync);
                    });
                    var partySizeEl = document.getElementById('party_size');
                    if (partySizeEl) {
                        partySizeEl.addEventListener('change', syncEntrantBlocks);
                    }
                    sync();
                });
            </script>
        @endpush
    @endif
@endsection
