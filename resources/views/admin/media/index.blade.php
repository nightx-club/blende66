@extends('layouts.admin')
@section('admin-content')
<div class="flex flex-col justify-between gap-5 md:flex-row md:items-end">
    <div><a href="{{ route('admin.weddings.index') }}" class="text-xs text-[#748072]">← Alle Hochzeiten</a><p class="mb-2 mt-5 text-[10px] font-semibold uppercase tracking-[.24em] text-[#7b8878]">Medienverwaltung</p><h1 class="font-serif text-5xl tracking-tight">{{ $wedding->couple_names }}</h1></div>
    <div class="flex flex-wrap gap-2"><a href="{{ route('admin.weddings.media.zip', $wedding) }}" class="rounded-full border border-[#abb5a8] px-5 py-3 text-xs text-[#50604f]">↓ Alle Originale als ZIP</a><a href="{{ route('admin.weddings.edit', $wedding) }}" class="rounded-full bg-[#435343] px-5 py-3 text-xs text-white">Hochzeit bearbeiten</a></div>
</div>

<section class="mt-8 grid grid-cols-3 overflow-hidden rounded-2xl border border-[#ded8ce] bg-[#fffdf9]">
    <div class="p-5 text-center"><strong class="block font-serif text-3xl">{{ $stats['photos'] }}</strong><span class="text-[9px] uppercase tracking-wider text-[#8a9189]">Fotos</span></div>
    <div class="border-x border-[#e5dfd6] p-5 text-center"><strong class="block font-serif text-3xl">{{ $stats['videos'] }}</strong><span class="text-[9px] uppercase tracking-wider text-[#8a9189]">Videos</span></div>
    <div class="p-5 text-center"><strong class="block font-serif text-3xl">{{ number_format($stats['bytes']/1024/1024, 1, ',', '.') }}</strong><span class="text-[9px] uppercase tracking-wider text-[#8a9189]">MB belegt</span></div>
</section>

@if($errors->any())<div class="mt-6 rounded-2xl bg-[#f7e7e4] p-4 text-sm text-[#9f4d43]">{{ $errors->first() }}</div>@endif

