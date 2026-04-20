@extends('layouts.app')

@section('title', 'Edit instructor profile — '.config('app.name'))

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:py-10">
        <h1 class="font-serif text-3xl font-semibold text-forest">My account</h1>
        <p class="mt-2 text-sm text-stone-600">
            Signed in as <span class="font-medium text-stone-800">{{ auth()->user()->name }}</span>
        </p>

        @include('account._tabs', ['active' => 'instructor'])

        <nav class="mb-6 text-sm">
            <a href="{{ route('account.instructor') }}" class="font-medium text-forest hover:text-forest-light">← My instructor profile</a>
        </nav>

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900" role="alert">
                <p class="font-medium">{{ __('Please fix the errors below.') }}</p>
            </div>
        @endif

        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="font-serif text-xl font-semibold text-forest">Edit public profile</h2>
            <p class="mt-1 text-sm text-stone-600">
                Changes appear on your <a href="{{ route('instructors.show', $instructor) }}" class="font-medium text-forest underline decoration-forest/30 underline-offset-2 hover:text-forest-light">directory listing</a>.
            </p>

            <form method="post" action="{{ route('account.instructor.update') }}" class="mt-8 space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="instructor-edit-name" class="block text-sm font-medium text-stone-700">Name</label>
                    <input
                        type="text"
                        name="name"
                        id="instructor-edit-name"
                        value="{{ old('name', $instructor->name) }}"
                        required
                        autocomplete="name"
                        class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="instructor-edit-slug" class="block text-sm font-medium text-stone-700">URL slug</label>
                    <input
                        type="text"
                        name="slug"
                        id="instructor-edit-slug"
                        value="{{ old('slug', $instructor->slug) }}"
                        required
                        pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
                        title="{{ __('Lowercase letters, numbers, and hyphens only') }}"
                        class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-3 font-mono text-sm text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                    >
                    <p class="mt-1 text-xs text-stone-500">
                        Public URL: {{ url('/instructors/') }}<span id="instructor-slug-preview">{{ old('slug', $instructor->slug) }}</span>
                    </p>
                    @error('slug')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="instructor-edit-headline" class="block text-sm font-medium text-stone-700">Headline <span class="font-normal text-stone-500">(optional)</span></label>
                    <input
                        type="text"
                        name="headline"
                        id="instructor-edit-headline"
                        value="{{ old('headline', $instructor->headline) }}"
                        maxlength="255"
                        class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                    >
                    @error('headline')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="instructor-edit-bio" class="block text-sm font-medium text-stone-700">Bio <span class="font-normal text-stone-500">(optional)</span></label>
                    <textarea
                        name="bio"
                        id="instructor-edit-bio"
                        rows="8"
                        class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                    >{{ old('bio', $instructor->bio) }}</textarea>
                    @error('bio')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="instructor-edit-city" class="block text-sm font-medium text-stone-700">Town / city <span class="font-normal text-stone-500">(optional)</span></label>
                        <input
                            type="text"
                            name="city"
                            id="instructor-edit-city"
                            value="{{ old('city', $instructor->city) }}"
                            class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                        >
                        @error('city')
                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="instructor-edit-county" class="block text-sm font-medium text-stone-700">County <span class="font-normal text-stone-500">(optional)</span></label>
                        <input
                            type="text"
                            name="county"
                            id="instructor-edit-county"
                            value="{{ old('county', $instructor->county) }}"
                            class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                        >
                        @error('county')
                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="instructor-edit-photo-url" class="block text-sm font-medium text-stone-700">Photo URL <span class="font-normal text-stone-500">(optional)</span></label>
                    <input
                        type="url"
                        name="photo_url"
                        id="instructor-edit-photo-url"
                        value="{{ old('photo_url', $instructor->photo_url) }}"
                        placeholder="https://"
                        class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                    >
                    @error('photo_url')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="instructor-edit-website" class="block text-sm font-medium text-stone-700">Website <span class="font-normal text-stone-500">(optional)</span></label>
                    <input
                        type="url"
                        name="website"
                        id="instructor-edit-website"
                        value="{{ old('website', $instructor->website) }}"
                        placeholder="https://"
                        class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                    >
                    @error('website')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-forest px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-light"
                    >
                        Save changes
                    </button>
                    <a
                        href="{{ route('account.instructor') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-stone-200 bg-white px-6 py-3 text-sm font-semibold text-stone-700 shadow-sm transition hover:bg-stone-50"
                    >
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <form method="post" action="{{ route('logout') }}" class="mt-10">
            @csrf
            <button
                type="submit"
                class="inline-flex w-full items-center justify-center rounded-xl bg-forest px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-light sm:w-auto"
            >
                Sign out
            </button>
        </form>
    </div>

    @push('scripts')
        <script>
            (function () {
                var input = document.getElementById('instructor-edit-slug');
                var preview = document.getElementById('instructor-slug-preview');
                if (!input || !preview) return;
                input.addEventListener('input', function () {
                    preview.textContent = input.value;
                });
            })();
        </script>
    @endpush
@endsection
