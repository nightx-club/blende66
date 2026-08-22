@php
    $title = $wedding->couple_names.' – Hochzeitsmomente';
@endphp
@extends('layouts.app')
@section('content')

@php
    $cover = $wedding->cover_image_path ? route('weddings.cover', $wedding) : null;
@endphp

<main class="min-h-screen">
    <header class="absolute inset-x-0 top-0 z-20 flex h-28 items-center justify-between px-5 text-white md:px-12">
        <a href="{{ route('weddings.show', $wedding) }}" class="inline-flex items-center gap-4 rounded-2xl border border-white/40 bg-white/90 px-4 py-3 shadow-xl shadow-[#263229]/10 backdrop-blur-md" aria-label="Blende 6 – zur Hochzeitsgalerie">
            <img src="{{ asset('images/blende6-logo.png') }}" alt="Blende 6" class="w-40 md:w-64">
            <span class="hidden border-l border-[#8f9990]/40 pl-4 font-serif text-sm tracking-[.16em] text-[#566158] lg:block">{{ str_replace([' & ', ' und '], ' · ', $wedding->couple_names) }}</span>
        </a>
        @if($unlocked)
            <button type="button" data-open-upload class="hidden rounded-full border border-white/30 bg-[#465745]/90 px-5 py-3 text-xs font-semibold shadow-lg backdrop-blur-md transition hover:-translate-y-0.5 hover:bg-[#354535] sm:block">＋ Moment teilen</button>
        @endif
    </header>

    <section class="relative flex min-h-[700px] items-end overflow-hidden bg-[#687363] text-white md:min-h-[820px]" @if($cover) style="background-image:linear-gradient(90deg,rgba(20,31,24,.78) 0%,rgba(20,31,24,.35) 48%,rgba(20,31,24,.12) 100%),linear-gradient(0deg,rgba(20,31,24,.48),transparent 48%),url('{{ $cover }}');background-size:cover;background-position:center 42%" @endif>
        @unless($cover)
            <div class="absolute inset-0 wedding-placeholder"><span class="petal petal-a"></span><span class="petal petal-b"></span><span class="petal petal-c"></span></div>
        @endunless
        <div class="relative z-10 mx-auto w-full max-w-7xl px-5 pb-20 pt-36 md:px-12 md:pb-28">
            <p class="mb-6 inline-flex rounded-full border border-white/30 bg-white/10 px-4 py-2 text-[9px] font-semibold uppercase tracking-[.28em] text-white/85 backdrop-blur-md">{{ $wedding->wedding_date->translatedFormat('d. F Y') }} · Private Eventgalerie</p>
            <h1 class="max-w-5xl font-serif text-6xl leading-[.88] tracking-[-.055em] md:text-9xl">{{ $wedding->couple_names }}</h1>
            <p class="mt-7 max-w-2xl text-sm leading-7 text-white/80 md:text-base">{{ $wedding->welcome_text ?: 'Willkommen in unserer gemeinsamen Sammlung der schönsten Augenblicke.' }}</p>
            @if($unlocked)
                <div class="mt-9 flex flex-wrap gap-3"><button type="button" data-open-upload class="rounded-full bg-white px-6 py-3.5 text-sm font-semibold text-[#3f5043] shadow-xl transition hover:-translate-y-0.5">＋ Fotos & Videos teilen</button><a href="#gallery" class="rounded-full border border-white/35 bg-white/10 px-6 py-3.5 text-sm font-semibold text-white backdrop-blur-md">Galerie entdecken ↓</a></div>
            @endif
        </div>
        <div class="absolute bottom-0 right-0 hidden items-center gap-3 border-l border-t border-white/20 bg-[#26352b]/45 px-8 py-5 text-[9px] uppercase tracking-[.2em] text-white/65 backdrop-blur-lg md:flex"><span class="block h-px w-10 bg-white/45"></span> Erinnerungen von allen Gästen</div>
    </section>

    @if(!$unlocked)
        <section class="relative z-20 mx-auto -mt-12 max-w-lg px-5 pb-24">
            <div class="rounded-[2rem] border border-[#ded8ce] bg-white p-7 text-center shadow-[0_25px_80px_rgba(55,65,57,.15)] md:p-11">
                <div class="mx-auto mb-6 grid size-14 place-items-center rounded-full border border-[#aeb8aa] font-serif text-[#5b6a58]">♡</div>
                <p class="mb-2 text-[10px] font-semibold uppercase tracking-[.24em] text-[#7d8978]">Nur für unsere Gäste</p>
                <h2 class="font-serif text-4xl tracking-tight">Schön, dass ihr da seid</h2>
                <p class="mx-auto mt-3 max-w-sm text-sm leading-6 text-[#788078]">Gebt die PIN von eurer Einladung ein, um die Galerie zu öffnen.</p>
                <form method="POST" action="{{ route('weddings.unlock', $wedding) }}" class="mt-7">
                    @csrf
                    <label for="pin" class="sr-only">Hochzeits-PIN</label>
                    <input id="pin" name="pin" value="{{ old('pin') }}" inputmode="numeric" autocomplete="one-time-code" maxlength="10" placeholder="• • • • • •" class="w-full rounded-2xl border border-[#d8d5ce] bg-[#faf7f2] px-4 py-4 text-center text-xl tracking-[.45em] outline-none transition focus:border-[#72806d] focus:ring-4 focus:ring-[#72806d]/10">
                    @error('pin')<p class="mt-3 text-sm text-[#a64f44]">{{ $message }}</p>@enderror
                    <button class="mt-5 w-full rounded-full bg-[#465745] px-6 py-4 text-sm font-semibold text-white shadow-lg shadow-[#465745]/15 transition hover:-translate-y-0.5 hover:bg-[#354535]">Galerie öffnen</button>
                </form>
                <p class="mt-5 text-[10px] text-[#9a9f98]">Der Zugang bleibt auf diesem Gerät gespeichert.</p>
            </div>
        </section>
    @else
        <section class="relative z-20 mx-auto -mt-8 max-w-6xl px-5 md:-mt-10 md:px-12">
            <div class="grid overflow-hidden rounded-3xl border border-white/50 bg-[#fffdf9]/95 shadow-[0_24px_70px_rgba(43,55,46,.14)] backdrop-blur-xl sm:grid-cols-3">
                <div class="flex items-center gap-4 p-5 md:p-6"><span class="grid size-10 shrink-0 place-items-center rounded-full bg-[#e8eee5] text-[#4d5e50]">01</span><div><strong class="block text-xs">Ohne Anmeldung</strong><span class="mt-1 block text-[10px] text-[#858c84]">Name eingeben und loslegen</span></div></div>
                <div class="flex items-center gap-4 border-y border-[#e6e2da] p-5 sm:border-x sm:border-y-0 md:p-6"><span class="grid size-10 shrink-0 place-items-center rounded-full bg-[#e8eee5] text-[#4d5e50]">02</span><div><strong class="block text-xs">Gemeinsam sammeln</strong><span class="mt-1 block text-[10px] text-[#858c84]">Fotos und Videos direkt teilen</span></div></div>
                <div class="flex items-center gap-4 p-5 md:p-6"><span class="grid size-10 shrink-0 place-items-center rounded-full bg-[#e8eee5] text-[#4d5e50]">03</span><div><strong class="block text-xs">Originale behalten</strong><span class="mt-1 block text-[10px] text-[#858c84]">Jeden Moment herunterladen</span></div></div>
            </div>
        </section>

        <section id="gallery" class="mx-auto max-w-7xl scroll-mt-28 px-5 py-20 md:px-12 md:py-28">
            @if(session('success'))<div class="mb-8 rounded-2xl bg-[#e8efe5] px-5 py-4 text-sm text-[#465745]">{{ session('success') }}</div>@endif
            <div class="mb-10 flex flex-col justify-between gap-6 md:flex-row md:items-end">
                <div>
                    <p class="mb-3 text-[10px] font-semibold uppercase tracking-[.26em] text-[#7a8975]">Von euch festgehalten</p>
                    <h2 class="max-w-3xl font-serif text-5xl leading-none tracking-tight md:text-7xl">Ein Tag.<br>Viele Perspektiven.</h2>
                    <p class="mt-3 text-sm text-[#7d847d]">{{ $media->count() }} {{ $media->count() === 1 ? 'Erinnerung' : 'Erinnerungen' }} in der Galerie</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex rounded-full bg-[#f0ece5] p-1" data-filters>
                        <button data-filter="all" class="filter-active rounded-full px-4 py-2 text-xs">Alle</button>
                        <button data-filter="photo" class="rounded-full px-4 py-2 text-xs">Fotos</button>
                        <button data-filter="video" class="rounded-full px-4 py-2 text-xs">Videos</button>
                    </div>
                    <button type="button" data-open-upload class="hidden rounded-full bg-[#465745] px-5 py-3 text-xs font-semibold text-white md:block">＋ Hochladen</button>
                </div>
            </div>

            @if($media->isEmpty())
                <div class="relative overflow-hidden rounded-[2.5rem] bg-[#e8ede7] px-6 py-20 text-center md:py-28">
                    <div class="absolute -left-20 -top-20 size-64 rounded-full border border-[#80907f]/20"></div><div class="absolute -bottom-24 -right-10 size-72 rounded-full bg-[#d9b3a8]/25 blur-2xl"></div>
                    <div class="relative"><div class="mx-auto mb-7 grid size-16 place-items-center rounded-full bg-white text-2xl text-[#536353] shadow-lg">＋</div>
                    <p class="mb-3 text-[10px] font-semibold uppercase tracking-[.24em] text-[#748272]">Die Galerie wartet auf euch</p>
                    <h3 class="font-serif text-4xl tracking-tight md:text-6xl">Teilt den ersten Moment</h3>
                    <p class="mx-auto mt-4 max-w-lg text-sm leading-7 text-[#747e75]">Ob Handyfoto oder kurzer Videoclip – eure Perspektive macht die Erinnerung an diesen Tag vollständig.</p>
                    <button type="button" data-open-upload class="mt-8 rounded-full bg-[#465745] px-7 py-4 text-sm font-semibold text-white shadow-xl shadow-[#465745]/15 transition hover:-translate-y-0.5">Fotos & Videos hochladen</button></div>
                </div>
            @else
                <div class="media-grid" data-gallery>
                    @foreach($media as $item)
                        <article class="media-tile group" data-kind="{{ $item->type }}">
                            <button type="button" class="block w-full overflow-hidden text-left" data-lightbox-src="{{ route('weddings.media.view', [$wedding, $item]) }}" data-lightbox-type="{{ $item->type }}">
                                @if($item->type === 'photo')
                                    <img src="{{ route('weddings.media.thumbnail', [$wedding, $item]) }}" loading="lazy" alt="Hochzeitsmoment" class="h-full min-h-64 w-full object-cover transition duration-500 group-hover:scale-[1.025]">
                                @else
                                    <div class="video-placeholder min-h-64"><span class="grid size-14 place-items-center rounded-full bg-white/20 text-xl backdrop-blur">▶</span><small>{{ gmdate('i:s', $item->video_duration ?? 0) }}</small></div>
                                @endif
                            </button>
                            <div class="flex items-center justify-between px-4 py-3">
                                <span class="text-[11px] text-[#7d847d]">{{ $item->guest_name ?: 'Ein lieber Gast' }}</span>
                                <a href="{{ route('weddings.media.download', [$wedding, $item]) }}" class="text-xs text-[#657161]" aria-label="Original herunterladen">↓ Original</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <button id="mobile-upload-cta" type="button" data-open-upload class="pointer-events-none fixed bottom-5 left-1/2 z-30 -translate-x-1/2 translate-y-24 rounded-full bg-[#465745] px-6 py-4 text-sm font-semibold text-white opacity-0 shadow-2xl transition duration-300 md:hidden">＋ Fotos & Videos</button>

        <div id="upload-modal" class="fixed inset-0 z-50 hidden items-end justify-center bg-[#1f2922]/70 p-0 backdrop-blur-sm md:items-center md:p-6" role="dialog" aria-modal="true" aria-labelledby="upload-title">
            <div class="max-h-[94vh] w-full max-w-3xl overflow-y-auto rounded-t-[2rem] bg-[#fffdf9] p-6 shadow-2xl md:rounded-[2rem] md:p-9">
                <div class="flex items-start justify-between gap-5">
                    <div><p class="mb-2 text-[10px] font-semibold uppercase tracking-[.24em] text-[#7a8975]">Erinnerungen teilen</p><h2 id="upload-title" class="font-serif text-4xl">Eure schönsten Momente</h2></div>
                    <button type="button" data-close-upload class="grid size-10 place-items-center rounded-full bg-[#f0ece5] text-xl">×</button>
                </div>
                <label id="dropzone" class="upload-dropzone mt-7 flex min-h-56 cursor-pointer flex-col items-center justify-center rounded-3xl border-2 border-dashed border-[#aeb9aa] bg-[#f7f2ea] p-6 text-center" tabindex="0">
                    <span class="dropzone-icon grid size-14 place-items-center rounded-full bg-[#465745] text-2xl text-white shadow-lg shadow-[#465745]/20">↑</span>
                    <strong class="mt-5 font-serif text-2xl font-normal">Fotos & Videos hier hineinziehen</strong>
                    <span class="mt-2 text-xs font-semibold text-[#657161]">oder klicken und Dateien auswählen</span>
                    <span class="mt-4 text-[10px] leading-5 text-[#858d84]">JPG, PNG, HEIC, WebP bis {{ $wedding->photo_max_mb }} MB<br>MP4, MOV, WebM bis {{ min($wedding->video_max_mb, 100) }} MB / {{ floor($wedding->video_max_seconds / 60) }} Min.</span>
                    <input id="file-input" type="file" multiple accept=".jpg,.jpeg,.png,.heic,.heif,.webp,.mp4,.mov,.webm" class="hidden">
                </label>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="block text-xs font-semibold text-[#536150]">Euer Name <span class="text-[#a24e44]">*</span><input id="guest-name" maxlength="80" autocomplete="name" required placeholder="z. B. Anna & Paul" class="mt-2 w-full rounded-xl border border-[#d8d5ce] bg-white px-4 py-3 text-sm outline-none focus:border-[#72806d]"></label>
                    <label class="block text-xs font-semibold text-[#536150]">E-Mail-Adresse <span class="font-normal text-[#929891]">(optional)</span><input id="guest-email" type="email" maxlength="254" autocomplete="email" placeholder="anna@beispiel.de" class="mt-2 w-full rounded-xl border border-[#d8d5ce] bg-white px-4 py-3 text-sm outline-none focus:border-[#72806d]"></label>
                </div>
                <p class="mt-3 text-[10px] leading-5 text-[#858d84]">Falls angegeben, dient die E-Mail-Adresse nur der Zuordnung und ist ausschließlich für den geschützten Master-Admin sichtbar.</p>
                <div id="upload-list" class="mt-5 space-y-3"></div>
                <div id="upload-error" class="mt-4 hidden rounded-xl bg-[#f8e9e6] px-4 py-3 text-sm text-[#9f4c42]"></div>
                <button id="start-upload" type="button" disabled class="mt-6 w-full rounded-full bg-[#465745] px-6 py-4 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-40">Upload starten</button>
                <a id="back-gallery" href="#gallery" class="mt-4 hidden w-full rounded-full border border-[#aeb9aa] px-6 py-4 text-center text-sm font-semibold text-[#465745]">Zur Galerie</a>
            </div>
        </div>

        <div id="lightbox" class="fixed inset-0 z-[60] hidden items-center justify-center bg-[#172019]/95 p-5 backdrop-blur-lg" role="dialog" aria-modal="true">
            <button type="button" id="lightbox-close" class="absolute right-5 top-4 text-4xl text-white">×</button>
            <div id="lightbox-content" class="flex max-h-[90vh] max-w-6xl items-center justify-center"></div>
        </div>
    @endif

    <footer class="bg-[#435243] px-6 py-14 text-center text-white"><span class="mx-auto mb-7 inline-flex rounded-2xl bg-white/90 px-5 py-3"><img src="{{ asset('images/blende6-logo.png') }}" alt="Blende 6" class="w-44"></span><p class="font-serif text-2xl">{{ $wedding->couple_names }}</p><p class="mt-2 text-[9px] uppercase tracking-[.22em] text-white/60">Danke, dass ihr diesen Tag mit uns teilt</p></footer>
</main>
@endsection

@if($unlocked)
@push('scripts')
<script>
(() => {
    const modal = document.querySelector('#upload-modal');
    const input = document.querySelector('#file-input');
    const list = document.querySelector('#upload-list');
    const start = document.querySelector('#start-upload');
    const errorBox = document.querySelector('#upload-error');
    const back = document.querySelector('#back-gallery');
    const guestName = document.querySelector('#guest-name');
    const guestEmail = document.querySelector('#guest-email');
    const dropzone = document.querySelector('#dropzone');
    let files = [];
    let dragDepth = 0;
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const limits = { photoCount: {{ $wedding->photo_batch_max }}, videoCount: {{ $wedding->video_batch_max }}, photoBytes: {{ $wedding->photo_max_mb * 1024 * 1024 }}, videoBytes: {{ min($wedding->video_max_mb, 100) * 1024 * 1024 }} };

    document.querySelectorAll('[data-open-upload]').forEach(btn => btn.addEventListener('click', () => { modal.classList.remove('hidden'); modal.classList.add('flex'); }));
    document.querySelector('[data-close-upload]')?.addEventListener('click', () => { if (!start.disabled || !files.some(f => f.uploading)) { modal.classList.add('hidden'); modal.classList.remove('flex'); } });
    modal?.addEventListener('click', e => { if (e.target === modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); } });
    dropzone?.addEventListener('dragenter', e => { e.preventDefault(); dragDepth += 1; dropzone.classList.add('is-dragging'); });
    dropzone?.addEventListener('dragover', e => { e.preventDefault(); e.dataTransfer.dropEffect = 'copy'; });
    dropzone?.addEventListener('dragleave', e => { e.preventDefault(); dragDepth = Math.max(0, dragDepth - 1); if (!dragDepth) dropzone.classList.remove('is-dragging'); });
    dropzone?.addEventListener('drop', e => { e.preventDefault(); dragDepth = 0; dropzone.classList.remove('is-dragging'); addFiles(e.dataTransfer.files); });
    dropzone?.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); input.click(); } });
    input?.addEventListener('change', () => addFiles(input.files));
    guestName?.addEventListener('input', render);
    guestEmail?.addEventListener('input', render);

    const mobileUploadCta = document.querySelector('#mobile-upload-cta');
    const gallery = document.querySelector('#gallery');
    if (mobileUploadCta && gallery) {
        new IntersectionObserver(entries => {
            const show = entries[0].isIntersecting;
            mobileUploadCta.classList.toggle('translate-y-24', !show);
            mobileUploadCta.classList.toggle('opacity-0', !show);
            mobileUploadCta.classList.toggle('pointer-events-none', !show);
        }, { threshold: 0.05 }).observe(gallery);
    }

    function kind(file) { return file.type.startsWith('video/') || /\.(mp4|mov|webm)$/i.test(file.name) ? 'video' : 'photo'; }
    function addFiles(selected) {
        error('');
        const next = files.concat(Array.from(selected).map(file => ({ file, id: crypto.randomUUID(), status: 'ready', progress: 0 })));
        const photos = next.filter(x => kind(x.file) === 'photo');
        const videos = next.filter(x => kind(x.file) === 'video');
        if (photos.length > limits.photoCount || videos.length > limits.videoCount) return error(`Maximal ${limits.photoCount} Fotos und ${limits.videoCount} Videos pro Upload.`);
        const tooLarge = next.find(x => x.file.size > (kind(x.file) === 'photo' ? limits.photoBytes : limits.videoBytes));
        if (tooLarge) return error(`${tooLarge.file.name} ist zu groß.`);
        files = next; input.value = ''; render();
    }
    function remove(id) { files = files.filter(x => x.id !== id); render(); }
    function error(message) { errorBox.textContent = message; errorBox.classList.toggle('hidden', !message); }
    function render() {
        list.innerHTML = '';
        files.forEach(item => {
            const row = document.createElement('div'); row.className = 'upload-row';
            const preview = kind(item.file) === 'photo' && !/\.hei[cf]$/i.test(item.file.name) ? `<img src="${URL.createObjectURL(item.file)}" alt="">` : `<span>${kind(item.file) === 'video' ? '▶' : '◇'}</span>`;
            row.innerHTML = `<div class="upload-preview">${preview}</div><div class="min-w-0 flex-1"><strong>${escapeHtml(item.file.name)}</strong><small>${(item.file.size/1024/1024).toFixed(1)} MB · ${item.status === 'success' ? 'Erfolgreich' : item.status === 'error' ? 'Fehlgeschlagen' : item.status === 'uploading' ? 'Wird hochgeladen' : 'Bereit'}</small><div class="progress-track"><i style="width:${item.progress}%" class="${item.status}"></i></div></div><button type="button" ${item.status === 'uploading' ? 'disabled' : ''} aria-label="Datei entfernen">×</button>`;
            row.querySelector('button').addEventListener('click', () => remove(item.id)); list.appendChild(row);
        });
        start.disabled = !files.length || files.some(x => x.status === 'uploading') || !guestName.value.trim() || !guestName.checkValidity() || !guestEmail.checkValidity();
    }
    function escapeHtml(value) { const div = document.createElement('div'); div.textContent = value; return div.innerHTML; }
    start?.addEventListener('click', async () => {
        if (!guestName.reportValidity()) return error('Bitte gebt euren Namen ein.');
        if (!guestEmail.reportValidity()) return error('Bitte prüft die E-Mail-Adresse oder lasst das Feld leer.');
        error(''); start.disabled = true; back.classList.add('hidden'); const batch = crypto.randomUUID();
        for (const item of files.filter(x => x.status !== 'success')) await upload(item, batch);
        start.classList.add('hidden'); back.classList.remove('hidden'); back.addEventListener('click', () => location.reload(), { once: true });
    });
    function upload(item, batch) { return new Promise(resolve => {
        item.status = 'uploading'; render(); const data = new FormData(); data.append('file', item.file); data.append('batch_id', batch); data.append('guest_name', document.querySelector('#guest-name').value); data.append('guest_email', guestEmail.value.trim());
        const xhr = new XMLHttpRequest(); xhr.open('POST', @json(route('weddings.upload', $wedding))); xhr.setRequestHeader('X-CSRF-TOKEN', csrf); xhr.setRequestHeader('Accept', 'application/json');
        xhr.upload.onprogress = e => { if (e.lengthComputable) { item.progress = Math.round(e.loaded/e.total*100); render(); } };
        xhr.onload = () => { item.status = xhr.status >= 200 && xhr.status < 300 ? 'success' : 'error'; item.progress = item.status === 'success' ? 100 : item.progress; if (item.status === 'error') { try { const body = JSON.parse(xhr.responseText); error(body.message || Object.values(body.errors || {})[0]?.[0] || 'Upload fehlgeschlagen.'); } catch { error('Upload fehlgeschlagen.'); } } render(); resolve(); };
        xhr.onerror = () => { item.status = 'error'; error('Netzwerkfehler beim Upload.'); render(); resolve(); }; xhr.send(data);
    }); }

    document.querySelectorAll('[data-filter]').forEach(btn => btn.addEventListener('click', () => { document.querySelectorAll('[data-filter]').forEach(x => x.classList.toggle('filter-active', x === btn)); document.querySelectorAll('[data-kind]').forEach(tile => tile.classList.toggle('hidden', btn.dataset.filter !== 'all' && tile.dataset.kind !== btn.dataset.filter)); }));
    const lightbox = document.querySelector('#lightbox'), content = document.querySelector('#lightbox-content');
    document.querySelectorAll('[data-lightbox-src]').forEach(btn => btn.addEventListener('click', () => { content.innerHTML = btn.dataset.lightboxType === 'photo' ? `<img src="${btn.dataset.lightboxSrc}" alt="Hochzeitsmoment" class="max-h-[86vh] max-w-full rounded-xl object-contain">` : `<video src="${btn.dataset.lightboxSrc}" controls autoplay playsinline class="max-h-[86vh] max-w-full rounded-xl"></video>`; lightbox.classList.remove('hidden'); lightbox.classList.add('flex'); }));
    function closeLightbox(){ lightbox.classList.add('hidden'); lightbox.classList.remove('flex'); content.innerHTML=''; }
    document.querySelector('#lightbox-close')?.addEventListener('click', closeLightbox); lightbox?.addEventListener('click', e => { if(e.target===lightbox) closeLightbox(); });
    document.addEventListener('keydown', e => { if(e.key==='Escape'){ closeLightbox(); modal?.classList.add('hidden'); modal?.classList.remove('flex'); } });
})();
</script>
@endpush
@endif
