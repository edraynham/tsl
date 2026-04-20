@extends('layouts.app')

@section('title', 'Competitions — '.$ground->name)

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:py-10">
        <nav class="mb-6 text-sm">
            <a href="{{ route('owner.dashboard') }}" class="font-medium text-forest hover:text-forest-light">← My grounds</a>
        </nav>

        @include('account._tabs', ['active' => 'grounds'])

        @include('owner.grounds._subnav', ['ground' => $ground])

        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="font-serif text-2xl font-semibold text-forest">Competitions</h1>
                <p class="mt-1 text-sm text-stone-600">{{ $ground->name }}</p>
            </div>
            <a
                href="{{ route('owner.grounds.competitions.create', $ground) }}"
                class="inline-flex items-center justify-center rounded-xl bg-forest px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-light"
            >
                Add competition
            </a>
        </div>

        @if (session('status'))
            <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('status') }}</div>
        @endif

        @if ($competitions->isEmpty())
            <p class="mt-10 text-stone-600">No competitions yet. Add your first event.</p>
        @else
            <ul class="mt-8 divide-y divide-stone-200 rounded-2xl border border-stone-200 bg-white">
                @foreach ($competitions as $c)
                    <li class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-medium text-forest">{{ $c->title }}</p>
                            <p class="mt-1 text-sm text-stone-600">
                                {{ $c->starts_at->format('D j M Y, g:ia') }}
                                @if ($c->disciplineDisplay())
                                    <span class="text-stone-400">·</span> {{ $c->disciplineDisplay() }}
                                @endif
                                <span class="text-stone-400">·</span>
                                @if ($c->cpsa_registered)
                                    <span class="font-medium text-amber-900">CPSA registered</span>
                                @else
                                    <span class="text-stone-500">Not CPSA registered</span>
                                @endif
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a
                                href="{{ route('competitions.show', $c) }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="rounded-lg border border-stone-200 px-3 py-1.5 text-sm font-medium text-stone-700 hover:bg-stone-50"
                            >
                                View public
                            </a>
                            <a
                                href="{{ route('owner.grounds.competitions.edit', [$ground, $c]) }}"
                                class="rounded-lg border border-stone-200 px-3 py-1.5 text-sm font-medium text-forest hover:bg-stone-50"
                            >
                                Edit
                            </a>
                            <form
                                method="post"
                                action="{{ route('owner.grounds.competitions.destroy', [$ground, $c]) }}"
                                onsubmit="return confirm('Delete this competition?');"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-sm font-medium text-red-800 hover:bg-red-50">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
