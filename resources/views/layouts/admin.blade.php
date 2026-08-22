@extends('layouts.app')
@section('content')
<div class="min-h-screen bg-[#f4efe7]">
    <header class="border-b border-[#ddd7cd] bg-[#fffdf9]/95">
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-5 md:px-10">
            <a href="{{ route('admin.weddings.index') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/blende6-logo.png') }}" alt="Blende 6" class="w-36 md:w-44">
                <span class="hidden rounded-full bg-[#e8eee5] px-3 py-1 text-[9px] font-semibold uppercase tracking-[.16em] text-[#52604f] sm:inline">Master Admin</span>
            </a>
            <nav class="flex items-center gap-3 text-xs text-[#657064]">
                <a href="{{ route('admin.weddings.index') }}" class="rounded-full px-4 py-2 hover:bg-[#f1ece4]">Galerien</a>
                <form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="rounded-full px-4 py-2 hover:bg-[#f1ece4]">Abmelden</button></form>
            </nav>
        </div>
    </header>
    <main class="mx-auto max-w-7xl px-5 py-10 md:px-10 md:py-14">
        @if(session('success'))<div class="mb-7 rounded-2xl bg-[#e2eddf] px-5 py-4 text-sm text-[#425440]">{{ session('success') }}</div>@endif
        @yield('admin-content')
    </main>
</div>
@endsection
