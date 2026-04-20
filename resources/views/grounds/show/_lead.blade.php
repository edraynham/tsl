<header>
    <div class="mb-3 flex items-center gap-2">
        <span class="size-1.5 shrink-0 rounded-full bg-tsl-tertiary" aria-hidden="true"></span>
        <p class="text-[11px] font-bold tracking-[0.15em] text-tsl-secondary uppercase">Shooting ground</p>
    </div>
    <h1 class="font-tsl-headline text-4xl font-bold tracking-tight text-tsl-primary md:text-5xl">{{ $ground->name }}</h1>
</header>

@if ($ground->full_address || $ground->postcode)
    <p class="text-lg leading-relaxed text-tsl-secondary">
        @if ($ground->full_address)
            {{ $ground->full_address }}
        @endif
        @if ($ground->postcode && $ground->full_address && ! str_contains($ground->full_address, $ground->postcode))
            <br><span class="text-tsl-outline">{{ $ground->postcode }}</span>
        @elseif ($ground->postcode)
            {{ $ground->postcode }}
        @endif
    </p>
@endif

@if ($ground->disciplines->isNotEmpty())
    <div class="mt-4">
        <p class="text-[11px] font-bold tracking-[0.15em] text-tsl-secondary uppercase">Disciplines</p>
        <ul class="mt-2 flex flex-wrap gap-2">
            @foreach ($ground->disciplines->sortBy('name') as $disc)
                <li class="rounded-md border border-tsl-outline-variant/50 bg-tsl-surface-container-low px-2.5 py-1 font-mono text-[11px] font-semibold uppercase tracking-wide text-tsl-primary">
                    {{ $disc->code }}
                    <span class="font-tsl-body text-[11px] font-normal normal-case text-tsl-secondary"> — {{ $disc->name }}</span>
                </li>
            @endforeach
        </ul>
    </div>
@endif
