@php
    $title = 'Blende 6 – Hochzeits- & Portraitfotografie in Hessen';
    $description = 'Natürliche und emotionale Hochzeitsfotografie und Portraitfotografie von Lina-Theresa Dick in der Wetterau und ganz Hessen.';
@endphp
@extends('marketing.layout')
@section('marketing-content')
<main>
    <section class="relative flex min-h-[720px] items-end overflow-hidden bg-[#425247] text-white" style="background-image:linear-gradient(90deg,rgba(27,38,31,.78),rgba(27,38,31,.12)),url('{{ asset('images/blende6/hero.jpg') }}');background-size:cover;background-position:center 38%">
        <div class="mx-auto w-full max-w-7xl px-5 pb-20 pt-32 md:px-10 md:pb-28">
            <span class="inline-flex rounded-2xl bg-white/95 px-6 py-4 shadow-2xl"><img src="{{ asset('images/blende6-logo.png') }}" alt="Blende 6" class="w-56 md:w-80"></span>
            <p class="mt-9 text-[10px] font-semibold uppercase tracking-[.28em] text-white/70">Fotografie, die sich nach dir anfühlt</p>
            <h1 class="mt-4 max-w-4xl font-serif text-6xl leading-[.92] tracking-[-.04em] md:text-8xl">Echte Momente.<br>Bleibende Geschichten.</h1>
            <p class="mt-7 max-w-2xl text-sm leading-7 text-white/80 md:text-base">Natürliche Hochzeitsfotografie und Portraitfotografie in der Wetterau, Büdingen, Hanau, Gießen, Gelnhausen und ganz Hessen.</p>
            <div class="mt-8 flex flex-wrap gap-3"><a href="{{ route('marketing.portfolio') }}" class="rounded-full bg-white px-6 py-3.5 text-sm font-semibold text-[#415045]">Portfolio entdecken</a><a href="{{ route('marketing.contact') }}" class="rounded-full border border-white/40 bg-white/10 px-6 py-3.5 text-sm font-semibold backdrop-blur">Shooting anfragen</a></div>
        </div>
    </section>

    <section class="mx-auto grid max-w-7xl gap-12 px-5 py-24 md:grid-cols-[.8fr_1.2fr] md:items-center md:px-10 md:py-32">
        <div class="relative mx-auto max-w-md"><div class="absolute -inset-5 -z-10 rounded-[3rem] border border-[#aeb7ae]"></div><img src="{{ asset('images/blende6/profile.png') }}" alt="Lina-Theresa Dick, Fotografin von Blende 6" class="aspect-square w-full rounded-[2.5rem] object-cover shadow-[0_28px_80px_rgba(54,67,58,.16)]"></div>
        <div><p class="text-[10px] font-semibold uppercase tracking-[.24em] text-[#778579]">Die Fotografin hinter Blende 6</p><h2 class="mt-4 font-serif text-5xl tracking-tight md:text-6xl">Lina-Theresa Dick</h2><div class="mt-7 space-y-5 text-sm leading-7 text-[#687169] md:text-base">
            <p>Ich bin Lina und biete <strong>Hochzeitsfotografie</strong> und <strong>Portraitfotografie</strong> in der Wetterau, in Büdingen, Hanau, Gießen, Gelnhausen und ganz Hessen an. Als leidenschaftliche Fotografin habe ich mich auf natürliche und emotionale Bilder spezialisiert, die echte Momente und authentische Gefühle festhalten.</p>
            <p>Ich liebe es, Frauen und Hochzeitspaare so zu fotografieren, wie sie wirklich sind: stark, lebendig und einzigartig. Meine Shootings finden am liebsten draußen in der Natur statt. Dort entstehen in entspannter Atmosphäre und mit natürlichem Licht lebendige und ausdrucksstarke Bilder.</p>
            <p>Was mich ausmacht, ist meine offene und empathische Art. Mir ist wichtig, dass du dich beim Shooting wohlfühlst und ganz du selbst sein kannst.</p>
        </div><a href="{{ route('marketing.about') }}" class="mt-8 inline-flex rounded-full bg-[#4d5d50] px-6 py-3.5 text-sm font-semibold text-white">Mehr über mich</a></div>
    </section>

    <section class="bg-[#e9ede7] px-5 py-24 md:px-10 md:py-28">
        <div class="mx-auto max-w-7xl"><div class="mb-10 flex flex-col justify-between gap-5 md:flex-row md:items-end"><div><p class="text-[10px] font-semibold uppercase tracking-[.24em] text-[#778579]">Ausgewählte Arbeiten</p><h2 class="mt-3 font-serif text-5xl tracking-tight md:text-6xl">Geschichten in Bildern</h2></div><a href="{{ route('marketing.portfolio') }}" class="text-sm font-semibold text-[#4d5d50]">Alle 122 Motive ansehen →</a></div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4"><img src="{{ asset('images/blende6/weddings/wedding-001.jpg') }}" alt="Hochzeitsfotografie von Blende 6" class="aspect-[3/4] w-full rounded-2xl object-cover"><img src="{{ asset('images/blende6/portfolio/portfolio-001.jpg') }}" alt="Portraitfotografie von Blende 6" class="aspect-[3/4] w-full rounded-2xl object-cover sm:mt-10"><img src="{{ asset('images/blende6/weddings/wedding-012.jpg') }}" alt="Hochzeitsmoment von Blende 6" class="aspect-[3/4] w-full rounded-2xl object-cover"><img src="{{ asset('images/blende6/portfolio/portfolio-012.jpg') }}" alt="Portrait von Blende 6" class="aspect-[3/4] w-full rounded-2xl object-cover sm:mt-10"></div>
        </div>
    </section>

    <section class="mx-auto grid max-w-6xl gap-12 px-5 py-24 md:grid-cols-[1fr_.75fr] md:items-center md:px-10 md:py-32"><div><p class="text-[10px] font-semibold uppercase tracking-[.24em] text-[#778579]">Freie Termine</p><h2 class="mt-4 font-serif text-5xl tracking-tight">Euer Moment wartet</h2><p class="mt-6 max-w-xl text-sm leading-7 text-[#687169]">Für dieses Jahr gibt es noch einen letzten freien Platz für ein Hochzeitsshooting 💍 und auch für das kommende Jahr dürfen schon Termine gebucht werden.</p><p class="mt-4 max-w-xl text-sm leading-7 text-[#687169]">Auch für Portraitshootings gibt es noch freie Plätze in ganz entspannter Atmosphäre. ✨</p><a href="mailto:info@blende6.de?subject=Shooting-Anfrage" class="mt-8 inline-flex rounded-full bg-[#4d5d50] px-6 py-3.5 text-sm font-semibold text-white">Termin anfragen</a></div><img src="{{ asset('images/blende6/availability.png') }}" alt="Freie Termine für Hochzeiten und Portraits" class="mx-auto max-h-[680px] rounded-[2.5rem] object-contain shadow-[0_28px_80px_rgba(54,67,58,.13)]"></section>
</main>
@endsection
