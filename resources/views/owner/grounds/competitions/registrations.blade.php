@extends('layouts.app')

@section('title', __('Entries').' — '.$competition->title)

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:py-10">
        <nav class="mb-6 text-sm">
            <a href="{{ route('account.competitions.index', ['ground' => $ground->slug]) }}" class="font-medium text-forest hover:text-forest-light">← {{ __('My competitions') }}</a>
        </nav>

        @include('account._tabs', ['active' => 'competitions'])

        <h1 class="font-serif text-2xl font-semibold text-forest">{{ __('Registrations') }}</h1>
        <p class="mt-1 text-sm text-stone-600">{{ $competition->title }}</p>

        @if ($registrations->isEmpty())
            <p class="mt-10 text-stone-600">{{ __('No registrations yet.') }}</p>
        @else
            <div class="mt-8 overflow-x-auto rounded-xl border border-stone-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-stone-200 text-left text-sm">
                    <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-wide text-stone-600">
                        <tr>
                            <th class="px-4 py-3">{{ __('Registered') }}</th>
                            <th class="px-4 py-3">{{ __('Name') }}</th>
                            <th class="px-4 py-3">{{ __('CPSA') }}</th>
                            <th class="px-4 py-3">{{ __('Email') }}</th>
                            <th class="px-4 py-3">{{ __('Telephone') }}</th>
                            <th class="px-4 py-3">{{ __('Squad') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 text-stone-800">
                        @foreach ($registrations as $r)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 text-stone-600">{{ $r->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3 font-medium">{{ $r->entrant_name }}</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $r->cpsa_number }}</td>
                                <td class="px-4 py-3"><a href="mailto:{{ $r->email }}" class="text-forest underline decoration-forest/30 underline-offset-2">{{ $r->email }}</a></td>
                                <td class="px-4 py-3">{{ $r->telephone }}</td>
                                <td class="px-4 py-3">
                                    @if ($r->squad)
                                        {{ $r->squad->label() }} · {{ $r->squad->starts_at->timezone('Europe/London')->format('D j M, g:ia') }}
                                    @else
                                        <span class="text-stone-500">{{ __('Open') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
