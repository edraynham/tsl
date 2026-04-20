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

            <section class="mt-14 border-t border-stone-200/90 pt-12" aria-labelledby="instructor-contact-heading">
                <h2 id="instructor-contact-heading" class="font-serif text-2xl font-semibold text-forest">
                    Send a message
                </h2>
                <p class="mt-2 text-sm leading-relaxed text-stone-600">
                    Your message goes to {{ config('app.name') }}. We’ll forward it to the right person and reply by email.
                </p>

                @if (session('status'))
                    <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="post" action="{{ route('contact.store') }}" class="mt-8 space-y-5">
                    @csrf
                    <input type="hidden" name="instructor_slug" value="{{ $instructor->slug }}">
                    <div>
                        <label for="instructor-contact-name" class="block text-sm font-medium text-stone-700">Name</label>
                        <input
                            type="text"
                            name="name"
                            id="instructor-contact-name"
                            value="{{ old('name') }}"
                            required
                            autocomplete="name"
                            class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                        >
                        @error('name')
                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="instructor-contact-email" class="block text-sm font-medium text-stone-700">Email</label>
                        <input
                            type="email"
                            name="email"
                            id="instructor-contact-email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="instructor-contact-phone" class="block text-sm font-medium text-stone-700">Phone <span class="font-normal text-stone-500">(optional)</span></label>
                        <input
                            type="tel"
                            name="phone"
                            id="instructor-contact-phone"
                            value="{{ old('phone') }}"
                            autocomplete="tel"
                            class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                        >
                        @error('phone')
                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="instructor-contact-skill-level" class="block text-sm font-medium text-stone-700">Skill level</label>
                        <select
                            name="skill_level"
                            id="instructor-contact-skill-level"
                            required
                            class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                        >
                            <option value="" disabled @selected(old('skill_level') === null || old('skill_level') === '')>Select skill level</option>
                            <option value="beginner" @selected(old('skill_level') === 'beginner')>Beginner</option>
                            <option value="intermediate" @selected(old('skill_level') === 'intermediate')>Intermediate</option>
                            <option value="advanced" @selected(old('skill_level') === 'advanced')>Advanced</option>
                        </select>
                        @error('skill_level')
                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="instructor-contact-message" class="block text-sm font-medium text-stone-700">Message</label>
                        <textarea
                            name="message"
                            id="instructor-contact-message"
                            rows="6"
                            required
                            class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                        >{{ old('message') }}</textarea>
                        @error('message')
                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                    <button
                        type="submit"
                        class="w-full rounded-xl bg-forest px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-light sm:w-auto"
                    >
                        Send message
                    </button>
                </form>
            </section>
        </div>
    </article>
@endsection
