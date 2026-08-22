@php
    $title = $wedding->couple_names.' – Fotos & Videos teilen';
    $lightboxItems = $media->map(function ($item) use ($wedding) {
        return [
            'id' => $item->id,
            'type' => $item->type,
            'guest' => $item->guest_name ?: 'Ein lieber Gast',
            'src' => route('weddings.media.view', [$wedding, $item]),
            'thumb' => $item->type === 'photo' ? route('weddings.media.thumbnail', [$wedding, $item]) : null,
            'download' => route('weddings.media.download', [$wedding, $item]),
        ];
    })->values();
@endphp
@extends('layouts.app')

@section('content')
<main class="min-h-screen bg-[#f7f3ed] text-[#2f3932]">
    <header class="border-b border-[#e3ded6] bg-[#fffdf9]">
        <div class="mx-auto flex h-24 max-w-7xl items-center justify-between gap-4 px-5 md:px-12">
            <a href="{{ route('weddings.show', $wedding) }}" class="inline-flex items-center gap-4" aria-label="Blende 6 – {{ $wedding->couple_names }}">
                <img src="{{ asset('images/blende6-logo.png') }}" alt="Blende 6" class="w-36 sm:w-48">
                <span class="hidden border-l border-[#b8bdb6] pl-4 sm:block"><strong class="block font-serif text-xl font-normal">{{ $wedding->couple_names }}</strong><small class="mt-0.5 block text-[9px] uppercase tracking-[.18em] text-[#818981]">{{ $wedding->wedding_date->translatedFormat('d. F Y') }}</small></span>
            </a>
            @if($unlocked)
                <nav class="flex items-center gap-2 text-xs font-semibold"><a href="#qr-code" class="rounded-full border border-[#b7beb4] px-4 py-2.5 text-[#536150]">QR-Code</a><a href="#albums" class="hidden rounded-full bg-[#465745] px-4 py-2.5 text-white sm:inline-flex">Alben ↓</a></nav>
            @endif
        </div>
    </header>

    @if(!$unlocked)
        <section class="mx-auto max-w-lg px-5 py-16 md:py-24">
            <div class="rounded-[2rem] border border-[#ded8ce] bg-white p-7 text-center shadow-[0_25px_80px_rgba(55,65,57,.15)] md:p-11">
                <div class="mx-auto mb-6 grid size-14 place-items-center rounded-full border border-[#aeb8aa] font-serif text-[#5b6a58]">♡</div>
                <p class="mb-2 text-[10px] font-semibold uppercase tracking-[.24em] text-[#7d8978]">Nur für unsere Gäste</p>
                <h2 class="font-serif text-4xl tracking-tight">Album öffnen</h2>
                <p class="mx-auto mt-3 max-w-sm text-sm leading-6 text-[#788078]">Gebt die PIN von eurer Einladung ein. Der QR-Code auf der Einladung öffnet diese Seite direkt.</p>
                <form method="POST" action="{{ route('weddings.unlock', $wedding) }}" class="mt-7">
                    @csrf
                    <label for="pin" class="sr-only">Hochzeits-PIN</label>
                    <input id="pin" name="pin" value="{{ old('pin') }}" inputmode="numeric" autocomplete="one-time-code" maxlength="10" placeholder="• • • • • •" class="w-full rounded-2xl border border-[#d8d5ce] bg-[#faf7f2] px-4 py-4 text-center text-xl tracking-[.45em] outline-none transition focus:border-[#72806d] focus:ring-4 focus:ring-[#72806d]/10">
                    @error('pin')<p class="mt-3 text-sm text-[#a64f44]">{{ $message }}</p>@enderror
                    <button class="mt-5 w-full rounded-full bg-[#465745] px-6 py-4 text-sm font-semibold text-white">Galerie öffnen</button>
                </form>
            </div>
        </section>
    @else
        <section id="upload" class="mx-auto grid max-w-7xl scroll-mt-8 gap-6 px-5 py-8 md:px-12 md:py-12 xl:grid-cols-[minmax(0,1fr)_340px]">
            @if(session('success'))<div class="rounded-2xl bg-[#e8efe5] px-5 py-4 text-sm text-[#465745] xl:col-span-2">{{ session('success') }}</div>@endif
            <div class="rounded-[2rem] border border-[#ded8ce] bg-[#fffdf9] p-6 shadow-[0_20px_60px_rgba(55,65,57,.08)] md:p-9">
                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                    <div><p class="mb-2 text-[10px] font-semibold uppercase tracking-[.24em] text-[#7a8975]">Direkter Gäste-Upload</p><h1 class="font-serif text-4xl tracking-tight md:text-5xl">Fotos & Videos hochladen</h1></div>
                    <span class="rounded-full bg-[#e8eee5] px-4 py-2 text-[10px] font-semibold text-[#536150]">Ohne Anmeldung</span>
                </div>
                <label id="dropzone" class="upload-dropzone mt-7 flex min-h-64 cursor-pointer flex-col items-center justify-center rounded-3xl border-2 border-dashed border-[#aeb9aa] bg-[#f7f2ea] p-6 text-center" tabindex="0">
                    <span class="dropzone-icon grid size-14 place-items-center rounded-full bg-[#465745] text-2xl text-white shadow-lg shadow-[#465745]/20">↑</span>
                    <strong class="mt-5 font-serif text-2xl font-normal md:text-3xl">Fotos & Videos hier hineinziehen</strong>
                    <span class="mt-2 text-xs font-semibold text-[#657161]">oder klicken und Dateien auswählen</span>
                    <span class="mt-4 text-[10px] leading-5 text-[#858d84]">Fotos bis {{ $wedding->photo_max_mb }} MB · Videos bis {{ min($wedding->video_max_mb, 100) }} MB / {{ floor($wedding->video_max_seconds / 60) }} Min.</span>
                    <input id="file-input" type="file" multiple accept=".jpg,.jpeg,.png,.heic,.heif,.webp,.mp4,.mov,.webm" class="hidden">
                </label>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="block text-xs font-semibold text-[#536150]">Euer Name <span class="text-[#a24e44]">*</span><input id="guest-name" maxlength="80" autocomplete="name" required placeholder="z. B. Chris" class="mt-2 w-full rounded-xl border border-[#d8d5ce] bg-white px-4 py-3 text-sm outline-none focus:border-[#72806d]"></label>
                    <label class="block text-xs font-semibold text-[#536150]">E-Mail-Adresse <span class="font-normal text-[#929891]">(optional)</span><input id="guest-email" type="email" maxlength="254" autocomplete="email" placeholder="chris@beispiel.de" class="mt-2 w-full rounded-xl border border-[#d8d5ce] bg-white px-4 py-3 text-sm outline-none focus:border-[#72806d]"></label>
                </div>
                <p class="mt-3 text-[10px] leading-5 text-[#858d84]">Die E-Mail-Adresse ist optional und ausschließlich für den geschützten Master-Admin sichtbar.</p>
                <div id="upload-list" class="mt-5 space-y-3"></div>
                <div id="upload-error" class="mt-4 hidden rounded-xl bg-[#f8e9e6] px-4 py-3 text-sm text-[#9f4c42]"></div>
                <button id="start-upload" type="button" class="mt-6 w-full rounded-full bg-[#465745] px-6 py-4 text-sm font-semibold text-white shadow-lg shadow-[#465745]/15 transition hover:bg-[#354535]">Dateien auswählen</button>
                <p id="start-help" class="mt-3 text-center text-[10px] text-[#858d84]">Der Upload startet erst, nachdem ihr mindestens eine Datei ausgewählt habt.</p>
                <a id="back-gallery" href="#albums" class="mt-4 hidden w-full rounded-full border border-[#aeb9aa] px-6 py-4 text-center text-sm font-semibold text-[#465745]">Upload ansehen</a>
            </div>

            <aside id="qr-code" class="scroll-mt-8 rounded-[2rem] bg-[#465745] p-7 text-center text-white shadow-[0_20px_60px_rgba(55,65,57,.14)] xl:sticky xl:top-6 xl:self-start">
                <p class="text-[10px] font-semibold uppercase tracking-[.24em] text-white/65">QR-Code für dieses Event</p>
                <h2 class="mt-3 font-serif text-3xl">{{ $wedding->couple_names }}</h2>
                <div class="mx-auto mt-6 max-w-64 rounded-2xl bg-white p-4"><img src="{{ route('weddings.qr', $wedding) }}" alt="QR-Code für {{ $wedding->couple_names }}" class="w-full"></div>
                <p class="mt-5 text-xs leading-6 text-white/70">Gäste scannen den Code und gelangen direkt auf diese Upload-Seite – ohne zusätzliche PIN-Eingabe.</p>
                <a href="{{ route('weddings.qr.download', $wedding) }}" class="mt-6 inline-flex w-full justify-center rounded-full bg-white px-5 py-3.5 text-sm font-semibold text-[#465745]">↓ QR-Code herunterladen</a>
            </aside>
        </section>

        <section id="albums" class="scroll-mt-10 border-t border-[#e1dcd4] bg-[#f1ede6] px-5 py-14 md:px-12 md:py-20">
            <div class="mx-auto max-w-7xl">
                <div class="mb-12 flex flex-col justify-between gap-6 md:flex-row md:items-end">
                    <div><p class="mb-3 text-[10px] font-semibold uppercase tracking-[.26em] text-[#7a8975]">Übersicht</p><h2 class="font-serif text-5xl leading-none tracking-tight md:text-7xl">Alben</h2><p class="mt-4 text-sm text-[#7d847d]">{{ $albums->count() }} {{ $albums->count() === 1 ? 'Album' : 'Alben' }} · {{ $media->count() }} Dateien</p></div>
                    @if($media->isNotEmpty())<a href="{{ route('weddings.archive.download', $wedding) }}" class="inline-flex justify-center rounded-full bg-[#465745] px-6 py-3.5 text-sm font-semibold text-white">↓ Alle Alben als ZIP</a>@endif
                </div>

                @forelse($albums as $album)
                    <details data-album-details class="group mb-5 overflow-hidden rounded-[2rem] border border-[#ded8ce] bg-[#fffdf9] shadow-[0_14px_45px_rgba(55,65,57,.06)]">
                        <summary class="flex cursor-pointer list-none flex-col justify-between gap-4 p-5 marker:hidden sm:flex-row sm:items-center md:p-7 [&::-webkit-details-marker]:hidden">
                            <div class="flex items-center gap-4"><span class="grid size-12 place-items-center rounded-full bg-[#e8eee5] font-serif text-xl text-[#4d5e50]">{{ mb_strtoupper(mb_substr($album->name, 0, 1)) }}</span><div><p class="text-[9px] font-semibold uppercase tracking-[.22em] text-[#879087]">Album</p><h3 class="mt-1 font-serif text-3xl">{{ $album->name }}</h3><p class="mt-1 text-[10px] text-[#858c84]">{{ $album->photos }} Fotos · {{ $album->videos }} Videos</p></div></div>
                            <div class="flex items-center gap-2"><a href="{{ route('weddings.guest-album.download', ['wedding' => $wedding, 'guest' => $album->name]) }}" onclick="event.stopPropagation()" class="rounded-full border border-[#abb5a8] px-5 py-3 text-center text-xs font-semibold text-[#50604f]">↓ ZIP laden</a><span class="grid size-11 place-items-center rounded-full bg-[#e8eee5] text-xl text-[#4d5e50] transition group-open:rotate-180">⌄</span></div>
                        </summary>
                        <div class="border-t border-[#e5dfd6] p-5 md:p-7">
                            <div class="media-grid" data-gallery>
                            @foreach($album->media as $item)
                                <article class="media-tile group">
                                    <button type="button" class="block w-full overflow-hidden text-left" data-lightbox-id="{{ $item->id }}">
                                        @if($item->type === 'photo')
                                            <img data-src="{{ route('weddings.media.thumbnail', [$wedding, $item]) }}" loading="lazy" decoding="async" alt="Bild aus dem Album {{ $album->name }}" class="h-full min-h-64 w-full bg-[#e8eee5] object-cover transition duration-500 group-hover:scale-[1.025]">
                                        @else
                                            <div class="video-placeholder min-h-64"><span class="grid size-14 place-items-center rounded-full bg-white/20 text-xl backdrop-blur">▶</span><small>{{ gmdate('i:s', $item->video_duration ?? 0) }}</small></div>
                                        @endif
                                    </button>
                                    <div class="flex items-center justify-between px-4 py-3"><span class="text-[11px] text-[#7d847d]">{{ $item->type === 'photo' ? 'Foto' : 'Video' }}</span><a href="{{ route('weddings.media.download', [$wedding, $item]) }}" class="text-xs font-semibold text-[#657161]">↓ Original</a></div>
                                </article>
                            @endforeach
                            </div>
                        </div>
                    </details>
                @empty
                    <div class="relative overflow-hidden rounded-[2.5rem] bg-[#e3e9e1] px-6 py-20 text-center md:py-28">
                        <div class="relative"><div class="mx-auto mb-7 grid size-16 place-items-center rounded-full bg-white text-2xl text-[#536353] shadow-lg">＋</div><p class="mb-3 text-[10px] font-semibold uppercase tracking-[.24em] text-[#748272]">Noch kein Gäste-Album</p><h3 class="font-serif text-4xl tracking-tight md:text-6xl">Teilt den ersten Moment</h3><p class="mx-auto mt-4 max-w-lg text-sm leading-7 text-[#747e75]">Nach dem ersten Upload erscheint hier automatisch ein persönliches Album unter dem eingegebenen Namen.</p><a href="#upload" class="mt-8 inline-flex rounded-full bg-[#465745] px-7 py-4 text-sm font-semibold text-white">Jetzt Dateien auswählen</a></div>
                    </div>
                @endforelse
            </div>
        </section>

        <div id="lightbox" class="fixed inset-0 z-[60] hidden bg-[#172019]/96 p-4 text-white backdrop-blur-lg md:p-7" role="dialog" aria-modal="true" aria-labelledby="lightbox-title">
            <button type="button" id="lightbox-close" class="absolute right-4 top-4 z-20 grid size-11 place-items-center rounded-full bg-white/15 text-3xl backdrop-blur md:right-7 md:top-6">×</button>
            <div class="mx-auto flex h-full max-w-7xl flex-col">
                <div class="flex min-h-0 flex-1 items-center gap-3 md:gap-6">
                    <button type="button" id="lightbox-prev" class="grid size-11 shrink-0 place-items-center rounded-full bg-white/12 text-2xl">‹</button>
                    <div id="lightbox-content" class="flex min-h-0 flex-1 items-center justify-center"></div>
                    <button type="button" id="lightbox-next" class="grid size-11 shrink-0 place-items-center rounded-full bg-white/12 text-2xl">›</button>
                </div>
                <div class="mx-auto mt-4 w-full max-w-5xl">
                    <div class="flex items-center justify-between gap-4"><div><p id="lightbox-title" class="font-serif text-2xl"></p><p id="lightbox-count" class="mt-1 text-[10px] text-white/55"></p></div><a id="lightbox-download" href="#" class="rounded-full bg-white px-4 py-2.5 text-xs font-semibold text-[#465745]">↓ Original</a></div>
                    <div id="lightbox-thumbs" class="mt-4 flex gap-2 overflow-x-auto pb-1"></div>
                </div>
            </div>
        </div>
    @endif

    <footer class="bg-[#435243] px-6 py-12 text-center text-white"><span class="mx-auto mb-6 inline-flex rounded-2xl bg-white/90 px-5 py-3"><img src="{{ asset('images/blende6-logo.png') }}" alt="Blende 6" class="w-44"></span><p class="font-serif text-2xl">{{ $wedding->couple_names }}</p><p class="mt-2 text-[9px] uppercase tracking-[.22em] text-white/60">Ein eigener Link und QR-Code für jede Hochzeit</p></footer>
