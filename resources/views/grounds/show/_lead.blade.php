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
