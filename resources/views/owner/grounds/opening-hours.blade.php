@extends('layouts.app')

@section('title', 'Opening hours — '.$ground->name)

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:py-10">
        <nav class="mb-6 text-sm">
            <a href="{{ route('owner.dashboard') }}" class="font-medium text-forest hover:text-forest-light">← My grounds</a>
        </nav>

        @include('owner.grounds._subnav', ['ground' => $ground])

        <h1 class="font-serif text-2xl font-semibold text-forest">Opening hours</h1>
        <p class="mt-1 text-sm text-stone-600">
            Set open and close times for each row. Leave both times blank on a row to skip it. Split days (e.g. morning and afternoon) use two rows with the same weekday.
        </p>

        @if (session('status'))
            <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('status') }}</div>
        @endif

        <form method="post" action="{{ route('owner.grounds.opening-hours.update', $ground) }}" class="mt-8">
            @csrf
            @method('PUT')

            <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-stone-200 text-sm">
                    <thead>
                        <tr class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-wider text-stone-600">
                            <th class="px-4 py-3">Weekday</th>
                            <th class="px-4 py-3">Opens</th>
                            <th class="px-4 py-3">Closes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($slots as $i => $slot)
                            <tr>
                                <td class="px-4 py-2">
                                    <label class="sr-only" for="weekday-{{ $i }}">Weekday</label>
                                    <select
                                        id="weekday-{{ $i }}"
                                        name="slots[{{ $i }}][weekday]"
                                        class="w-full rounded-lg border border-stone-200 bg-white px-2 py-2 text-stone-800 focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                                    >
                                        @foreach (\App\Models\OpeningHour::WEEKDAY_LABELS as $n => $label)
                                            <option value="{{ $n }}" @selected((int) ($slot['weekday'] ?? 1) === $n)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-2">
                                    <label class="sr-only" for="opens-{{ $i }}">Opens</label>
                                    <input
                                        type="time"
                                        id="opens-{{ $i }}"
                                        name="slots[{{ $i }}][opens_at]"
                                        value="{{ $slot['opens_at'] ?? '' }}"
                                        class="w-full rounded-lg border border-stone-200 px-2 py-2 font-mono text-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                                    >
                                </td>
                                <td class="px-4 py-2">
                                    <label class="sr-only" for="closes-{{ $i }}">Closes</label>
                                    <input
                                        type="time"
                                        id="closes-{{ $i }}"
                                        name="slots[{{ $i }}][closes_at]"
                                        value="{{ $slot['closes_at'] ?? '' }}"
                                        class="w-full rounded-lg border border-stone-200 px-2 py-2 font-mono text-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                                    >
                                    <input type="hidden" name="slots[{{ $i }}][sort_order]" value="{{ (int) ($slot['sort_order'] ?? 0) }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="mt-4 text-xs text-stone-500">Need more rows? Save, then we can add a repeat block — for now duplicate a weekday in two rows.</p>

            @if ($errors->any())
                <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <button type="submit" class="mt-6 rounded-xl bg-forest px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-light">
                Save opening hours
            </button>
        </form>
    </div>
@endsection
