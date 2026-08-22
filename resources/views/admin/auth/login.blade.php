@extends('layouts.app')
@section('content')
<main class="admin-login-bg grid min-h-screen place-items-center p-5">
    <section class="w-full max-w-md rounded-[2rem] border border-[#ded8cf] bg-[#fffdf9]/95 p-7 shadow-[0_30px_100px_rgba(55,65,57,.16)] backdrop-blur md:p-11">
        <img src="{{ asset('images/blende6-logo.png') }}" alt="Blende 6" class="mx-auto mb-8 w-64 max-w-full">
        <p class="mb-2 text-center text-[10px] font-semibold uppercase tracking-[.24em] text-[#7a8876]">Master Admin</p>
        <h1 class="text-center font-serif text-4xl tracking-tight">Willkommen zurück</h1>
        <p class="mt-3 text-center text-sm leading-6 text-[#7f867e]">Hochzeiten, Uploads und Galerien an einem Ort verwalten.</p>
        <form method="POST" action="{{ route('admin.login.store') }}" class="mt-8 space-y-4">
            @csrf
            <label class="block text-xs font-semibold text-[#536150]">E-Mail-Adresse<input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus class="mt-2 w-full rounded-xl border border-[#d7d3cc] bg-white px-4 py-3.5 text-sm outline-none focus:border-[#71806e] focus:ring-4 focus:ring-[#71806e]/10"></label>
            <label class="block text-xs font-semibold text-[#536150]">Passwort<input type="password" name="password" autocomplete="current-password" required class="mt-2 w-full rounded-xl border border-[#d7d3cc] bg-white px-4 py-3.5 text-sm outline-none focus:border-[#71806e] focus:ring-4 focus:ring-[#71806e]/10"></label>
            <label class="flex items-center gap-2 text-xs text-[#767f75]"><input type="checkbox" name="remember" value="1" class="rounded border-[#bdc4ba]"> Angemeldet bleiben</label>
            @error('email')<p class="text-sm text-[#a44f44]">{{ $message }}</p>@enderror
            <button class="w-full rounded-full bg-[#435343] px-6 py-4 text-sm font-semibold text-white shadow-lg shadow-[#435343]/15 transition hover:-translate-y-0.5">Anmelden</button>
        </form>
    </section>
</main>
@endsection
