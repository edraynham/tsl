@props(['ground'])

<nav class="mb-8 flex flex-wrap gap-x-4 gap-y-2 border-b border-stone-200 pb-4 text-sm" aria-label="Ground editor">
    <a
        href="{{ route('owner.grounds.edit', $ground) }}"
        class="{{ request()->routeIs('owner.grounds.edit') ? 'font-semibold text-forest' : 'text-stone-600 hover:text-forest' }}"
    >
        Profile &amp; preview
    </a>
    <span class="text-stone-300" aria-hidden="true">|</span>
    <a
        href="{{ route('owner.grounds.opening-hours.edit', $ground) }}"
        class="{{ request()->routeIs('owner.grounds.opening-hours.*') ? 'font-semibold text-forest' : 'text-stone-600 hover:text-forest' }}"
    >
        Opening hours
    </a>
    <span class="text-stone-300" aria-hidden="true">|</span>
    <a
        href="{{ route('owner.grounds.competitions.index', $ground) }}"
        class="{{ request()->routeIs('owner.grounds.competitions.*') ? 'font-semibold text-forest' : 'text-stone-600 hover:text-forest' }}"
    >
        Competitions
    </a>
    <span class="text-stone-300" aria-hidden="true">|</span>
    <a href="{{ route('grounds.show', $ground) }}" class="text-stone-500 hover:text-forest">View public page</a>
</nav>
