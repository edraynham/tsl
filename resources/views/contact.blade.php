@extends('layouts.app')

@section('title', 'Contact — '.config('app.name'))

@section('content')
    <div class="border-b border-stone-200/80 bg-cream px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
        <div class="mx-auto max-w-xl">
            <nav class="mb-8 text-sm">
                <a href="{{ route('home') }}" class="font-medium text-forest hover:text-forest-light">← Home</a>
            </nav>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Get in touch</p>
            <h1 class="mt-3 font-serif text-4xl font-semibold tracking-tight text-forest sm:text-5xl">
                Contact us
            </h1>
            <p class="mt-4 text-base leading-relaxed text-stone-600">
                Questions about the directory, listings, or your account? Send us a message and we’ll reply by email.
            </p>

            @if (session('status'))
                <div class="mt-8 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <form method="post" action="{{ route('contact.store') }}" class="mt-10 space-y-5">
                @csrf
                <div>
                    <label for="contact-name" class="block text-sm font-medium text-stone-700">Name</label>
                    <input
                        type="text"
                        name="name"
                        id="contact-name"
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
                    <label for="contact-email" class="block text-sm font-medium text-stone-700">Email</label>
                    <input
                        type="email"
                        name="email"
                        id="contact-email"
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
                    <label for="contact-phone" class="block text-sm font-medium text-stone-700">Phone <span class="font-normal text-stone-500">(optional)</span></label>
                    <input
                        type="tel"
                        name="phone"
                        id="contact-phone"
                        value="{{ old('phone') }}"
                        autocomplete="tel"
                        class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                    >
                    @error('phone')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="contact-subject" class="block text-sm font-medium text-stone-700">Subject</label>
                    <input
                        type="text"
                        name="subject"
                        id="contact-subject"
                        value="{{ old('subject') }}"
                        required
                        maxlength="180"
                        class="mt-1 w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-800 shadow-sm focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest"
                    >
                    @error('subject')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="contact-message" class="block text-sm font-medium text-stone-700">Message</label>
                    <textarea
                        name="message"
                        id="contact-message"
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
        </div>
    </div>
@endsection
