@extends('layouts.app')

@php
    $editing = $competition->exists;
@endphp

@section('title', ($editing ? 'Edit' : 'Add').' competition — '.$ground->name)

@section('content')
    <div class="mx-auto max-w-2xl px-4 py-8 sm:px-6 lg:py-10">
        <nav class="mb-6 text-sm">
            <a href="{{ route('owner.grounds.competitions.index', $ground) }}" class="font-medium text-forest hover:text-forest-light">← Competitions</a>
        </nav>

        <h1 class="font-serif text-2xl font-semibold text-forest">{{ $editing ? 'Edit competition' : 'New competition' }}</h1>
        <p class="mt-1 text-sm text-stone-600">{{ $ground->name }}</p>

        <form
            method="post"
            action="{{ $editing ? route('owner.grounds.competitions.update', [$ground, $competition]) : route('owner.grounds.competitions.store', $ground) }}"
            class="mt-8 space-y-5"
        >
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div>
                <label class="block text-sm font-medium text-stone-700" for="title">Title</label>
                <input
                    type="text"
                    name="title"
                    id="title"
                    value="{{ old('title', $competition->title) }}"
                    required
                    class="mt-1 w-full rounded-xl border border-stone-200 px-4 py-2.5 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700" for="slug">Slug (optional)</label>
                <input
                    type="text"
                    name="slug"
                    id="slug"
                    value="{{ old('slug', $competition->slug) }}"
                    class="mt-1 w-full rounded-xl border border-stone-200 px-4 py-2.5 font-mono text-sm text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                >
                <p class="mt-1 text-xs text-stone-500">Leave blank to generate from the title.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700" for="summary">Summary</label>
                <textarea
                    name="summary"
                    id="summary"
                    rows="4"
                    class="mt-1 w-full rounded-xl border border-stone-200 px-4 py-2.5 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                >{{ old('summary', $competition->summary) }}</textarea>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-stone-700" for="starts_at">Starts</label>
                    <input
                        type="datetime-local"
                        name="starts_at"
                        id="starts_at"
                        value="{{ old('starts_at', $competition->starts_at?->format('Y-m-d\TH:i')) }}"
                        required
                        class="mt-1 w-full rounded-xl border border-stone-200 px-4 py-2.5 font-mono text-sm text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700" for="ends_at">Ends (optional)</label>
                    <input
                        type="datetime-local"
                        name="ends_at"
                        id="ends_at"
                        value="{{ old('ends_at', $competition->ends_at?->format('Y-m-d\TH:i')) }}"
                        class="mt-1 w-full rounded-xl border border-stone-200 px-4 py-2.5 font-mono text-sm text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                    >
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700" for="discipline_id">Discipline</label>
                <select
                    name="discipline_id"
                    id="discipline_id"
                    class="mt-1 w-full rounded-xl border border-stone-200 px-4 py-2.5 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                >
                    <option value="">— None —</option>
                    @foreach ($disciplines as $d)
                        <option value="{{ $d->id }}" @selected((int) old('discipline_id', $competition->discipline_id) === $d->id)>
                            {{ $d->name }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-stone-500">Optional. Shown on the public calendar and used for event search.</p>
            </div>

            <div class="flex items-start gap-3 rounded-xl border border-stone-200 bg-cream-dark/30 px-4 py-3">
                <input
                    type="checkbox"
                    name="cpsa_registered"
                    id="cpsa_registered"
                    value="1"
                    @checked(old('cpsa_registered', $competition->cpsa_registered))
                    class="mt-1 size-4 rounded border-stone-300 text-forest focus:ring-forest"
                >
                <div>
                    <label class="text-sm font-medium text-stone-800" for="cpsa_registered">CPSA registered</label>
                    <p class="mt-0.5 text-xs text-stone-600">Tick if this is an official CPSA registered competition.</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700" for="external_url">More info URL</label>
                <input
                    type="url"
                    name="external_url"
                    id="external_url"
                    value="{{ old('external_url', $competition->external_url) }}"
                    class="mt-1 w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                >
            </div>

            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <button type="submit" class="rounded-xl bg-forest px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-light">
                {{ $editing ? 'Save changes' : 'Create competition' }}
            </button>
        </form>
    </div>
@endsection
