@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\CompetitionSquad> $squads */
    /** @var bool $interactive */
    $interactive = $interactive ?? false;
@endphp

<div class="overflow-x-auto rounded-xl border border-stone-200 bg-white shadow-sm">
    <table class="min-w-full divide-y divide-stone-200 text-left text-sm">
        <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-wide text-stone-600">
            <tr>
                @if ($interactive)
                    <th class="w-14 px-3 py-3" scope="col">
                        <span class="sr-only">{{ __('Choose') }}</span>
                    </th>
                @endif
                <th class="px-4 py-3" scope="col">{{ __('Squad') }}</th>
                <th class="px-4 py-3" scope="col">{{ __('Start time') }}</th>
                <th class="min-w-[10rem] px-4 py-3" scope="col">{{ __('Places') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100 text-stone-800">
            @foreach ($squads as $squad)
                @php
                    $taken = (int) ($squad->registrations_count ?? 0);
                    $cap = $squad->capacity();
                    $squadPct = $cap > 0 ? min(100, (int) round(100 * $taken / $cap)) : 0;
                    $full = $taken >= $cap;
                    $checked = $interactive && (int) old('competition_squad_id') === $squad->id;
                @endphp
                <tr
                    @if ($interactive && ! $full)
                        onclick="if (!event.target.closest || !event.target.closest('input[type=radio]')) { var el = document.getElementById('book-squad-radio-{{ $squad->id }}'); if (el) { el.click(); } }"
                    @endif
                    @class([
                        'transition-colors' => $interactive,
                        'cursor-pointer' => $interactive && ! $full,
                        'hover:bg-stone-50' => $interactive && ! $full,
                        'has-[:checked]:bg-cream-dark/50' => $interactive,
                        'opacity-60' => $interactive && $full,
                    ])
                >
                    @if ($interactive)
                        <td class="px-3 py-3 align-middle">
                            <input
                                type="radio"
                                name="competition_squad_id"
                                id="book-squad-radio-{{ $squad->id }}"
                                value="{{ $squad->id }}"
                                class="size-4 border-stone-300 text-forest focus:ring-forest"
                                data-free-places="{{ max(0, $cap - $taken) }}"
                                data-squad-capacity="{{ $cap }}"
                                @checked($checked)
                                @disabled($full)
                            >
                        </td>
                    @endif
                    <td class="px-4 py-3 font-medium align-middle">
                        @if ($interactive)
                            <label for="book-squad-radio-{{ $squad->id }}" class="cursor-pointer">
                                {{ $squad->label() }}
                            </label>
                        @else
                            {{ $squad->label() }}
                        @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap align-middle">{{ $squad->starts_at->timezone('Europe/London')->format('D j M, g:ia') }}</td>
                    <td class="px-4 py-3 align-middle">
                        @if ($full)
                            <p @class([
                                'text-base font-bold',
                                'text-red-800' => $interactive,
                                'text-stone-600' => ! $interactive,
                            ])>{{ __('Full') }}</p>
                        @else
                            @php $squadPlacesId = 'book-squad-places-'.$squad->id; @endphp
                            <div class="max-w-xs" role="group" aria-labelledby="{{ $squadPlacesId }}">
                                <div class="mb-1.5 flex justify-end">
                                    <span id="{{ $squadPlacesId }}" class="text-xs tabular-nums text-stone-600">{{ $taken }} / {{ $cap }}</span>
                                </div>
                                <div
                                    class="h-2 w-full overflow-hidden rounded-full bg-stone-200 shadow-inner"
                                    role="progressbar"
                                    aria-valuemin="0"
                                    aria-valuemax="{{ $cap }}"
                                    aria-valuenow="{{ $taken }}"
                                    aria-labelledby="{{ $squadPlacesId }}"
                                >
                                    <div
                                        class="h-full min-w-0 rounded-full transition-all {{ $squadPct >= 85 ? 'bg-amber-600' : 'bg-forest' }}"
                                        style="width: {{ $squadPct }}%"
                                    ></div>
                                </div>
                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
