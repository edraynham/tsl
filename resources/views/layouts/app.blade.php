<!DOCTYPE html>
<html class="h-full" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700|playfair-display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-full bg-cream font-sans text-stone-800 antialiased">
    <div class="flex min-h-full flex-col">
        <header
            data-site-header
            class="site-header sticky top-0 z-50 border-b border-stone-200/90 bg-cream/95 backdrop-blur-md transition-shadow duration-300 ease-out"
        >
            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between gap-3 py-3 lg:gap-6 lg:py-4">
                    <a
                        href="{{ route('home') }}"
                        class="min-w-0 shrink font-serif text-lg font-semibold tracking-tight text-forest sm:text-xl lg:text-2xl"
                    >
                        @auth
                            @if (trim((string) auth()->user()->first_name) !== '')
                                {{ config('app.name') }}<span class="font-bold italic text-forest-muted"> for {{ trim(auth()->user()->first_name) }}</span>
                            @else
                                {{ config('app.name') }}
                            @endif
                        @else
                            {{ config('app.name') }}
                        @endauth
                    </a>

                    <nav
                        class="hidden flex-1 items-center justify-center gap-x-6 text-base font-medium lg:flex xl:gap-x-8"
                        aria-label="Main"
                    >
                        <a
                            href="{{ route('grounds.index') }}"
                            @if (request()->routeIs('grounds.*')) aria-current="page" @endif
                            class="whitespace-nowrap transition {{ request()->routeIs('grounds.*') ? 'font-semibold text-forest' : 'text-stone-700 hover:text-forest' }}"
                        >Clay Grounds</a>
                        <a
                            href="{{ route('competitions.index') }}"
                            @if (request()->routeIs('competitions.*')) aria-current="page" @endif
                            class="whitespace-nowrap transition {{ request()->routeIs('competitions.*') ? 'font-semibold text-forest' : 'text-stone-700 hover:text-forest' }}"
                        >Competitions</a>
                        <a
                            href="{{ route('instructors.index') }}"
                            @if (request()->routeIs('instructors.*')) aria-current="page" @endif
                            class="whitespace-nowrap transition {{ request()->routeIs('instructors.*') ? 'font-semibold text-forest' : 'text-stone-700 hover:text-forest' }}"
                        >Instructors</a>
                    </nav>

                    <div class="hidden items-center gap-3 lg:flex">
                        <form
                            action="{{ route('grounds.index') }}"
                            method="get"
                            class="flex h-10 w-10 shrink-0 items-center overflow-hidden rounded-full border border-stone-200 bg-white shadow-sm transition-[width,box-shadow] duration-200 ease-out focus-within:w-52 focus-within:border-stone-300 focus-within:shadow-md xl:focus-within:w-56"
                            role="search"
                        >
                            <label for="header-search-q" class="flex h-10 w-10 shrink-0 cursor-pointer items-center justify-center">
                                <span class="sr-only">Search grounds</span>
                                <svg class="size-4 text-stone-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                            </label>
                            <input
                                id="header-search-q"
                                type="search"
                                name="q"
                                placeholder="Search grounds..."
                                class="min-w-0 flex-1 border-0 bg-transparent py-2 pr-2 text-sm text-stone-800 placeholder:text-stone-400 focus:outline-none focus:ring-0"
                            >
                            <button type="submit" class="sr-only">Search</button>
                        </form>

                        @auth
                            <a
                                href="{{ route('account') }}"
                                class="inline-flex shrink-0 items-center justify-center rounded-full bg-forest px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-light sm:px-6"
                            >
                                My account
                            </a>
                            <form method="post" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button
                                    type="submit"
                                    class="inline-flex shrink-0 items-center justify-center rounded-full border border-stone-200 bg-white px-4 py-2.5 text-sm font-semibold text-stone-800 shadow-sm transition hover:bg-stone-50 sm:px-5"
                                >
                                    Sign out
                                </button>
                            </form>
                        @else
                            <a
                                href="{{ route('account') }}"
                                class="inline-flex shrink-0 items-center justify-center rounded-full bg-forest px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-light sm:px-6"
                            >
                                My account
                            </a>
                        @endauth
                    </div>

                    <div class="flex shrink-0 items-center gap-2 lg:hidden">
                        @auth
                            <a
                                href="{{ route('account') }}"
                                class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-full bg-forest px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-forest-light sm:px-5 sm:text-sm"
                            >
                                My account
                            </a>
                            <form method="post" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button
                                    type="submit"
                                    class="inline-flex min-h-11 items-center justify-center rounded-full border border-stone-200 bg-white px-3 py-2 text-xs font-semibold text-stone-800 shadow-sm transition hover:bg-stone-50 sm:px-4 sm:text-sm"
                                >
                                    Out
                                </button>
                            </form>
                        @else
                            <a
                                href="{{ route('account') }}"
                                class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-full bg-forest px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-forest-light sm:px-5 sm:text-sm"
                            >
                                My account
                            </a>
                        @endauth
                        <button
                            type="button"
                            class="inline-flex size-11 touch-manipulation items-center justify-center rounded-lg border border-stone-200 bg-white text-forest shadow-sm transition hover:border-stone-300 hover:bg-stone-50"
                            aria-controls="mobile-menu"
                            aria-expanded="false"
                            aria-label="Open menu"
                            data-mobile-nav-toggle
                        >
                            <svg data-mobile-nav-icon-open class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                            <svg data-mobile-nav-icon-close class="hidden size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div
                    id="mobile-menu"
                    class="absolute left-0 right-0 top-full z-40 hidden max-h-[min(85vh,28rem)] overflow-y-auto overscroll-contain rounded-b-2xl border-t border-stone-200/90 bg-cream pb-5 pt-1 shadow-[0_16px_40px_-12px_rgba(28,28,25,0.18)] lg:hidden"
                    data-mobile-nav-panel
                    aria-hidden="true"
                >
                    <nav class="flex flex-col gap-0.5 px-1 pt-2" aria-label="Main">
                        <a
                            href="{{ route('grounds.index') }}"
                            @if (request()->routeIs('grounds.*')) aria-current="page" @endif
                            class="rounded-xl px-3 py-3 text-base font-medium transition hover:bg-stone-100 active:bg-stone-200/60 min-[400px]:py-3.5 {{ request()->routeIs('grounds.*') ? 'font-semibold text-forest' : 'text-stone-800' }}"
                        >
                            Clay Grounds
                        </a>
                        <a
                            href="{{ route('competitions.index') }}"
                            @if (request()->routeIs('competitions.*')) aria-current="page" @endif
                            class="rounded-xl px-3 py-3 text-base font-medium transition hover:bg-stone-100 active:bg-stone-200/60 min-[400px]:py-3.5 {{ request()->routeIs('competitions.*') ? 'font-semibold text-forest' : 'text-stone-800' }}"
                        >
                            Competitions
                        </a>
                        <a
                            href="{{ route('instructors.index') }}"
                            @if (request()->routeIs('instructors.*')) aria-current="page" @endif
                            class="rounded-xl px-3 py-3 text-base font-medium transition hover:bg-stone-100 active:bg-stone-200/60 min-[400px]:py-3.5 {{ request()->routeIs('instructors.*') ? 'font-semibold text-forest' : 'text-stone-800' }}"
                        >
                            Instructors
                        </a>
                        @auth
                            <a
                                href="{{ route('account') }}"
                                class="rounded-xl px-3 py-3 text-base font-medium text-forest transition hover:bg-stone-100 active:bg-stone-200/60 min-[400px]:py-3.5"
                            >
                                My account
                            </a>
                            <form method="post" action="{{ route('logout') }}" class="px-3 pt-1">
                                @csrf
                                <button
                                    type="submit"
                                    class="w-full rounded-xl border border-stone-200 bg-white px-3 py-3 text-left text-base font-medium text-stone-800 transition hover:bg-stone-100"
                                >
                                    Sign out
                                </button>
                            </form>
                        @else
                            <a
                                href="{{ route('account') }}"
                                class="rounded-xl px-3 py-3 text-base font-medium text-forest transition hover:bg-stone-100 active:bg-stone-200/60 min-[400px]:py-3.5"
                            >
                                My account
                            </a>
                        @endauth
                    </nav>
                    <form action="{{ route('grounds.index') }}" method="get" class="mt-4 px-1" role="search">
                        <label for="mobile-search-q" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-stone-500">Search directory</label>
                        <div class="flex items-center gap-2 rounded-xl border border-stone-200 bg-white px-3 py-2.5 shadow-sm">
                            <svg class="size-5 shrink-0 text-stone-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                            <input
                                id="mobile-search-q"
                                type="search"
                                name="q"
                                placeholder="Town, county, postcode…"
                                class="min-w-0 flex-1 border-0 bg-transparent text-base text-stone-800 placeholder:text-stone-400 focus:outline-none focus:ring-0"
                                autocomplete="off"
                            >
                            <button type="submit" class="shrink-0 rounded-lg bg-forest px-3 py-1.5 text-sm font-semibold text-white hover:bg-forest-light">
                                Go
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </header>

        @auth
            @unless (request()->routeIs('verification.notice'))
                @if (! auth()->user()->hasVerifiedEmail())
                    <div class="border-b border-amber-200/90 bg-amber-50 px-4 py-3 text-center text-sm text-amber-950 sm:px-6">
                        <p class="mx-auto max-w-2xl leading-relaxed">
                            <span class="font-semibold">Verify your email</span>
                            to unlock owner tools and full account access.
                            <a href="{{ route('verification.notice') }}" class="font-semibold text-forest underline decoration-forest/30 underline-offset-2">Resend or open instructions</a>
                        </p>
                    </div>
                @endif
            @endunless
        @endauth

        <div
            class="fixed inset-0 z-40 hidden bg-stone-900/25 lg:hidden"
            data-mobile-nav-backdrop
            aria-hidden="true"
        ></div>

        <main class="flex-1">
            @yield('content')
        </main>

        <footer class="border-t border-stone-200/90 bg-cream pt-16 pb-10">
            <div class="mx-auto flex max-w-7xl flex-col gap-12 px-4 sm:px-6 lg:flex-row lg:px-8 lg:justify-between">
                <div class="max-w-sm">
                    <p class="font-serif text-xl font-semibold text-forest">{{ config('app.name') }}</p>
                    <p class="mt-4 text-sm leading-relaxed text-stone-600">
                        The UK’s trusted directory for clay shooting grounds and competitions.
                    </p>
                    <div class="mt-6 flex gap-4">
                        <a href="#" class="text-stone-500 transition hover:text-forest" aria-label="Instagram">
                            <svg class="size-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>
                        </a>
                        <a href="#" class="text-stone-500 transition hover:text-forest" aria-label="Facebook">
                            <svg class="size-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                    </div>
                </div>

                <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-3 lg:gap-16">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-stone-500">Explore</p>
                        <ul class="mt-4 space-y-3 text-sm">
                            <li><a href="{{ route('grounds.index') }}" class="text-stone-700 hover:text-forest">Clay Grounds</a></li>
                            <li><a href="{{ route('competitions.index') }}" class="text-stone-700 hover:text-forest">Competitions</a></li>
                            <li><a href="{{ route('instructors.index') }}" class="text-stone-700 hover:text-forest">Instructors</a></li>
                            <li><a href="{{ route('about') }}" class="text-stone-700 hover:text-forest">About TSL</a></li>
                        </ul>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-stone-500">Legal &amp; support</p>
                        <ul class="mt-4 space-y-3 text-sm">
                            <li><a href="{{ route('privacy') }}" class="text-stone-700 hover:text-forest">Privacy Policy</a></li>
                            <li><a href="#" class="text-stone-700 hover:text-forest">Terms of Service</a></li>
                            <li><a href="{{ route('contact') }}" class="text-stone-700 hover:text-forest">Contact</a></li>
                        </ul>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-1">
                        <p class="text-xs font-semibold uppercase tracking-wider text-stone-500">Newsletter</p>
                        <p class="mt-2 text-sm text-stone-600">Shooting news and ground updates.</p>
                        <div class="mt-4 flex gap-2">
                            <input type="email" name="email" placeholder="Email address" class="min-w-0 flex-1 rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-800 placeholder:text-stone-400 focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest">
                            <button type="button" class="flex shrink-0 items-center justify-center rounded-lg bg-forest px-4 text-white transition hover:bg-forest-light" aria-label="Subscribe">
                                <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <p class="mx-auto mt-8 max-w-7xl px-4 text-center text-xs text-stone-500 sm:px-6 lg:px-8">
                &copy; {{ date('Y') }} {{ config('app.name') }}. Clay shooting in the United Kingdom.
            </p>
        </footer>
    </div>

    <aside
        data-cookie-notice
        class="pointer-events-none fixed right-4 bottom-4 z-[70] w-[min(92vw,360px)] translate-x-4 opacity-0 transition duration-300 ease-out"
        aria-live="polite"
    >
        <div class="pointer-events-auto rounded-2xl border border-stone-200 bg-white p-4 shadow-[0_14px_30px_-10px_rgba(28,28,25,0.35)] sm:p-5">
            <p class="font-semibold text-forest">Cookies on {{ config('app.name') }}</p>
            <p class="mt-2 text-sm leading-relaxed text-stone-600">
                We use essential cookies to keep the site working and remember preferences. By continuing, you accept this.
                <a href="{{ route('privacy') }}" class="font-medium text-forest underline decoration-forest/30 underline-offset-2 hover:text-forest-light">Learn more</a>.
            </p>
            <div class="mt-4 flex items-center justify-end gap-2">
                <button
                    type="button"
                    data-cookie-accept
                    class="inline-flex items-center justify-center rounded-lg bg-forest px-4 py-2 text-sm font-semibold text-white transition hover:bg-forest-light"
                >
                    Accept
                </button>
            </div>
        </div>
    </aside>
    @stack('scripts')
</body>
</html>
