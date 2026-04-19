@extends('layouts.app')

@section('title', 'Instructors — '.config('app.name'))

@section('content')
    <div class="relative overflow-hidden bg-cream">
        <div class="pointer-events-none absolute -left-32 top-20 h-96 w-96 rounded-full bg-forest/[0.04] blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -right-24 bottom-40 h-80 w-80 rounded-full bg-amber-200/25 blur-3xl" aria-hidden="true"></div>

        <div class="relative mx-auto max-w-6xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
            <header class="mx-auto max-w-3xl text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Coaching &amp; tuition</p>
                <h1 class="mt-3 font-serif text-4xl font-semibold tracking-tight text-forest sm:text-5xl">
                    Instructors
                </h1>
                <p class="mt-4 text-lg leading-relaxed text-stone-600">
                    Qualified coaches and instructors offering lessons and development — find someone near you and follow through for contact details.
                </p>
                @if ($instructors->isNotEmpty())
                    <p class="mt-6 inline-flex items-center gap-2 rounded-full border border-stone-200/90 bg-white/80 px-4 py-2 text-sm text-stone-600 shadow-sm backdrop-blur-sm">
                        <span class="font-semibold tabular-nums text-forest">{{ $instructors->count() }}</span>
                        <span>{{ \Illuminate\Support\Str::plural('instructor', $instructors->count()) }} listed</span>
                    </p>
                @endif
            </header>

            @if ($instructors->isEmpty())
                <p class="mx-auto mt-14 max-w-lg text-center text-stone-600">
                    No instructors are listed yet. Check back soon.
                </p>
            @else
                <ul class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($instructors as $instructor)
                        <li>
                            <a
                                href="{{ route('instructors.show', $instructor) }}"
                                class="group flex h-full flex-col overflow-hidden rounded-2xl border border-stone-200/90 bg-white shadow-sm transition hover:border-forest/25 hover:shadow-md"
                            >
                                <div class="aspect-[4/3] bg-stone-100">
                                    @if ($instructor->photo_url)
                                        <img
                                            src="{{ $instructor->photo_url }}"
                                            alt=""
                                            class="size-full object-cover transition group-hover:scale-[1.02]"
                                            loading="lazy"
                                        >
                                    @else
                                        <div class="flex size-full items-center justify-center bg-gradient-to-br from-forest/10 to-stone-100">
                                            <span class="font-serif text-3xl font-semibold text-forest/40">{{ \Illuminate\Support\Str::substr($instructor->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex flex-1 flex-col p-5">
                                    <h2 class="font-serif text-xl font-semibold text-forest group-hover:text-forest-light">
                                        {{ $instructor->name }}
                                    </h2>
                                    @if ($instructor->headline)
                                        <p class="mt-1 text-sm font-medium text-forest-muted">{{ $instructor->headline }}</p>
                                    @endif
                                    @if ($instructor->locationLabel() !== '')
                                        <p class="mt-3 text-sm text-stone-600">{{ $instructor->locationLabel() }}</p>
                                    @endif
                                    <p class="mt-4 line-clamp-3 flex-1 text-sm leading-relaxed text-stone-600">
                                        {{ $instructor->bio ? \Illuminate\Support\Str::limit(strip_tags($instructor->bio), 160) : 'View profile' }}
                                    </p>
                                    <span class="mt-4 text-sm font-semibold text-forest">View profile <span aria-hidden="true">→</span></span>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection
