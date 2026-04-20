@extends('layouts.app')

@section('title', __('Competitions').' — '.config('app.name'))

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:py-10">
        <nav class="mb-6 text-sm">
            <a href="{{ route('owner.dashboard') }}" class="font-medium text-forest hover:text-forest-light">← {{ __('My grounds') }}</a>
        </nav>

        @include('account._tabs', ['active' => 'competitions'])

        <h1 class="font-serif text-2xl font-semibold text-forest">{{ __('Competitions') }}</h1>
        <p class="mt-1 text-sm text-stone-600">{{ __('Manage events for the grounds you list on the site.') }}</p>

        @if (session('status'))
            <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('status') }}</div>
        @endif

        @if ($grounds->isEmpty())
            <div class="mt-10 rounded-2xl border border-dashed border-stone-300 bg-white/60 px-6 py-12 text-center">
                <p class="text-stone-700">{{ __('You don’t manage any grounds yet.') }}</p>
                <p class="mt-2 text-sm text-stone-500">{{ __('Ask an administrator to link your account to a ground before you can add competitions.') }}</p>
            </div>
        @else
            <div class="mt-10 space-y-12">
                @foreach ($grounds as $ground)
                    <section
                        id="ground-{{ $ground->slug }}"
                        class="scroll-mt-8 rounded-2xl border border-stone-200/90 bg-white p-5 shadow-sm sm:p-6"
                    >
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <h2 class="font-serif text-xl font-semibold text-forest">{{ $ground->name }}</h2>
                                <p class="mt-1 text-sm text-stone-600">
                                    <a href="{{ route('owner.grounds.edit', $ground) }}" class="font-medium text-forest underline decoration-forest/30 underline-offset-2 hover:text-forest-light">{{ __('Ground profile') }}</a>
                                </p>
                            </div>
                            <a
                                href="{{ route('account.competitions.create', ['ground' => $ground->slug]) }}"
                                class="inline-flex shrink-0 items-center justify-center rounded-xl bg-forest px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-light"
                            >
                                {{ __('Add competition') }}
                            </a>
                        </div>

                        @if ($ground->competitions->isEmpty())
                            <p class="mt-6 text-sm text-stone-600">{{ __('No competitions yet for this ground.') }}</p>
                        @else
                            <ul class="mt-6 divide-y divide-stone-200 rounded-xl border border-stone-200 bg-cream-dark/20">
                                @foreach ($ground->competitions as $c)
                                    <li class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                                        <div>
                                            <p class="font-medium text-forest">{{ $c->title }}</p>
                                            <p class="mt-1 text-sm text-stone-600">
                                                {{ $c->starts_at->format('D j M Y, g:ia') }}
                                                @if ($c->disciplineDisplay())
                                                    <span class="text-stone-400">·</span> {{ $c->disciplineDisplay() }}
                                                @endif
                                                <span class="text-stone-400">·</span>
                                                @if ($c->cpsa_registered)
                                                    <span class="font-medium text-amber-900">{{ __('CPSA registered') }}</span>
                                                @else
                                                    <span class="text-stone-500">{{ __('Not CPSA registered') }}</span>
                                                @endif
                                            </p>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <a
                                                href="{{ route('competitions.show', $c) }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="rounded-lg border border-stone-200 bg-white px-3 py-1.5 text-sm font-medium text-stone-700 hover:bg-stone-50"
                                            >
                                                {{ __('View public') }}
                                            </a>
                                            <a
                                                href="{{ route('account.competitions.registrations.index', $c) }}"
                                                class="rounded-lg border border-stone-200 bg-white px-3 py-1.5 text-sm font-medium text-stone-700 hover:bg-stone-50"
                                            >
                                                {{ __('Entries') }}
                                            </a>
                                            <a
                                                href="{{ route('account.competitions.squads.edit', $c) }}"
                                                class="rounded-lg border border-stone-200 bg-white px-3 py-1.5 text-sm font-medium text-stone-700 hover:bg-stone-50"
                                            >
                                                {{ __('Squads') }}
                                            </a>
                                            <a
                                                href="{{ route('account.competitions.edit', $c) }}"
                                                class="rounded-lg border border-stone-200 bg-white px-3 py-1.5 text-sm font-medium text-forest hover:bg-stone-50"
                                            >
                                                {{ __('Edit') }}
                                            </a>
                                            <form
                                                method="post"
                                                action="{{ route('account.competitions.destroy', $c) }}"
                                                onsubmit="return confirm({{ json_encode(__('Delete this competition?')) }});"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-sm font-medium text-red-800 hover:bg-red-50">
                                                    {{ __('Delete') }}
                                                </button>
                                            </form>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </section>
                @endforeach
            </div>
        @endif

        @if ($highlightGroundSlug)
            <script>
                document.getElementById('ground-{{ $highlightGroundSlug }}')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            </script>
        @endif
    </div>
@endsection