</main>
@endsection

@if($unlocked)
@push('scripts')
<script>
(() => {
    const input = document.querySelector('#file-input');
    const list = document.querySelector('#upload-list');
    const start = document.querySelector('#start-upload');
    const startHelp = document.querySelector('#start-help');
    const errorBox = document.querySelector('#upload-error');
    const back = document.querySelector('#back-gallery');
    const guestName = document.querySelector('#guest-name');
    const guestEmail = document.querySelector('#guest-email');
    const dropzone = document.querySelector('#dropzone');
    let files = [];
    let dragDepth = 0;
    let uploading = false;
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const limits = { photoCount: {{ $wedding->photo_batch_max }}, videoCount: {{ $wedding->video_batch_max }}, photoBytes: {{ $wedding->photo_max_mb * 1024 * 1024 }}, videoBytes: {{ min($wedding->video_max_mb, 100) * 1024 * 1024 }} };

    dropzone?.addEventListener('dragenter', event => { event.preventDefault(); dragDepth += 1; dropzone.classList.add('is-dragging'); });
    dropzone?.addEventListener('dragover', event => { event.preventDefault(); event.dataTransfer.dropEffect = 'copy'; });
    dropzone?.addEventListener('dragleave', event => { event.preventDefault(); dragDepth = Math.max(0, dragDepth - 1); if (!dragDepth) dropzone.classList.remove('is-dragging'); });
    dropzone?.addEventListener('drop', event => { event.preventDefault(); dragDepth = 0; dropzone.classList.remove('is-dragging'); addFiles(event.dataTransfer.files); });
    dropzone?.addEventListener('keydown', event => { if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); input.click(); } });
    input?.addEventListener('change', () => addFiles(input.files));
    guestName?.addEventListener('input', render);
    guestEmail?.addEventListener('input', render);

    function kind(file) { return file.type.startsWith('video/') || /\.(mp4|mov|webm)$/i.test(file.name) ? 'video' : 'photo'; }
    function addFiles(selected) {
        error('');
        const next = files.concat(Array.from(selected).map(file => ({ file, id: crypto.randomUUID(), status: 'ready', progress: 0 })));
        const photos = next.filter(item => kind(item.file) === 'photo');
        const videos = next.filter(item => kind(item.file) === 'video');
        if (photos.length > limits.photoCount || videos.length > limits.videoCount) return error(`Maximal ${limits.photoCount} Fotos und ${limits.videoCount} Videos pro Upload.`);
        const tooLarge = next.find(item => item.file.size > (kind(item.file) === 'photo' ? limits.photoBytes : limits.videoBytes));
        if (tooLarge) return error(`${tooLarge.file.name} ist zu groß.`);
        files = next;
        input.value = '';
        render();
    }
    function remove(id) { files = files.filter(item => item.id !== id); render(); }
    function error(message) { errorBox.textContent = message; errorBox.classList.toggle('hidden', !message); }
    function escapeHtml(value) { const div = document.createElement('div'); div.textContent = value; return div.innerHTML; }
    function render() {
        list.innerHTML = '';
        files.forEach(item => {
            const row = document.createElement('div');
            row.className = 'upload-row';
            const preview = kind(item.file) === 'photo' && !/\.hei[cf]$/i.test(item.file.name) ? `<img src="${URL.createObjectURL(item.file)}" alt="">` : `<span>${kind(item.file) === 'video' ? '▶' : '◇'}</span>`;
            row.innerHTML = `<div class="upload-preview">${preview}</div><div class="min-w-0 flex-1"><strong>${escapeHtml(item.file.name)}</strong><small>${(item.file.size / 1024 / 1024).toFixed(1)} MB · ${item.status === 'success' ? 'Erfolgreich' : item.status === 'error' ? 'Fehlgeschlagen' : item.status === 'uploading' ? 'Wird hochgeladen' : 'Bereit'}</small><div class="progress-track"><i style="width:${item.progress}%" class="${item.status}"></i></div></div><button type="button" ${item.status === 'uploading' ? 'disabled' : ''} aria-label="Datei entfernen">×</button>`;
            row.querySelector('button').addEventListener('click', () => remove(item.id));
            list.appendChild(row);
        });
        start.disabled = uploading;
        start.textContent = uploading ? 'Upload läuft …' : files.length ? `${files.length} ${files.length === 1 ? 'Datei' : 'Dateien'} hochladen` : 'Dateien auswählen';
        startHelp.textContent = files.length ? 'Bereit zum Hochladen. Name prüfen und Upload starten.' : 'Der Upload startet erst, nachdem ihr mindestens eine Datei ausgewählt habt.';
    }
    start?.addEventListener('click', async () => {
        if (!files.length) { input.click(); return; }
        if (!guestName.reportValidity()) return error('Bitte gebt euren Namen ein. So entsteht euer persönliches Gäste-Album.');
        if (!guestEmail.reportValidity()) return error('Bitte prüft die E-Mail-Adresse oder lasst das Feld leer.');
        error('');
        uploading = true;
        back.classList.add('hidden');
        render();
        const batch = crypto.randomUUID();
        for (const item of files.filter(value => value.status !== 'success')) await upload(item, batch);
        uploading = false;
        start.classList.add('hidden');
        startHelp.textContent = 'Fertig – eure Dateien sind jetzt im Gäste-Album sichtbar.';
        back.classList.remove('hidden');
        back.addEventListener('click', () => location.reload(), { once: true });
    });
    function upload(item, batch) { return new Promise(resolve => {
        item.status = 'uploading'; render();
        const data = new FormData();
        data.append('file', item.file); data.append('batch_id', batch); data.append('guest_name', guestName.value.trim()); data.append('guest_email', guestEmail.value.trim());
        const xhr = new XMLHttpRequest();
        xhr.open('POST', @json(route('weddings.upload', $wedding)));
        xhr.setRequestHeader('X-CSRF-TOKEN', csrf); xhr.setRequestHeader('Accept', 'application/json');
        xhr.upload.onprogress = event => { if (event.lengthComputable) { item.progress = Math.round(event.loaded / event.total * 100); render(); } };
        xhr.onload = () => { item.status = xhr.status >= 200 && xhr.status < 300 ? 'success' : 'error'; item.progress = item.status === 'success' ? 100 : item.progress; if (item.status === 'error') { try { const body = JSON.parse(xhr.responseText); error(body.message || Object.values(body.errors || {})[0]?.[0] || 'Upload fehlgeschlagen.'); } catch { error('Upload fehlgeschlagen.'); } } render(); resolve(); };
        xhr.onerror = () => { item.status = 'error'; error('Netzwerkfehler beim Upload.'); render(); resolve(); };
        xhr.send(data);
    }); }

    document.querySelectorAll('[data-album-details]').forEach(album => album.addEventListener('toggle', () => {
        if (!album.open) return;
        album.querySelectorAll('img[data-src]').forEach(image => { image.src = image.dataset.src; image.removeAttribute('data-src'); });
    }));

    const lightboxItems = @js($lightboxItems);
    let activeLightboxItems = lightboxItems;
    const lightbox = document.querySelector('#lightbox');
    const content = document.querySelector('#lightbox-content');
    const thumbs = document.querySelector('#lightbox-thumbs');
    const title = document.querySelector('#lightbox-title');
    const count = document.querySelector('#lightbox-count');
    const download = document.querySelector('#lightbox-download');
    let current = 0;
    function showLightbox(index) {
        if (!activeLightboxItems.length) return;
        current = (index + activeLightboxItems.length) % activeLightboxItems.length;
        const item = activeLightboxItems[current];
        content.innerHTML = item.type === 'photo' ? `<img src="${item.src}" alt="Hochzeitsmoment" class="max-h-[66vh] max-w-full rounded-xl object-contain shadow-2xl">` : `<video src="${item.src}" controls autoplay playsinline class="max-h-[66vh] max-w-full rounded-xl shadow-2xl"></video>`;
        title.textContent = item.guest;
        count.textContent = `${current + 1} von ${activeLightboxItems.length}`;
        download.href = item.download;
        thumbs.innerHTML = activeLightboxItems.map((value, indexValue) => `<button type="button" data-thumb-index="${indexValue}" class="h-16 w-16 shrink-0 overflow-hidden rounded-lg border-2 ${indexValue === current ? 'border-white' : 'border-transparent opacity-55'}">${value.thumb ? `<img src="${value.thumb}" alt="" class="size-full object-cover">` : '<span class="grid size-full place-items-center bg-[#536353]">▶</span>'}</button>`).join('');
        thumbs.querySelectorAll('[data-thumb-index]').forEach(button => button.addEventListener('click', () => showLightbox(Number(button.dataset.thumbIndex))));
        lightbox.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeLightbox() { lightbox.classList.add('hidden'); content.innerHTML = ''; document.body.style.overflow = ''; }
    document.querySelectorAll('[data-lightbox-id]').forEach(button => button.addEventListener('click', () => {
        const selected = lightboxItems.find(item => String(item.id) === button.dataset.lightboxId);
        activeLightboxItems = lightboxItems.filter(item => item.guest.toLocaleLowerCase() === selected.guest.toLocaleLowerCase());
        showLightbox(activeLightboxItems.findIndex(item => String(item.id) === button.dataset.lightboxId));
    }));
    document.querySelector('#lightbox-close')?.addEventListener('click', closeLightbox);
    document.querySelector('#lightbox-prev')?.addEventListener('click', () => showLightbox(current - 1));
    document.querySelector('#lightbox-next')?.addEventListener('click', () => showLightbox(current + 1));
    document.addEventListener('keydown', event => { if (lightbox.classList.contains('hidden')) return; if (event.key === 'Escape') closeLightbox(); if (event.key === 'ArrowLeft') showLightbox(current - 1); if (event.key === 'ArrowRight') showLightbox(current + 1); });
})();
</script>
@endpush
@endif
