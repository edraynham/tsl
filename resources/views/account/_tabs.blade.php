@props([
    'active',
])

@php
    $isProfile = $active === 'profile';
    $isGrounds = $active === 'grounds';
    $isInstructor = $active === 'instructor';

    $tabClass = fn (bool $on) => $on
        ? '-mb-px border-b-2 border-forest pb-3 font-semibold text-forest'
        : '-mb-px border-b-2 border-transparent pb-3 text-stone-600 transition hover:border-stone-300 hover:text-forest';
@endphp

<nav class="mb-8 flex flex-wrap gap-x-6 gap-y-2 border-b border-stone-200 text-sm" aria-label="Account sections">
    <a
        href="{{ route('account') }}"
        @if ($isProfile) aria-current="page" @endif
        class="{{ $tabClass($isProfile) }}"
    >
        My Profile
    </a>
    <a
        href="{{ route('owner.dashboard') }}"
        @if ($isGrounds) aria-current="page" @endif
        class="{{ $tabClass($isGrounds) }}"
    >
        My Grounds
    </a>
    <a
        href="{{ route('account.instructor') }}"
        @if ($isInstructor) aria-current="page" @endif
        class="{{ $tabClass($isInstructor) }}"
    >
        My Instructor Profile
    </a>
</nav>
