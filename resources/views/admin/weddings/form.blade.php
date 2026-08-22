@extends('layouts.admin')
@section('admin-content')
@php
    $editing = $wedding->exists;
@endphp
<div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
    <div><a href="{{ route('admin.weddings.index') }}" class="text-xs text-[#748072]">← Zur Übersicht</a><h1 class="mt-4 font-serif text-5xl tracking-tight">{{ $editing ? $wedding->couple_names : 'Neue Hochzeit / Event' }}</h1></div>
    @if($editing)<div class="flex gap-2"><a href="{{ route('admin.weddings.qr.download', $wedding) }}" class="rounded-full border border-[#abb5a8] px-5 py-3 text-xs text-[#50604f]">QR-Code laden</a><a href="{{ route('admin.weddings.media.index', $wedding) }}" class="rounded-full bg-[#435343] px-5 py-3 text-xs text-white">Medien</a></div>@endif
</div>

@if($errors->any())<div class="mt-7 rounded-2xl bg-[#f7e7e4] p-5 text-sm text-[#9f4d43]"><strong>Bitte prüft die Eingaben:</strong><ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.weddings.update', $wedding) : route('admin.weddings.store') }}" class="mt-9 grid gap-6 lg:grid-cols-[1fr_330px]">
    @csrf @if($editing) @method('PUT') @endif
    <div class="space-y-6">
        <section class="admin-panel"><div class="panel-title"><span>01</span><div><h2>Hochzeit / Event</h2><p>Grunddaten und persönliche Begrüßung</p></div></div>
            <div class="mt-6 grid gap-5 sm:grid-cols-2"><label class="form-label sm:col-span-2">Name des Brautpaares / Events<input name="couple_names" value="{{ old('couple_names', $wedding->couple_names) }}" required class="form-input" placeholder="Anna & Tom oder Sommerfest 2026"></label><label class="form-label">URL-Kürzel<input name="slug" value="{{ old('slug', $wedding->slug) }}" required class="form-input" placeholder="anna-und-tom"></label><label class="form-label">Datum<input type="date" name="wedding_date" value="{{ old('wedding_date', optional($wedding->wedding_date)->format('Y-m-d')) }}" required class="form-input"></label><label class="form-label sm:col-span-2">Begrüßungstext<textarea name="welcome_text" rows="4" class="form-input" placeholder="Schön, dass ihr diesen Tag mit uns teilt …">{{ old('welcome_text', $wedding->welcome_text) }}</textarea></label><label class="form-label sm:col-span-2">Titelbild<input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp" class="form-input file:mr-4 file:rounded-full file:border-0 file:bg-[#e9eee6] file:px-4 file:py-2 file:text-xs file:text-[#465745]"></label></div>
        </section>
        <section class="admin-panel"><div class="panel-title"><span>02</span><div><h2>Zugang</h2><p>PIN und Sichtbarkeit der Gastseite</p></div></div>
            <div class="mt-6 grid gap-5 sm:grid-cols-2"><label class="form-label">{{ $editing ? 'Neue PIN (optional)' : 'Hochzeits-PIN' }}<input name="pin" inputmode="numeric" maxlength="10" {{ $editing ? '' : 'required' }} class="form-input" placeholder="z. B. 260826"></label><label class="flex items-center gap-3 self-end rounded-xl bg-[#f3efe8] px-4 py-3.5 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $wedding->exists ? $wedding->is_active : true)) class="size-4 rounded"> Gastseite aktiv</label></div>
        </section>
        <section class="admin-panel"><div class="panel-title"><span>03</span><div><h2>Upload-Grenzen</h2><p>Je Hochzeit / Event flexibel einstellbar</p></div></div>
            <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3"><label class="form-label">Foto max. MB<input type="number" name="photo_max_mb" min="1" max="50" value="{{ old('photo_max_mb', $wedding->photo_max_mb ?: 25) }}" class="form-input"></label><label class="form-label">Fotos je Upload<input type="number" name="photo_batch_max" min="1" max="20" value="{{ old('photo_batch_max', $wedding->photo_batch_max ?: 20) }}" class="form-input"></label><label class="form-label">Video max. MB<input type="number" name="video_max_mb" min="10" max="100" value="{{ old('video_max_mb', min($wedding->video_max_mb ?: 100, 100)) }}" class="form-input"><span class="mt-2 block text-[10px] font-normal text-[#8a9089]">Absolute Obergrenze: 100 MB</span></label><label class="form-label">Video max. Sekunden<input type="number" name="video_max_seconds" min="10" max="600" value="{{ old('video_max_seconds', $wedding->video_max_seconds ?: 180) }}" class="form-input"></label><label class="form-label">Videos je Upload<input type="number" name="video_batch_max" min="1" max="5" value="{{ old('video_batch_max', $wedding->video_batch_max ?: 5) }}" class="form-input"></label></div>
        </section>
        <button class="rounded-full bg-[#435343] px-7 py-4 text-sm font-semibold text-white">{{ $editing ? 'Änderungen speichern' : 'Hochzeit / Event anlegen' }}</button>
    </div>
    <aside class="space-y-5">
        @if($editing)<section class="admin-panel text-center"><p class="mb-4 text-[10px] font-semibold uppercase tracking-[.2em] text-[#7b8878]">Individueller QR-Code</p><img src="{{ route('admin.weddings.qr', $wedding) }}" alt="Individueller QR-Code für {{ $wedding->couple_names }}" class="mx-auto w-52 rounded-xl"><p class="mt-4 text-[10px] leading-5 text-[#848b83]">Dieser QR-Code gehört ausschließlich zu dieser Hochzeit / diesem Event. Gäste können damit direkt öffnen, hochladen und herunterladen.</p><a href="{{ route('admin.weddings.qr.download', $wedding) }}" class="mt-4 inline-flex rounded-full bg-[#435343] px-5 py-2.5 text-xs text-white">QR-Code herunterladen</a></section>@endif
        <section class="rounded-3xl bg-[#435343] p-6 text-white"><h3 class="font-serif text-2xl">Tipp</h3><p class="mt-3 text-xs leading-6 text-white/70">Druckt den individuellen QR-Code auf Tischkarten oder Eventmaterialien. Jeder Code führt nur zur zugehörigen Galerie.</p></section>
    </aside>
</form>
@endsection
