@extends('layouts.app')

@section('title', $instructor->name.' — Instructors — '.config('app.name'))

@section('content')
    <article class="border-b border-stone-200/90 bg-cream">
        <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
            <nav class="text-sm">
                <a href="{{ route('instructors.index') }}" class="font-medium text-forest underline decoration-forest/30 underline-offset-4 transition hover:text-forest-light">← Instructors</a>
            </nav>

            <header class="mt-8">
                @if ($instructor->photo_url)
                    <div class="overflow-hidden rounded-2xl border border-stone-200/90 bg-stone-100 shadow-sm">
                        <img src="{{ $instructor->photo_url }}" alt="" class="aspect-[16/9] w-full object-cover sm:aspect-[2/1]">
                    </div>
                @endif
                <h1 class="mt-8 font-serif text-3xl font-semibold tracking-tight text-forest sm:text-4xl">
                    {{ $instructor->name }}
                </h1>
                @if ($instructor->headline)
                    <p class="mt-2 text-lg text-forest-muted">{{ $instructor->headline }}</p>
                @endif
                @if ($instructor->locationLabel() !== '')
                    <p class="mt-4 text-sm text-stone-600">{{ $instructor->locationLabel() }}</p>
                @endif
                @if ($instructor->website)
                    <p class="mt-4">
                        <a
                            href="{{ $instructor->website }}"
                            class="inline-flex items-center gap-1.5 text-sm font-semibold text-forest underline decoration-forest/30 underline-offset-4 transition hover:text-forest-light"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Website
                            <span aria-hidden="true">↗</span>
                        </a>
                    </p>
                @endif
            </header>

            @if ($instructor->bio)
                <div class="mt-10 space-y-4 text-base leading-relaxed text-stone-700">
                    @foreach (explode("\n\n", trim($instructor->bio)) as $para)
                        @if (trim($para) !== '')
                            <p>{{ trim($para) }}</p>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </article>
@endsection
