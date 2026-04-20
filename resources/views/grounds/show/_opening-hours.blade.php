@if ($ground->hasStructuredWeeklyHours() || $ground->opening_hours)
    <div class="rounded-xl border border-tsl-outline-variant/40 bg-tsl-surface-container-low px-5 py-5 shadow-sm">
        <h2 class="font-tsl-headline text-lg font-semibold text-tsl-primary">Opening hours</h2>
        @if ($ground->hasStructuredWeeklyHours())
            @php $oh = $ground->openingHours; @endphp
            <dl class="mt-4 space-y-2 text-sm text-tsl-on-surface">
                @foreach (\App\Models\OpeningHours::WEEKDAY_LABELS as $iso => $label)
                    @php
                        $prefix = \App\Models\ShootingGround::DAY_PREFIXES[(int) $iso - 1] ?? null;
                        $o = $prefix ? $oh?->{$prefix.'_opens_at'} : null;
                        $c = $prefix ? $oh?->{$prefix.'_closes_at'} : null;
                        $isToday = (int) $iso === now()->dayOfWeekIso;
                    @endphp
                    <div class="flex flex-col gap-0.5 sm:flex-row sm:gap-3">
                        <dt class="shrink-0 sm:w-28 {{ $isToday ? 'font-bold text-tsl-primary' : 'font-medium text-tsl-secondary' }}">{{ $label }}</dt>
                        <dd class="leading-relaxed {{ $isToday ? 'font-semibold text-tsl-on-surface' : '' }}">
                            @if ($o && $c)
                                <span>{{ $o->format('g:ia') }}–{{ $c->format('g:ia') }}</span>
                            @else
                                <span class="{{ $isToday ? 'text-tsl-on-surface' : 'text-tsl-outline' }}">Closed</span>
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
        @else
            <div class="mt-3 whitespace-pre-line text-sm leading-relaxed text-tsl-secondary">{{ $ground->opening_hours }}</div>
        @endif
    </div>
@endif
