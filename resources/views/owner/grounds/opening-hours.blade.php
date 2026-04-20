@extends('layouts.app')

@section('title', 'Opening hours — '.$ground->name)

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:py-10">
        <nav class="mb-6 text-sm">
            <a href="{{ route('owner.dashboard') }}" class="font-medium text-forest hover:text-forest-light">← My grounds</a>
        </nav>

        @include('account._tabs', ['active' => 'grounds'])

        @include('owner.grounds._subnav', ['ground' => $ground])

        <h1 class="font-serif text-2xl font-semibold text-forest">Opening hours</h1>
        <p class="mt-1 text-sm text-stone-600">
            Set open and close times for each day. Leave both times blank for a closed day.
        </p>

        @if (session('status'))
            <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('status') }}</div>
        @endif

        <form method="post" action="{{ route('owner.grounds.opening-hours.update', $ground) }}" class="mt-8">
            @csrf
            @method('PUT')

            <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white shadow-sm">
                <table class="min-w-[640px] w-full divide-y divide-stone-200 text-sm">
                    <thead>
                        <tr class="bg-stone-50 text-center text-xs font-semibold uppercase tracking-wider text-stone-600">
                            @foreach (\App\Models\OpeningHours::WEEKDAY_LABELS as $label)
                                <th class="px-2 py-3 sm:px-3">{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <tr>
                            @foreach (\App\Models\ShootingGround::DAY_PREFIXES as $day)
                                @php
                                    $slot = $days[$day] ?? ['opens_at' => '', 'closes_at' => ''];
                                @endphp
                                <td class="align-top px-2 py-3 sm:px-3">
                                    <div class="flex flex-col gap-2">
                                        <div>
                                            <label class="mb-0.5 block text-[10px] font-medium uppercase tracking-wide text-stone-500" for="opens-{{ $day }}">Opens</label>
                                            <input
                                                type="time"
                                                id="opens-{{ $day }}"
                                                name="days[{{ $day }}][opens_at]"
                                                value="{{ $slot['opens_at'] ?? '' }}"
                                                class="w-full min-w-0 rounded-lg border border-stone-200 px-1.5 py-2 font-mono text-xs focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest sm:text-sm"
                                            >
                                        </div>
                                        <div>
                                            <label class="mb-0.5 block text-[10px] font-medium uppercase tracking-wide text-stone-500" for="closes-{{ $day }}">Closes</label>
                                            <input
                                                type="time"
                                                id="closes-{{ $day }}"
                                                name="days[{{ $day }}][closes_at]"
                                                value="{{ $slot['closes_at'] ?? '' }}"
                                                class="w-full min-w-0 rounded-lg border border-stone-200 px-1.5 py-2 font-mono text-xs focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest sm:text-sm"
                                            >
                                        </div>
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>

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
