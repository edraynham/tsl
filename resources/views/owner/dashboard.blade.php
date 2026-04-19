@extends('layouts.app')

@section('title', 'My grounds — '.config('app.name'))

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
        <h1 class="font-serif text-3xl font-semibold text-forest">My grounds</h1>
        <p class="mt-2 text-stone-600">
            Edit your public profile, opening hours, and competitions.
        </p>

        @if ($grounds->isEmpty())
            <div class="mt-10 rounded-2xl border border-dashed border-stone-300 bg-white/60 px-6 py-12 text-center">
                <p class="text-stone-700">You don’t manage any grounds yet.</p>
                <p class="mt-2 text-sm text-stone-500">Ask an administrator to link your account to a ground.</p>
            </div>
        @else
            <ul class="mt-8 space-y-3">
                @foreach ($grounds as $g)
                    <li>
                        <a
                            href="{{ route('owner.grounds.edit', $g) }}"
                            class="flex items-center justify-between rounded-2xl border border-stone-200 bg-white px-5 py-4 shadow-sm transition hover:border-stone-300 hover:shadow"
                        >
                            <span class="font-medium text-forest">{{ $g->name }}</span>
                            <span class="text-sm text-stone-500">Edit →</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
