@extends('layouts.admin')
@section('admin-content')
<div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
    <div><p class="mb-2 text-[10px] font-semibold uppercase tracking-[.24em] text-[#7b8878]">Übersicht</p><h1 class="font-serif text-5xl tracking-tight md:text-6xl">Hochzeiten & Events</h1><p class="mt-3 text-sm text-[#7b837b]">Jede Feier hat ihre eigene Galerie und einen individuellen QR-Code.</p></div>
    <a href="{{ route('admin.weddings.create') }}" class="rounded-full bg-[#435343] px-6 py-3.5 text-center text-sm font-semibold text-white">＋ Hochzeit / Event anlegen</a>
</div>

<div class="mt-10 grid gap-5 lg:grid-cols-2">
    @forelse($weddings as $wedding)
        <article class="overflow-hidden rounded-[1.5rem] border border-[#ded8ce] bg-[#fffdf9] shadow-sm">
            <div class="relative h-48 bg-[#77816f]" @if($wedding->cover_image_path) style="background-image:linear-gradient(0deg,rgba(32,42,34,.6),transparent),url('{{ route('weddings.cover', $wedding) }}');background-size:cover;background-position:center" @endif>
                @unless($wedding->cover_image_path)<div class="absolute inset-0 wedding-placeholder"></div>@endunless
                <span class="absolute right-4 top-4 rounded-full px-3 py-1.5 text-[10px] font-semibold {{ $wedding->is_active ? 'bg-[#e4efdf] text-[#41553e]' : 'bg-[#eee9e3] text-[#847e77]' }}">{{ $wedding->is_active ? 'Aktiv' : 'Deaktiviert' }}</span>
                <div class="absolute inset-x-0 bottom-0 p-5 text-white"><p class="text-[9px] uppercase tracking-[.2em] text-white/70">{{ $wedding->wedding_date->translatedFormat('d. F Y') }}</p><h2 class="mt-1 font-serif text-3xl">{{ $wedding->couple_names }}</h2></div>
            </div>
            <div class="grid grid-cols-3 border-b border-[#e5dfd6] text-center"><div class="p-4"><strong class="block font-serif text-2xl">{{ $wedding->photo_count }}</strong><span class="text-[9px] uppercase tracking-wider text-[#8a9189]">Fotos</span></div><div class="border-x border-[#e5dfd6] p-4"><strong class="block font-serif text-2xl">{{ $wedding->video_count }}</strong><span class="text-[9px] uppercase tracking-wider text-[#8a9189]">Videos</span></div><div class="p-4"><strong class="block font-serif text-2xl">{{ number_format(($wedding->storage_bytes ?? 0)/1024/1024, 1, ',', '.') }}</strong><span class="text-[9px] uppercase tracking-wider text-[#8a9189]">MB</span></div></div>
            <div class="flex flex-wrap gap-2 p-4 text-xs">
                <a href="{{ route('admin.weddings.media.index', $wedding) }}" class="rounded-full bg-[#435343] px-4 py-2.5 text-white">Medien verwalten</a>
                <a href="{{ route('admin.weddings.edit', $wedding) }}" class="rounded-full border border-[#bbc2b8] px-4 py-2.5 text-[#52604f]">Bearbeiten</a>
                <a href="{{ route('admin.weddings.qr.download', $wedding) }}" class="rounded-full border border-[#bbc2b8] px-4 py-2.5 text-[#52604f]">↓ QR-Code</a>
                <a href="{{ route('weddings.show', $wedding) }}" target="_blank" class="rounded-full px-4 py-2.5 text-[#6f786e]">Gastseite ↗</a>
            </div>
        </article>
    @empty
        <div class="col-span-full rounded-[2rem] border border-dashed border-[#bbc3b8] p-16 text-center"><h2 class="font-serif text-3xl">Noch keine Hochzeit / kein Event</h2><p class="mt-2 text-sm text-[#7f877e]">Legt eure erste Galerie in wenigen Schritten an.</p></div>
    @endforelse
</div>
@endsection
