@extends('layouts.app')

@section('title', 'Edit '.$ground->name.' — '.config('app.name'))

@section('content')
    @php
        $selectedDisciplineIds = collect(old('discipline_ids', $ground->disciplines->pluck('id')->all()))
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->values()
            ->all();
        $selectedFacilityIds = collect(old('facility_ids', $ground->facilities->pluck('id')->all()))
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->values()
            ->all();
    @endphp

    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:py-10">
        <nav class="mb-6 text-sm">
            <a href="{{ route('owner.dashboard') }}" class="font-medium text-forest hover:text-forest-light">← My grounds</a>
        </nav>

        @include('account._tabs', ['active' => 'grounds'])

        @include('owner.grounds._subnav', ['ground' => $ground])

        @if (session('status'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('status') }}</div>
        @endif

        <div class="grid gap-10 lg:grid-cols-2 lg:items-start">
            <div>
                <h1 class="font-serif text-2xl font-semibold text-forest">Profile</h1>
                <p class="mt-1 text-sm text-stone-600">Changes update your public listing. The preview updates as you type.</p>

                <form id="owner-ground-form" method="post" action="{{ route('owner.grounds.update', $ground) }}" class="mt-6 space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-stone-700" for="name">Name</label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            data-preview="name"
                            value="{{ old('name', $ground->name) }}"
                            required
                            class="mt-1 w-full rounded-xl border border-stone-200 px-4 py-2.5 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-stone-700" for="slug">URL slug</label>
                        <input
                            type="text"
                            name="slug"
                            id="slug"
                            value="{{ old('slug', $ground->slug) }}"
                            required
                            class="mt-1 w-full rounded-xl border border-stone-200 px-4 py-2.5 font-mono text-sm text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                        >
                        <p class="mt-1 text-xs text-stone-500">Public URL: /grounds/<span id="slug-preview">{{ old('slug', $ground->slug) }}</span></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-stone-700" for="photo_url">Cover image URL</label>
                        <input
                            type="url"
                            name="photo_url"
                            id="photo_url"
                            data-preview="photo"
                            value="{{ old('photo_url', $ground->photo_url) }}"
                            class="mt-1 w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                        >
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-stone-700" for="city">Town / city</label>
                            <input
                                type="text"
                                name="city"
                                id="city"
                                data-preview="city"
                                value="{{ old('city', $ground->city) }}"
                                class="mt-1 w-full rounded-xl border border-stone-200 px-4 py-2.5 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700" for="county">County</label>
                            <input
                                type="text"
                                name="county"
                                id="county"
                                data-preview="county"
                                value="{{ old('county', $ground->county) }}"
                                class="mt-1 w-full rounded-xl border border-stone-200 px-4 py-2.5 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-stone-700" for="postcode">Postcode</label>
                        <input
                            type="text"
                            name="postcode"
                            id="postcode"
                            data-preview="postcode"
                            value="{{ old('postcode', $ground->postcode) }}"
                            class="mt-1 w-full max-w-xs rounded-xl border border-stone-200 px-4 py-2.5 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-stone-700" for="full_address">Full address</label>
                        <textarea
                            name="full_address"
                            id="full_address"
                            data-preview="address"
                            rows="3"
                            class="mt-1 w-full rounded-xl border border-stone-200 px-4 py-2.5 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                        >{{ old('full_address', $ground->full_address) }}</textarea>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-stone-700" for="latitude">Latitude</label>
                            <input
                                type="text"
                                name="latitude"
                                id="latitude"
                                value="{{ old('latitude', $ground->latitude) }}"
                                class="mt-1 w-full rounded-xl border border-stone-200 px-4 py-2.5 font-mono text-sm text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700" for="longitude">Longitude</label>
                            <input
                                type="text"
                                name="longitude"
                                id="longitude"
                                value="{{ old('longitude', $ground->longitude) }}"
                                class="mt-1 w-full rounded-xl border border-stone-200 px-4 py-2.5 font-mono text-sm text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-stone-700" for="description">Description</label>
                        <textarea
                            name="description"
                            id="description"
                            data-preview="description"
                            rows="8"
                            class="mt-1 w-full rounded-xl border border-stone-200 px-4 py-2.5 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                        >{{ old('description', $ground->description) }}</textarea>
                    </div>

                    <div class="space-y-3 rounded-xl border border-stone-200 bg-stone-50/80 px-4 py-4">
                        <div>
                            <p class="text-sm font-medium text-stone-700">Disciplines offered</p>
                            <p class="mt-0.5 text-xs text-stone-500">Shown on your public ground page and in the directory.</p>
                        </div>
                        <div class="grid max-h-64 gap-2 overflow-y-auto pr-1 sm:grid-cols-2">
                            @foreach ($disciplines as $d)
                                <label class="group flex cursor-pointer items-start gap-2.5 rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm transition hover:border-forest/40 hover:bg-forest/[0.03] has-[:checked]:border-forest has-[:checked]:bg-forest/[0.06]">
                                    <input
                                        type="checkbox"
                                        name="discipline_ids[]"
                                        value="{{ $d->id }}"
                                        class="mt-0.5 rounded border-stone-300 text-forest focus:ring-forest"
                                        @checked(in_array($d->id, $selectedDisciplineIds, true))
                                    >
                                    <span class="leading-relaxed"><span class="font-mono font-semibold text-forest">{{ $d->code }}</span> — {{ $d->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="space-y-3 rounded-xl border border-stone-200 bg-stone-50/80 px-4 py-4">
                        <div>
                            <p class="text-sm font-medium text-stone-700">Facilities &amp; amenities</p>
                            <p class="mt-0.5 text-xs text-stone-500">e.g. cafe, gun hire — shown on your public listing.</p>
                        </div>
                        <div class="grid max-h-56 gap-2 overflow-y-auto pr-1 sm:grid-cols-2">
                            @forelse ($facilities as $f)
                                <label class="group flex cursor-pointer items-start gap-2.5 rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm transition hover:border-forest/40 hover:bg-forest/[0.03] has-[:checked]:border-forest has-[:checked]:bg-forest/[0.06]">
                                    <input
                                        type="checkbox"
                                        name="facility_ids[]"
                                        value="{{ $f->id }}"
                                        class="mt-0.5 rounded border-stone-300 text-forest focus:ring-forest"
                                        @checked(in_array($f->id, $selectedFacilityIds, true))
                                    >
                                    <span class="leading-relaxed">{{ $f->name }}</span>
                                </label>
                            @empty
                                <p class="col-span-full text-sm text-stone-500">No facility records found yet.</p>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-stone-700" for="practice_notes">Practice notes</label>
                        <textarea name="practice_notes" id="practice_notes" rows="2" class="mt-1 w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest">{{ old('practice_notes', $ground->practice_notes) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700" for="lesson_notes">Lesson notes</label>
                        <textarea name="lesson_notes" id="lesson_notes" rows="2" class="mt-1 w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest">{{ old('lesson_notes', $ground->lesson_notes) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700" for="competition_notes">Competition notes</label>
                        <textarea name="competition_notes" id="competition_notes" rows="2" class="mt-1 w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest">{{ old('competition_notes', $ground->competition_notes) }}</textarea>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-stone-700" for="website">Website</label>
                            <input type="url" name="website" id="website" value="{{ old('website', $ground->website) }}" class="mt-1 w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700" for="facebook_url">Facebook</label>
                            <input type="url" name="facebook_url" id="facebook_url" value="{{ old('facebook_url', $ground->facebook_url) }}" class="mt-1 w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700" for="instagram_url">Instagram</label>
                            <input type="url" name="instagram_url" id="instagram_url" value="{{ old('instagram_url', $ground->instagram_url) }}" class="mt-1 w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest">
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                            <ul class="list-inside list-disc space-y-1">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <button type="submit" class="rounded-xl bg-forest px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-light">
                        Save profile
                    </button>
                </form>
            </div>

            <div class="lg:sticky lg:top-24">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-stone-500">Live preview</h2>
                <div class="mt-4 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm ring-1 ring-stone-100">
                    <div class="aspect-[21/9] max-h-40 bg-stone-100">
                        <img
                            src="{{ $ground->coverPhotoUrl() }}"
                            alt=""
                            class="size-full object-cover"
                            data-preview-img
                            data-fallback="{{ $ground->coverPhotoUrl() }}"
                        >
                    </div>
                    <div class="p-5">
                        <h3 class="font-serif text-xl font-semibold text-forest" data-preview-target="name">{{ $ground->name }}</h3>
                        <p class="mt-2 text-sm text-stone-700">
                            <span data-preview-target="address">{{ $ground->full_address ?: '—' }}</span>
                            @if ($ground->postcode)
                                <br><span class="text-stone-500" data-preview-target="postcode">{{ $ground->postcode }}</span>
                            @else
                                <span data-preview-target="postcode" class="hidden"></span>
                            @endif
                        </p>
                        <p class="mt-2 text-sm text-stone-600">
                            <span data-preview-target="city">{{ $ground->city ?: '—' }}</span>
                            @if ($ground->county)
                                <span> · </span><span data-preview-target="county">{{ $ground->county }}</span>
                            @else
                                <span data-preview-target="county"></span>
                            @endif
                        </p>
                        <div class="mt-4 max-h-48 overflow-y-auto border-t border-stone-100 pt-4 text-sm leading-relaxed text-stone-700">
                            <p class="whitespace-pre-wrap" data-preview-target="description">{{ $ground->description ?: '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const form = document.getElementById('owner-ground-form');
            if (!form) return;

            const slugInput = document.getElementById('slug');
            const slugPreview = document.getElementById('slug-preview');
            const img = document.querySelector('[data-preview-img]');
            const fallback = img ? img.dataset.fallback : '';

            function sync() {
                form.querySelectorAll('[data-preview]').forEach(function (input) {
                    const key = input.getAttribute('data-preview');
                    const el = document.querySelector('[data-preview-target="' + key + '"]');
                    if (el) {
                        el.textContent = input.value.trim() || '—';
                    }
                    if (input.name === 'photo_url' && img) {
                        var u = input.value.trim();
                        img.src = u || fallback;
                    }
                });
                if (slugInput && slugPreview) {
                    slugPreview.textContent = slugInput.value.trim() || '…';
                }
            }

            form.addEventListener('input', sync);
            form.addEventListener('change', sync);
            sync();
        })();
    </script>
@endpush
