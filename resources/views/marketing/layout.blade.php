@extends('layouts.app')
@section('content')
<div class="marketing-shell min-h-screen bg-[#f8f5ef]">
    <header class="sticky top-0 z-40 border-b border-[#dfe2dd] bg-[#faf8f3]/95 backdrop-blur-xl">
        <div class="mx-auto flex h-24 max-w-7xl items-center justify-between gap-5 px-5 md:px-10">
            <a href="{{ route('marketing.home') }}" aria-label="Blende 6 Startseite"><img src="{{ asset('images/blende6-logo.png') }}" alt="Blende 6" class="w-44 md:w-60"></a>
            <nav class="hidden items-center gap-1 text-xs font-semibold text-[#526057] md:flex">
                <a href="{{ route('marketing.home') }}" class="marketing-nav-link">Start</a>
                <a href="{{ route('marketing.about') }}" class="marketing-nav-link">Über mich</a>
                <a href="{{ route('marketing.shootings') }}" class="marketing-nav-link">Shootings</a>
                <a href="{{ route('marketing.portfolio') }}" class="marketing-nav-link">Portfolio</a>
                <a href="{{ route('marketing.contact') }}" class="marketing-nav-link">Kontakt</a>
            </nav>
            <a href="mailto:info@blende6.de" class="rounded-full bg-[#4d5d50] px-4 py-3 text-[10px] font-semibold uppercase tracking-[.12em] text-white transition hover:-translate-y-0.5 md:px-5">Shooting anfragen</a>
        </div>
        <nav class="flex justify-center gap-1 overflow-x-auto border-t border-[#e5e5e0] px-4 py-2 text-[10px] font-semibold text-[#526057] md:hidden">
            <a href="{{ route('marketing.home') }}" class="marketing-nav-link">Start</a><a href="{{ route('marketing.about') }}" class="marketing-nav-link">Über mich</a><a href="{{ route('marketing.shootings') }}" class="marketing-nav-link">Shootings</a><a href="{{ route('marketing.portfolio') }}" class="marketing-nav-link">Portfolio</a><a href="{{ route('marketing.contact') }}" class="marketing-nav-link">Kontakt</a>
        </nav>
    </header>

    @yield('marketing-content')

    <footer class="bg-[#435243] text-white">
        <div class="mx-auto grid max-w-7xl gap-10 px-6 py-14 md:grid-cols-[1.2fr_1fr_1fr] md:px-10">
            <div><span class="inline-flex rounded-2xl bg-white/95 px-5 py-3"><img src="{{ asset('images/blende6-logo.png') }}" alt="Blende 6" class="w-48"></span><p class="mt-5 max-w-sm text-xs leading-6 text-white/65">Natürliche Hochzeits- und Portraitfotografie in der Wetterau, Büdingen, Hanau, Gießen, Gelnhausen und ganz Hessen.</p></div>
            <div><p class="text-[10px] font-semibold uppercase tracking-[.2em] text-white/50">Kontakt</p><p class="mt-4 text-sm leading-7">Lina-Theresa Dick<br>Freiherr-vom-Stein-Straße 28<br>63695 Glauburg</p><a href="mailto:info@blende6.de" class="mt-2 inline-block text-sm underline underline-offset-4">info@blende6.de</a></div>
            <div><p class="text-[10px] font-semibold uppercase tracking-[.2em] text-white/50">Blende 6</p><div class="mt-4 flex flex-col items-start gap-2 text-sm"><a href="{{ route('marketing.shootings') }}">Shootings</a><a href="{{ route('marketing.portfolio') }}">Portfolio</a><a href="{{ route('marketing.contact') }}">Kontakt</a><a href="{{ route('marketing.imprint') }}">Impressum</a><a href="{{ route('marketing.privacy') }}">Datenschutz</a><a href="{{ route('admin.login') }}" class="text-white/55">Master-Admin</a></div></div>
        </div>
        <div class="border-t border-white/10 px-6 py-5 text-center text-[9px] uppercase tracking-[.18em] text-white/45">© {{ date('Y') }} Blende 6 Fotografie · Lina-Theresa Dick</div>
    </footer>
</div>
@endsection
