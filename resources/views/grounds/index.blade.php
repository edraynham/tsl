@extends('layouts.app')

@section('title', 'Directory — '.config('app.name'))

@push('head')
    <link href="https://fonts.bunny.net/css?family=newsreader:400,500,600,700|work-sans:300,400,500,600,700&display=swap" rel="stylesheet">
@endpush

@section('content')
    @php
        $facilities = is_array($facilities ?? null) ? $facilities : [];
        $disciplineFilter = is_array($disciplineFilter ?? null) ? $disciplineFilter : [];
        $allDisciplines = isset($allDisciplines) ? $allDisciplines : collect();
        $sort = $sort ?? 'az';
        $viewMode = $viewMode ?? 'list';
        $distance = request('distance');
        $filterQuery = array_merge(request()->except(['page', 'distance']), ['view' => $viewMode]);
    @endphp

    <div class="bg-tsl-surface font-tsl-body text-tsl-on-surface" data-directory-geo>
        <main class="mx-auto max-w-screen-2xl px-4 py-10 sm:px-8 sm:py-12">
            {{-- Editorial header --}}
            <header class="mb-12 sm:mb-16">
                <p class="mb-2 font-tsl-body text-xs font-semibold uppercase tracking-[0.15em] text-tsl-secondary">Curated collections</p>
                <h1 class="mb-4 font-tsl-headline text-4xl font-bold tracking-tight text-tsl-primary md:text-5xl lg:text-6xl">Shooting Grounds</h1>
                <p class="max-w-2xl font-tsl-body text-lg leading-relaxed text-tsl-secondary">
                    Discover the finest sporting estates and shooting grounds. From historic woodland skeet to modern clay traps, find your next marksman destination.
                </p>
            </header>

            <form method="get" action="{{ route('grounds.index') }}" class="flex flex-col gap-10 lg:flex-row lg:gap-12" id="directory-filters">
                {{-- Filters sidebar --}}
                <aside class="w-full shrink-0 space-y-10 lg:w-72">
                    {{-- View toggle --}}
                    <div class="flex items-center rounded-full bg-tsl-surface-container p-1">
                        <a
                            href="{{ route('grounds.index', array_merge(request()->except('page'), ['view' => 'list'])) }}"
                            class="flex flex-1 items-center justify-center gap-2 rounded-full py-2 pl-4 pr-4 text-sm font-medium transition-colors {{ $viewMode === 'list' ? 'bg-tsl-surface-container-lowest text-tsl-primary shadow-sm' : 'text-tsl-secondary hover:text-tsl-primary' }}"
                        >
                            <svg class="size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25a2.25 2.25 0 0 1-2.25 2.25H15.75a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25a2.25 2.25 0 0 1-2.25-2.25v-2.25Z" />
                            </svg>
                            List view
                        </a>
                        <a
                            href="{{ route('grounds.index', array_merge(request()->except('page'), ['view' => 'map'])) }}"
                            class="flex flex-1 items-center justify-center gap-2 rounded-full py-2 pl-4 pr-4 text-sm font-medium transition-colors {{ $viewMode === 'map' ? 'bg-tsl-surface-container-lowest text-tsl-primary shadow-sm' : 'text-tsl-secondary hover:text-tsl-primary' }}"
                        >
                            <svg class="size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-9v9m6-4.5v8.25a.75.75 0 0 1-.75.75H3.75a.75.75 0 0 1-.75-.75V6.75a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 .75.75Z" />
                            </svg>
                            Map view
                        </a>
                    </div>

                    {{-- Location --}}
                    <section>
                        <h3 class="mb-6 font-tsl-headline text-xl font-semibold text-tsl-primary">Location</h3>
                        <div class="space-y-4">
                            <div class="relative">
                                <label for="directory-q" class="sr-only">Postcode or town</label>
                                <input
                                    id="directory-q"
                                    name="q"
                                    type="text"
                                    value="{{ $q }}"
                                    placeholder="Postcode or town"
                                    class="w-full rounded-xl border-0 bg-tsl-surface-container-low px-4 py-3 text-sm text-tsl-on-surface placeholder:text-tsl-secondary/70 focus:ring-2 focus:ring-tsl-primary"
                                >
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach (['10' => '10 miles', '25' => '25 miles', '50' => '50+ miles'] as $miles => $label)
                                    <a
                                        href="{{ route('grounds.index', array_merge($filterQuery, ['distance' => $miles])) }}"
                                        class="cursor-pointer rounded-full px-4 py-2 text-xs font-medium transition-colors {{ $distance === $miles ? 'bg-tsl-primary-container text-tsl-on-primary-container' : 'bg-tsl-surface-container-high text-tsl-secondary hover:bg-tsl-surface-container-highest' }}"
                                    >{{ $label }}</a>
                                @endforeach
                            </div>
                        </div>
                    </section>

                    <section>
                        <h3 class="mb-6 font-tsl-headline text-xl font-semibold text-tsl-primary">Discipline</h3>
                        <div class="max-h-64 space-y-2.5 overflow-y-auto pr-1">
                            @foreach ($allDisciplines as $d)
                                <label class="group flex cursor-pointer items-start gap-3">
                                    <input
                                        type="checkbox"
                                        name="discipline[]"
                                        value="{{ $d->id }}"
                                        class="mt-0.5 rounded-sm border-tsl-outline-variant text-tsl-primary focus:ring-tsl-primary"
                                        @checked(in_array($d->id, $disciplineFilter, true))
                                        onchange="this.form.submit()"
                                    >
                                    <span class="text-sm font-medium leading-snug text-tsl-secondary transition-colors group-hover:text-tsl-primary">
                                        <span class="font-semibold text-tsl-primary">{{ $d->code }}</span>
                                        <span class="text-tsl-secondary"> — {{ $d->name }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-3 text-xs leading-relaxed text-tsl-secondary/90">Shows grounds that offer any of the disciplines selected.</p>
                    </section>

                    {{-- Facilities --}}
                    <section>
                        <h3 class="mb-6 font-tsl-headline text-xl font-semibold text-tsl-primary">Facilities</h3>
                        <div class="space-y-3">
                            <label class="group flex cursor-pointer items-center gap-3">
                                <input
                                    type="checkbox"
                                    name="facility[]"
                                    value="practice"
                                    class="rounded-sm border-tsl-outline-variant text-tsl-primary focus:ring-tsl-primary"
                                    @checked(in_array('practice', $facilities, true))
                                    onchange="this.form.submit()"
                                >
                                <span class="text-sm font-medium text-tsl-secondary transition-colors group-hover:text-tsl-primary">Practice</span>
                            </label>
                            <label class="group flex cursor-pointer items-center gap-3">
                                <input
                                    type="checkbox"
                                    name="facility[]"
                                    value="lessons"
                                    class="rounded-sm border-tsl-outline-variant text-tsl-primary focus:ring-tsl-primary"
                                    @checked(in_array('lessons', $facilities, true))
                                    onchange="this.form.submit()"
                                >
                                <span class="text-sm font-medium text-tsl-secondary transition-colors group-hover:text-tsl-primary">Expert tuition</span>
                            </label>
                            <label class="group flex cursor-pointer items-center gap-3">
                                <input
                                    type="checkbox"
                                    name="facility[]"
                                    value="competitions"
                                    class="rounded-sm border-tsl-outline-variant text-tsl-primary focus:ring-tsl-primary"
                                    @checked(in_array('competitions', $facilities, true))
                                    onchange="this.form.submit()"
                                >
                                <span class="text-sm font-medium text-tsl-secondary transition-colors group-hover:text-tsl-primary">Competitions</span>
                            </label>
                            <label class="group flex cursor-not-allowed items-center gap-3 opacity-50">
                                <input type="checkbox" disabled class="rounded-sm border-tsl-outline-variant text-tsl-primary">
                                <span class="text-sm font-medium text-tsl-secondary">Clubhouse &amp; cafe</span>
                            </label>
                        </div>
                    </section>

                    @if ($distance)
                        <input type="hidden" name="distance" value="{{ $distance }}">
                    @endif
                    @if ($hasUserGeo ?? false)
                        <input type="hidden" name="user_lat" value="{{ $userLat }}">
                        <input type="hidden" name="user_lng" value="{{ $userLng }}">
                    @endif
                    <input type="hidden" name="view" value="{{ $viewMode }}">

                    <a
                        href="{{ route('grounds.index') }}"
                        class="block w-full rounded-xl border border-tsl-outline-variant/40 py-4 text-center text-sm font-semibold text-tsl-primary transition-colors hover:bg-tsl-surface-container-low"
                    >Clear all filters</a>
                </aside>

                {{-- Results --}}
                <div class="min-w-0 flex-1">
                    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                        <p class="font-tsl-body text-sm text-tsl-secondary">
                            @if ($viewMode === 'map')
                                @if ($filteredTotal > 0)
                                    <span class="font-semibold text-tsl-primary">{{ $mapPinsCount }}</span>
                                    @if ($mapPinsCount === 1)
                                        ground
                                    @else
                                        grounds
                                    @endif
                                    on the map
                                    @if ($missingCoordsCount > 0)
                                        <span class="text-tsl-secondary">({{ $missingCoordsCount }} without coordinates)</span>
                                    @endif
                                @else
                                    <span class="font-semibold text-tsl-primary">0</span> grounds
                                @endif
                            @else
                                @if ($grounds->total() > 0)
                                    Showing
                                    <span class="font-semibold text-tsl-primary">{{ $grounds->firstItem() }}</span>–<span class="font-semibold text-tsl-primary">{{ $grounds->lastItem() }}</span>
                                    of
                                    <span class="font-semibold text-tsl-primary">{{ $grounds->total() }}</span>
                                    grounds
                                @else
                                    <span class="font-semibold text-tsl-primary">0</span> grounds
                                @endif
                            @endif
                        </p>
                        @if ($viewMode === 'list')
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold uppercase tracking-wider text-tsl-secondary">Sort by</span>
                                <label for="directory-sort" class="sr-only">Sort by</label>
                                <select
                                    id="directory-sort"
                                    name="sort"
                                    class="cursor-pointer border-0 bg-transparent text-sm font-medium text-tsl-primary focus:ring-0"
                                    onchange="this.form.submit()"
                                >
                                    <option value="distance" @selected($sort === 'distance')>Nearest first</option>
                                    <option value="az" @selected($sort === 'az')>A–Z</option>
                                    <option value="za" @selected($sort === 'za')>Z–A</option>
                                    <option value="county" @selected($sort === 'county')>County</option>
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="sort" value="{{ $sort }}">
                        @endif
                    </div>

                    @if ($viewMode === 'map')
                        <script type="application/json" id="directory-map-data">@json($mapMarkers)</script>
                        <div
                            id="directory-map"
                            class="directory-map-root isolate z-0 h-[min(70vh,560px)] min-h-[320px] w-full rounded-xl bg-tsl-surface-container ring-1 ring-tsl-outline-variant/30"
                            role="region"
                            aria-label="Shooting grounds map"
                        ></div>
                        <p class="mt-3 text-xs text-tsl-secondary">
                            Map data © <a href="https://www.openstreetmap.org/copyright" class="underline decoration-tsl-outline hover:text-tsl-primary" target="_blank" rel="noopener noreferrer">OpenStreetMap</a> contributors. Click a pin for details.
                        </p>
                    @elseif ($grounds->isEmpty())
                        <p class="rounded-xl border border-dashed border-tsl-outline-variant bg-tsl-surface-container-lowest p-10 text-center text-tsl-secondary">
                            No grounds match your search.
                        </p>
                    @else
                        <ul class="grid grid-cols-1 gap-8 md:grid-cols-2 xl:grid-cols-3">
                            @foreach ($grounds as $ground)
                                <li>
                                    <a
                                        href="{{ route('grounds.show', $ground) }}"
                                        class="group flex h-full flex-col overflow-hidden rounded-xl bg-tsl-surface-container-lowest transition-all duration-500 hover:shadow-[0_40px_40px_-15px_rgba(28,28,25,0.06)]"
                                    >
                                        <div class="relative aspect-[4/3] overflow-hidden">
                                            <img
                                                src="{{ $ground->coverPhotoUrl() }}"
                                                alt="{{ $ground->name }}"
                                                class="size-full object-cover transition-transform duration-700 group-hover:scale-105"
                                                loading="lazy"
                                            >
                                            @if ($loop->first && $grounds->currentPage() === 1)
                                                <div class="absolute top-4 right-4 rounded-full bg-tsl-surface/90 px-3 py-1 text-[10px] font-bold tracking-widest text-tsl-tertiary uppercase">Featured</div>
                                            @endif
                                        </div>
                                        <div class="flex flex-1 flex-col p-6">
                                            <div class="mb-3 flex items-center gap-2">
                                                <span class="size-1.5 shrink-0 rounded-full bg-tsl-tertiary"></span>
                                                <p class="font-tsl-body text-[11px] font-bold tracking-widest text-tsl-secondary uppercase">
                                                    {{ $ground->county ?: 'United Kingdom' }}
                                                </p>
                                            </div>
                                            <h2 class="mb-2 font-tsl-headline text-2xl font-bold text-tsl-primary transition-colors group-hover:text-tsl-tertiary">{{ $ground->name }}</h2>
                                            @if ($ground->disciplines->isNotEmpty())
                                                <p class="mb-2 flex flex-wrap gap-1.5">
                                                    @foreach ($ground->disciplines->take(6) as $disc)
                                                        <span class="rounded-md bg-tsl-surface-container-high px-2 py-0.5 font-mono text-[10px] font-semibold uppercase tracking-wide text-tsl-primary">{{ $disc->code }}</span>
                                                    @endforeach
                                                    @if ($ground->disciplines->count() > 6)
                                                        <span class="self-center text-[10px] font-medium text-tsl-secondary">+{{ $ground->disciplines->count() - 6 }}</span>
                                                    @endif
                                                </p>
                                            @endif
                                            @if ($ground->facilities->isNotEmpty())
                                                <p class="mb-3 line-clamp-2 text-[11px] leading-snug text-tsl-secondary">
                                                    @foreach ($ground->facilities->take(4) as $facility)
                                                        <span class="mr-1.5 inline-block rounded border border-tsl-outline-variant/50 bg-tsl-surface-container-low px-1.5 py-0.5 text-tsl-on-surface">{{ $facility->name }}</span>
                                                    @endforeach
                                                    @if ($ground->facilities->count() > 4)
                                                        <span class="text-tsl-secondary">+{{ $ground->facilities->count() - 4 }} more</span>
                                                    @endif
                                                </p>
                                            @endif
                                            <p class="mb-6 line-clamp-2 flex-1 font-tsl-body text-sm leading-relaxed text-tsl-secondary">
                                                {{ $ground->description ? \Illuminate\Support\Str::limit(strip_tags($ground->description), 140) : 'Shooting ground in the UK — view opening times, disciplines, and booking on the detail page.' }}
                                            </p>
                                            <div class="mt-auto flex flex-wrap items-center justify-between gap-2">
                                                <span class="text-xs text-tsl-secondary">
                                                    @if ($hasUserGeo ?? false)
                                                        @if ($ground->latitude && $ground->longitude)
                                                            @php
                                                                $mi = \App\Support\Geo::haversineMiles($userLat, $userLng, (float) $ground->latitude, (float) $ground->longitude);
                                                            @endphp
                                                            <span class="font-medium text-tsl-primary">{{ $mi < 10 ? number_format($mi, 1) : number_format($mi, 0) }} mi</span>
                                                            <span class="text-tsl-outline" aria-hidden="true">·</span>
                                                        @else
                                                            <span class="text-tsl-secondary/80">Distance n/a</span>
                                                            <span class="text-tsl-outline" aria-hidden="true">·</span>
                                                        @endif
                                                    @endif
                                                    @if ($ground->city)
                                                        {{ $ground->city }}
                                                    @endif
                                                    @if ($ground->postcode)
                                                        @if ($ground->city) · @endif{{ $ground->postcode }}
                                                    @endif
                                                </span>
                                                <span class="leather-underline font-tsl-body text-sm font-semibold text-tsl-tertiary">View details</span>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-16 flex justify-center sm:mt-20">
                            {{ $grounds->links('pagination::directory') }}
                        </div>
                    @endif
                </div>
            </form>
        </main>
    </div>
@endsection
