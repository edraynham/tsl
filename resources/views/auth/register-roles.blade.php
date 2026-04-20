@extends('layouts.app')

@section('title', 'How you’ll use '.config('app.name'))

@section('content')
    <div class="mx-auto max-w-xl px-4 py-16 sm:px-6">
        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-stone-500">Step 2 of 2</p>
        <h1 class="mt-2 font-serif text-3xl font-semibold text-forest">How will you use {{ config('app.name') }}?</h1>
        <p class="mt-2 text-sm text-stone-600">
            Choose one or more. You can change this later in your account settings when that’s available.
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
                <div class="mt-4 grid gap-3">
                    <label class="group block cursor-pointer rounded-xl border border-stone-100 bg-white p-4 shadow-sm transition hover:border-forest/30 hover:bg-forest/[0.03] has-[:checked]:border-forest has-[:checked]:bg-emerald-100/90">
                        <span class="flex items-start gap-3">
                        <input
                            type="checkbox"
                            name="roles[]"
                            value="shooter"
                            class="mt-1 size-5 rounded border-stone-300 text-forest focus:ring-forest"
                            @checked(in_array('shooter', old('roles', []), true))
                        >
                            <span class="min-w-0">
                                <span class="flex items-center gap-2">
                                    <span class="inline-flex size-7 items-center justify-center rounded-full bg-forest/10 text-forest">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path d="M3.5 3.75A2.25 2.25 0 0 1 5.75 1.5h8.5a2.25 2.25 0 0 1 2.25 2.25v12.5a.75.75 0 0 1-1.138.643L10 13.74l-5.362 3.153A.75.75 0 0 1 3.5 16.25V3.75Z" />
                                        </svg>
                                    </span>
                                    <span class="text-base font-semibold text-stone-900">Shooter</span>
                                    <span class="rounded-full bg-stone-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-stone-600 transition group-has-[:checked]:bg-forest/10 group-has-[:checked]:text-forest">Explore</span>
                                </span>
                                <span class="mt-1 block text-sm leading-relaxed text-stone-600">Discover great grounds, browse competitions, and plan your next shooting day.</span>
                            </span>
                        </span>
                    </label>
                    <label class="group block cursor-pointer rounded-xl border border-stone-100 bg-white p-4 shadow-sm transition hover:border-forest/30 hover:bg-forest/[0.03] has-[:checked]:border-forest has-[:checked]:bg-emerald-100/90">
                        <span class="flex items-start gap-3">
                        <input
                            type="checkbox"
                            name="roles[]"
                            value="organiser"
                            class="mt-1 size-5 rounded border-stone-300 text-forest focus:ring-forest"
                            @checked(in_array('organiser', old('roles', []), true))
                        >
                            <span class="min-w-0">
                                <span class="flex items-center gap-2">
                                    <span class="inline-flex size-7 items-center justify-center rounded-full bg-forest/10 text-forest">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M9.664 1.319a.75.75 0 0 1 .672 0l7.25 3.5a.75.75 0 0 1 0 1.362l-7.25 3.5a.75.75 0 0 1-.672 0l-7.25-3.5a.75.75 0 0 1 0-1.362l7.25-3.5ZM3.5 8.736l6.164 2.975a.75.75 0 0 0 .672 0L16.5 8.736V14a.75.75 0 0 1-.414.671l-5.75 2.875a.75.75 0 0 1-.672 0l-5.75-2.875A.75.75 0 0 1 3.5 14V8.736Z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                    <span class="text-base font-semibold text-stone-900">Ground owner / shoot organiser</span>
                                    <span class="rounded-full bg-stone-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-stone-600 transition group-has-[:checked]:bg-forest/10 group-has-[:checked]:text-forest">Manage</span>
                                </span>
                                <span class="mt-1 block text-sm leading-relaxed text-stone-600">Showcase your ground, publish details, and keep events and facilities up to date.</span>
                            </span>
                        </span>
                    </label>
                    <label class="group block cursor-pointer rounded-xl border border-stone-100 bg-white p-4 shadow-sm transition hover:border-forest/30 hover:bg-forest/[0.03] has-[:checked]:border-forest has-[:checked]:bg-emerald-100/90">
                        <span class="flex items-start gap-3">
                        <input
                            type="checkbox"
                            name="roles[]"
                            value="instructor"
                            class="mt-1 size-5 rounded border-stone-300 text-forest focus:ring-forest"
                            @checked(in_array('instructor', old('roles', []), true))
                        >
                            <span class="min-w-0">
                                <span class="flex items-center gap-2">
                                    <span class="inline-flex size-7 items-center justify-center rounded-full bg-forest/10 text-forest">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path d="M10 2.5a3 3 0 0 0-3 3v1.086a4.5 4.5 0 1 0 6 0V5.5a3 3 0 0 0-3-3Zm1.5 9.75a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                                            <path d="M2.75 12.5a7.25 7.25 0 1 1 14.5 0v2.25a.75.75 0 0 1-.75.75H3.5a.75.75 0 0 1-.75-.75V12.5Z" />
                                        </svg>
                                    </span>
                                    <span class="text-base font-semibold text-stone-900">Instructor</span>
                                    <span class="rounded-full bg-stone-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-stone-600 transition group-has-[:checked]:bg-forest/10 group-has-[:checked]:text-forest">Coach</span>
                                </span>
                                <span class="mt-1 block text-sm leading-relaxed text-stone-600">Build your coaching profile and help shooters improve technique and confidence.</span>
                            </span>
                        </span>
                    </label>
                </div>
                @error('roles')
                    <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-forest py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-light"
            >
                Continue
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L11.22 6.03a.75.75 0 1 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 1 1-1.06-1.06l3.22-3.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" />
                </svg>
            </button>
        </form>
    </div>
@endsection
