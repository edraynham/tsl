@extends('layouts.app')

@php
    $tz = $squadScheduleTz ?? 'Europe/London';
    $tplStart = $competition->starts_at->timezone($tz);
    $tplDate = $tplStart->format('Y-m-d');
    $tplTime = $tplStart->format('G:i');
@endphp

@section('title', __('Squads').' — '.$competition->title)

@section('content')
    <div
        class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:py-10"
        data-owner-competition-squads
        data-next-squad-index="{{ $nextSquadIndex }}"
    >
        <nav class="mb-6 text-sm">
            <a href="{{ route('account.competitions.edit', $competition) }}" class="font-medium text-forest hover:text-forest-light">← {{ __('Edit competition') }}</a>
        </nav>

        @include('account._tabs', ['active' => 'competitions'])

        <h1 class="font-serif text-2xl font-semibold text-forest">{{ __('Squads & start times') }}</h1>
        <p class="mt-1 text-sm text-stone-600">{{ $competition->title }}</p>

        @if (session('status'))
            <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('status') }}</div>
        @endif

        <p class="mt-6 text-sm text-stone-600">
            {{ __('Set squad size and when each squad starts. You start with one row; use “Add squad” for more.') }}
        </p>
        <p class="mt-2 text-xs text-stone-500">
            {{ __('Pick the date from the calendar. Type the time in UK (London) — e.g. 9:00, 14:30, or 2:30pm.') }}
        </p>

        <form method="post" action="{{ route('account.competitions.squads.update', $competition) }}" class="mt-8 space-y-6">
            @csrf
            @method('PUT')

            <div class="overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
                <div class="flex flex-wrap gap-x-2 gap-y-1 border-b border-stone-200 bg-stone-50 px-3 py-2.5 sm:gap-x-3 sm:px-4">
                    <div class="w-[4.5rem] shrink-0 text-[10px] font-semibold uppercase leading-tight tracking-wide text-stone-600 sm:w-24 sm:text-xs">
                        {{ __('Squad') }}
                    </div>
                    <div class="w-[8.5rem] shrink-0 text-[10px] font-semibold uppercase leading-tight tracking-wide text-stone-600 sm:w-36 sm:text-xs">
                        {{ __('Date') }}
                    </div>
                    <div class="min-w-[5.5rem] flex-1 text-[10px] font-semibold uppercase leading-tight tracking-wide text-stone-600 sm:min-w-[6rem] sm:text-xs">
                        {{ __('Time') }}
                    </div>
                    <div class="w-16 shrink-0 text-[10px] font-semibold uppercase leading-tight tracking-wide text-stone-600 sm:w-20 sm:text-xs">
                        {{ __('People') }}
                    </div>
                    <div class="w-10 shrink-0 sm:w-11" aria-hidden="true"></div>
                </div>
                <ul class="divide-y divide-stone-200" data-squad-rows>
                    @foreach ($squadRows as $i => $row)
                        @php
                            $sizeVal = (int) old('squads.'.$i.'.max_participants', $row['max_participants'] ?? 6);
                            $startDate = old('squads.'.$i.'.start_date', $row['start_date'] ?? '');
                            $startTime = old('squads.'.$i.'.start_time', $row['start_time'] ?? '');
                        @endphp
                        <li class="flex flex-wrap items-end gap-2 px-3 py-3 sm:flex-nowrap sm:items-center sm:gap-3 sm:px-4 sm:py-3.5" data-squad-row>
                            <input type="hidden" name="squads[{{ $i }}][id]" value="{{ $row['id'] ?? '' }}">
                            <div class="w-[4.5rem] shrink-0 sm:w-24">
                                <span class="text-sm font-medium text-stone-800">
                                    {{ __('Squad') }} <span data-squad-label-index class="tabular-nums">{{ $i + 1 }}</span>
                                </span>
                            </div>
                            <div class="w-[8.5rem] shrink-0 sm:w-36">
                                <label for="sq-date-{{ $i }}" class="sr-only">{{ __('Date') }}</label>
                                <input
                                    type="date"
                                    name="squads[{{ $i }}][start_date]"
                                    id="sq-date-{{ $i }}"
                                    value="{{ $startDate }}"
                                    class="w-full rounded-xl border border-stone-200 bg-white px-2 py-2 text-sm text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                                >
                            </div>
                            <div class="min-w-0 flex-1 sm:min-w-[6rem]">
                                <label for="sq-time-{{ $i }}" class="sr-only">{{ __('Start time') }}</label>
                                <input
                                    type="text"
                                    name="squads[{{ $i }}][start_time]"
                                    id="sq-time-{{ $i }}"
                                    value="{{ $startTime }}"
                                    inputmode="text"
                                    autocomplete="off"
                                    placeholder="{{ __('e.g. 9:00') }}"
                                    class="w-full min-w-0 rounded-xl border border-stone-200 bg-white px-3 py-2 font-mono text-sm text-stone-800 shadow-sm placeholder:text-stone-400 focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                                >
                            </div>
                            <div class="w-16 shrink-0 sm:w-20">
                                <label for="sq-size-{{ $i }}" class="sr-only">{{ __('People') }}</label>
                                <select
                                    name="squads[{{ $i }}][max_participants]"
                                    id="sq-size-{{ $i }}"
                                    class="w-full rounded-xl border border-stone-200 bg-white px-1.5 py-2 text-sm text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest sm:px-2"
                                >
                                    @foreach ($squadSizeOptions as $n)
                                        <option value="{{ $n }}" @selected($sizeVal === $n)>{{ $n }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex w-10 shrink-0 items-center justify-end sm:w-11">
                                <button
                                    type="button"
                                    data-remove-squad-row
                                    title="{{ __('Remove squad') }}"
                                    aria-label="{{ __('Remove squad') }}"
                                    class="hidden inline-flex size-9 items-center justify-center rounded-lg border border-stone-200 text-red-700 shadow-sm transition hover:border-red-200 hover:bg-red-50 disabled:opacity-50"
                                >
                                    <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button
                    type="button"
                    data-add-squad-row
                    class="rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm font-semibold text-forest shadow-sm transition hover:border-stone-400 hover:bg-stone-50"
                >
                    {{ __('Add squad') }}
                </button>
            </div>

            <template data-squad-row-template>
                <li class="flex flex-wrap items-end gap-2 px-3 py-3 sm:flex-nowrap sm:items-center sm:gap-3 sm:px-4 sm:py-3.5" data-squad-row>
                    <input type="hidden" name="squads[__INDEX__][id]" value="">
                    <div class="w-[4.5rem] shrink-0 sm:w-24">
                        <span class="text-sm font-medium text-stone-800">
                            {{ __('Squad') }} <span data-squad-label-index class="tabular-nums">1</span>
                        </span>
                    </div>
                    <div class="w-[8.5rem] shrink-0 sm:w-36">
                        <label class="sr-only" for="sq-date-__INDEX__">{{ __('Date') }}</label>
                        <input
                            type="date"
                            name="squads[__INDEX__][start_date]"
                            id="sq-date-__INDEX__"
                            value="{{ $tplDate }}"
                            class="w-full rounded-xl border border-stone-200 bg-white px-2 py-2 text-sm text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                        >
                    </div>
                    <div class="min-w-0 flex-1 sm:min-w-[6rem]">
                        <label class="sr-only" for="sq-time-__INDEX__">{{ __('Start time') }}</label>
                        <input
                            type="text"
                            name="squads[__INDEX__][start_time]"
                            id="sq-time-__INDEX__"
                            value="{{ $tplTime }}"
                            inputmode="text"
                            autocomplete="off"
                            placeholder="{{ __('e.g. 9:00') }}"
                            class="w-full min-w-0 rounded-xl border border-stone-200 bg-white px-3 py-2 font-mono text-sm text-stone-800 shadow-sm placeholder:text-stone-400 focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                        >
                    </div>
                    <div class="w-16 shrink-0 sm:w-20">
                        <label class="sr-only" for="sq-size-__INDEX__">{{ __('People') }}</label>
                        <select
                            name="squads[__INDEX__][max_participants]"
                            id="sq-size-__INDEX__"
                            class="w-full rounded-xl border border-stone-200 bg-white px-1.5 py-2 text-sm text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest sm:px-2"
                        >
                            @foreach ($squadSizeOptions as $n)
                                <option value="{{ $n }}" @selected($n === 6)>{{ $n }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex w-10 shrink-0 items-center justify-end sm:w-11">
                        <button
                            type="button"
                            data-remove-squad-row
                            title="{{ __('Remove squad') }}"
                            aria-label="{{ __('Remove squad') }}"
                            class="inline-flex size-9 items-center justify-center rounded-lg border border-stone-200 text-red-700 shadow-sm transition hover:border-red-200 hover:bg-red-50 disabled:opacity-50"
                        >
                            <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>
                    </div>
                </li>
            </template>

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
                {{ __('Save squads') }}
            </button>
        </form>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/owner-competition-squads-entry.js')
@endpush
