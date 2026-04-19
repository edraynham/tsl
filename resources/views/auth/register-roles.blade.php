@extends('layouts.app')

@section('title', 'How you’ll use '.config('app.name'))

@section('content')
    <div class="mx-auto max-w-md px-4 py-16 sm:px-6">
        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-stone-500">Step 2 of 2</p>
        <h1 class="mt-2 font-serif text-3xl font-semibold text-forest">How will you use {{ config('app.name') }}?</h1>
        <p class="mt-2 text-sm text-stone-600">
            Choose one or both. You can change this later in your account settings when that’s available.
        </p>

        @if (session('status'))
            <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">
                {{ session('status') }}
            </div>
        @endif

        <form method="post" action="{{ route('register.roles.store') }}" class="mt-8 space-y-5">
            @csrf

            <div class="rounded-xl border border-stone-200 bg-stone-50/80 px-4 py-4">
                <p class="text-sm font-medium text-stone-800">I am a… <span class="text-red-600">*</span></p>
                <p class="mt-1 text-xs text-stone-500">Select at least one.</p>
                <div class="mt-4 space-y-3">
                    <label class="flex cursor-pointer items-start gap-3 text-sm">
                        <input
                            type="checkbox"
                            name="roles[]"
                            value="shooter"
                            class="mt-1 rounded border-stone-300 text-forest focus:ring-forest"
                            @checked(in_array('shooter', old('roles', []), true))
                        >
                        <span><span class="font-medium text-stone-800">Shooter</span> — find grounds, competitions, and plan shooting days.</span>
                    </label>
                    <label class="flex cursor-pointer items-start gap-3 text-sm">
                        <input
                            type="checkbox"
                            name="roles[]"
                            value="organiser"
                            class="mt-1 rounded border-stone-300 text-forest focus:ring-forest"
                            @checked(in_array('organiser', old('roles', []), true))
                        >
                        <span><span class="font-medium text-stone-800">Ground owner / shoot organiser</span> — list and manage your ground or events (where applicable).</span>
                    </label>
                </div>
                @error('roles')
                    <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="w-full rounded-xl bg-forest py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-light"
            >
                Continue
            </button>
        </form>
    </div>
@endsection
