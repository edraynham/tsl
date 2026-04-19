@extends('layouts.app')

@section('title', 'Privacy policy — '.config('app.name'))

@section('content')
    <article class="border-b border-stone-200/80 bg-cream px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
        <div class="mx-auto max-w-3xl">
            <nav class="mb-8 text-sm">
                <a href="{{ route('home') }}" class="font-medium text-forest hover:text-forest-light">← Home</a>
            </nav>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Legal</p>
            <h1 class="mt-3 font-serif text-4xl font-semibold tracking-tight text-forest sm:text-5xl">
                Privacy policy
            </h1>
            <p class="mt-4 text-sm text-stone-600">
                Last updated: {{ date('j F Y') }}. This policy describes how {{ config('app.name') }} (“we”, “us”) handles personal information when you use our website and related services.
            </p>

            <div class="mt-12 space-y-10 text-stone-700">
                <section class="space-y-3">
                    <h2 class="font-serif text-xl font-semibold text-forest">Who we are</h2>
                    <p class="text-base leading-relaxed">
                        {{ config('app.name') }} operates an online directory of UK clay shooting grounds and competitions. For data protection purposes, the controller of your personal information is the person or organisation operating this site (referred to in this policy as “we”).
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="font-serif text-xl font-semibold text-forest">Information we collect</h2>
                    <p class="text-base leading-relaxed">
                        Depending on how you use the site, we may process:
                    </p>
                    <ul class="list-inside list-disc space-y-2 pl-1 text-base leading-relaxed">
                        <li><span class="font-medium text-stone-800">Account details</span> — if you register or sign in (for example name, email address, and preferences such as whether you identify as a shooter or ground owner).</li>
                        <li><span class="font-medium text-stone-800">Technical data</span> — such as IP address, browser type, approximate location derived from IP or a postcode or place you search for, and cookies or similar technologies as described below.</li>
                        <li><span class="font-medium text-stone-800">Communications</span> — messages you send us and, where applicable, records needed to provide magic-link or email verification services.</li>
                        <li><span class="font-medium text-stone-800">Ground and event information</span> — details you submit as a ground owner or organiser to be displayed publicly on the site.</li>
                    </ul>
                </section>

                <section class="space-y-3">
                    <h2 class="font-serif text-xl font-semibold text-forest">How we use your information</h2>
                    <p class="text-base leading-relaxed">
                        We use personal information to provide and improve the directory, authenticate users, show relevant listings (including by location where you choose to share it), send service emails such as sign-in links or verification messages, keep our service secure, comply with the law, and understand how the site is used in aggregate.
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="font-serif text-xl font-semibold text-forest">Legal bases (UK GDPR)</h2>
                    <p class="text-base leading-relaxed">
                        Where UK GDPR applies, we rely on appropriate bases such as: <span class="font-medium text-stone-800">performance of a contract</span> (providing the services you ask for); <span class="font-medium text-stone-800">legitimate interests</span> (running and securing the site, analytics at an appropriate level); and <span class="font-medium text-stone-800">consent</span> where required (for example non-essential cookies, if we use them). You may withdraw consent where processing is based on consent, without affecting processing before withdrawal.
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="font-serif text-xl font-semibold text-forest">Cookies and similar technologies</h2>
                    <p class="text-base leading-relaxed">
                        We may use cookies and similar technologies that are strictly necessary for the site to function (for example session or security cookies), and where applicable analytics or preference cookies in line with your choices and applicable law. You can control many cookies through your browser settings.
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="font-serif text-xl font-semibold text-forest">Sharing and processors</h2>
                    <p class="text-base leading-relaxed">
                        We do not sell your personal information. We may share it with service providers who host the site, send email, or provide infrastructure, strictly as needed to operate {{ config('app.name') }}, and under appropriate contractual safeguards. We may disclose information if required by law or to protect rights, safety, or integrity of users or the public.
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="font-serif text-xl font-semibold text-forest">Retention</h2>
                    <p class="text-base leading-relaxed">
                        We keep personal information only for as long as necessary for the purposes above, including legal, accounting, or reporting requirements. When data is no longer needed, we delete or anonymise it in line with our practices.
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="font-serif text-xl font-semibold text-forest">Your rights</h2>
                    <p class="text-base leading-relaxed">
                        Under UK data protection law you may have rights including access, rectification, erasure, restriction of processing, objection, and data portability, and the right to lodge a complaint with the Information Commissioner’s Office (ICO). To exercise your rights, contact us using the details below. We will respond within the timeframes required by law.
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="font-serif text-xl font-semibold text-forest">International transfers</h2>
                    <p class="text-base leading-relaxed">
                        If we or our processors transfer personal data outside the UK, we will ensure appropriate safeguards are in place as required by applicable law.
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="font-serif text-xl font-semibold text-forest">Children</h2>
                    <p class="text-base leading-relaxed">
                        Our services are not directed at children under 13, and we do not knowingly collect personal information from them. If you believe we have done so, please contact us and we will take steps to delete it.
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="font-serif text-xl font-semibold text-forest">Changes to this policy</h2>
                    <p class="text-base leading-relaxed">
                        We may update this policy from time to time. The “Last updated” date at the top will change when we do. Continued use of the site after changes constitutes acceptance of the updated policy where permitted by law.
                    </p>
                </section>

                <section class="space-y-3">
                    <h2 class="font-serif text-xl font-semibold text-forest">Contact</h2>
                    <p class="text-base leading-relaxed">
                        For privacy questions or requests, please contact us using the contact options published on this website (for example a contact email or form when available). If no dedicated address is shown yet, you may reach the site operator through any general enquiry channel we provide.
                    </p>
                </section>
            </div>
        </div>
    </article>
@endsection