<section class="mt-10">
    <div class="mb-5 flex flex-col justify-between gap-2 sm:flex-row sm:items-end">
        <div><p class="mb-2 text-[10px] font-semibold uppercase tracking-[.24em] text-[#7b8878]">Master Admin</p><h2 class="font-serif text-3xl">Alben nach Personen</h2></div>
        <p class="text-xs text-[#858c84]">Alle Uploads einer Person werden zu einem Album zusammengefasst.</p>
    </div>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($guestAlbums as $album)
            <article class="rounded-2xl border border-[#ded8ce] bg-[#fffdf9] p-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <strong class="block truncate text-sm text-[#354238]">Album von {{ $album->name }}</strong>
                        @if($album->guest_email)<a href="mailto:{{ $album->guest_email }}" class="mt-1 block truncate text-xs text-[#657161]">{{ $album->guest_email }}</a>@else<p class="mt-1 truncate text-xs text-[#858c84]">Keine E-Mail hinterlegt</p>@endif
                    </div>
                    <span class="rounded-full bg-[#edf1ea] px-3 py-1 text-[9px] font-semibold text-[#536150]">{{ $album->media_count }} Dateien</span>
                </div>
                <p class="mt-4 text-[10px] leading-5 text-[#858c84]">{{ $album->photo_count }} Fotos · {{ $album->video_count }} Videos · {{ number_format(($album->storage_bytes ?? 0)/1024/1024, 1, ',', '.') }} MB<br>Zuletzt: {{ \Illuminate\Support\Carbon::parse($album->latest_upload_at)->format('d.m.Y H:i') }} Uhr</p>
                <form method="POST" action="{{ route('admin.weddings.albums.destroy', $wedding) }}" class="mt-4 border-t border-[#e8e3da] pt-4">
                    @csrf @method('DELETE')
                    <input type="hidden" name="guest_name" value="{{ $album->name }}">
                    <button onclick="return confirm(@js('Das komplette Album von '.$album->name.' mit '.$album->media_count.' Dateien dauerhaft löschen?'))" class="text-xs font-semibold text-[#a24e44]">Komplettes Album löschen</button>
                </form>
            </article>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-[#bbc3b8] p-8 text-center text-sm text-[#7f877e]">Noch keine Personen-Alben vorhanden.</div>
        @endforelse
    </div>
</section>

<section class="mt-10">
    <div class="mb-5"><p class="mb-2 text-[10px] font-semibold uppercase tracking-[.24em] text-[#7b8878]">Einzelverwaltung</p><h2 class="font-serif text-3xl">Einzelne Fotos & Videos</h2></div>
</section>

<form method="POST" action="{{ route('admin.weddings.media.bulk', $wedding) }}" class="mt-8" id="bulk-form">
    @csrf
    <div class="mb-4 flex flex-wrap items-center gap-2 rounded-2xl bg-[#ece7df] p-3">
        <label class="mr-2 flex items-center gap-2 px-2 text-xs text-[#657064]"><input type="checkbox" id="select-all" class="rounded"> Alle auswählen</label>
        <button name="action" value="publish" class="rounded-full bg-white px-4 py-2 text-xs text-[#52604f]">Veröffentlichen</button>
        <button name="action" value="hide" class="rounded-full bg-white px-4 py-2 text-xs text-[#52604f]">Ausblenden</button>
        <button name="action" value="delete" onclick="return confirm('Ausgewählte Medien samt Originaldateien dauerhaft löschen?')" class="rounded-full bg-[#f7e3df] px-4 py-2 text-xs text-[#9e4b42]">Löschen</button>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse($media as $item)
            <article class="group overflow-hidden rounded-2xl border {{ $item->is_published ? 'border-[#ded8ce]' : 'border-[#d4b2aa]' }} bg-[#fffdf9]">
                <div class="relative h-48 overflow-hidden bg-[#687363]">
                    @if($item->type === 'photo')<img src="{{ route('weddings.media.thumbnail', [$wedding, $item]) }}" loading="lazy" class="size-full object-cover" alt="">@else<div class="video-placeholder size-full"><span class="text-2xl">▶</span><small>{{ gmdate('i:s', $item->video_duration ?? 0) }}</small></div>@endif
                    <label class="absolute left-3 top-3 grid size-8 place-items-center rounded-full bg-white/90 shadow"><input type="checkbox" name="media_ids[]" value="{{ $item->id }}" class="media-check rounded"></label>
                    @unless($item->is_published)<span class="absolute right-3 top-3 rounded-full bg-[#f7e2de] px-3 py-1 text-[9px] font-semibold text-[#9a4a42]">Ausgeblendet</span>@endunless
                </div>
                <div class="flex items-end justify-between gap-3 p-4"><div class="min-w-0"><strong class="block truncate text-xs">{{ $item->original_name }}</strong><p class="mt-2 text-[9px] leading-4 text-[#858c84]">{{ $item->guest_name ?: 'Ohne Gastname' }} · {{ $item->created_at->format('d.m.Y H:i') }}<br>{{ number_format($item->file_size/1024/1024, 1, ',', '.') }} MB</p></div><button type="submit" form="delete-media-{{ $item->id }}" onclick="return confirm('Dieses Medium samt Originaldatei dauerhaft löschen?')" class="text-[10px] text-[#a24e44]">Löschen</button></div>
            </article>
        @empty
            <div class="col-span-full rounded-[2rem] border border-dashed border-[#bbc3b8] p-16 text-center"><h2 class="font-serif text-3xl">Noch keine Uploads</h2><p class="mt-2 text-sm text-[#7f877e]">Sobald Gäste Dateien teilen, erscheinen sie hier.</p></div>
        @endforelse
    </div>
</form>
@foreach($media as $item)<form id="delete-media-{{ $item->id }}" method="POST" action="{{ route('admin.weddings.media.destroy', [$wedding, $item]) }}" class="hidden">@csrf @method('DELETE')</form>@endforeach
<div class="mt-8">{{ $media->links() }}</div>
@push('scripts')<script>document.querySelector('#select-all')?.addEventListener('change',e=>document.querySelectorAll('.media-check').forEach(x=>x.checked=e.target.checked));document.querySelector('#bulk-form')?.addEventListener('submit',e=>{if(!document.querySelector('.media-check:checked')){e.preventDefault();alert('Bitte zuerst mindestens ein Medium auswählen.')}});</script>@endpush
@endsection
