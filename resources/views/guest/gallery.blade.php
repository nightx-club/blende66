@php
    $title = 'Blende6 – Fotos & Videos hochladen';
    $description = 'Die geschützte Blende6 Galerie für Fotos und Videos.';
    $lightboxItems = $media->where('type', 'photo')->map(function ($item) use ($wedding) {
        return [
            'id' => $item->id,
            'guest' => $item->guest_name ?: 'Blende6',
            'src' => route('weddings.media.view', [$wedding, $item]),
            'thumb' => route('weddings.media.thumbnail', [$wedding, $item]),
            'download' => route('weddings.media.download', [$wedding, $item]),
        ];
    })->values();
@endphp
@extends('layouts.app')

@section('content')
<main class="min-h-screen bg-[#f7f3ed] text-[#2f3932]">
    @include('partials.public-navigation')

    <section id="upload-bereich" class="mx-auto grid max-w-7xl gap-6 px-5 py-10 md:px-12 md:py-14 xl:grid-cols-[minmax(0,1fr)_340px]">
            <div class="rounded-[2rem] border border-[#ded8ce] bg-[#fffdf9] p-6 shadow-[0_20px_60px_rgba(55,65,57,.08)] md:p-9">
                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                    <div><p class="mb-2 text-[10px] font-semibold uppercase tracking-[.24em] text-[#7a8975]">Direkter Upload</p><h2 class="font-serif text-4xl tracking-tight md:text-5xl">Fotos & Videos hochladen</h2></div>
                    <span class="rounded-full bg-[#e8eee5] px-4 py-2 text-[10px] font-semibold text-[#536150]">Sofort sichtbar</span>
                </div>
                <label id="dropzone" class="upload-dropzone mt-7 flex min-h-64 cursor-pointer flex-col items-center justify-center rounded-3xl border-2 border-dashed border-[#aeb9aa] bg-[#f7f2ea] p-6 text-center" tabindex="0">
                    <span class="dropzone-icon grid size-14 place-items-center rounded-full bg-[#465745] text-2xl text-white shadow-lg shadow-[#465745]/20">↑</span>
                    <strong class="mt-5 font-serif text-2xl font-normal md:text-3xl">Fotos & Videos hier hineinziehen</strong>
                    <span class="mt-2 text-xs font-semibold text-[#657161]">oder klicken und Dateien auswählen</span>
                    <span class="mt-4 text-[10px] leading-5 text-[#858d84]">Fotos bis {{ $wedding->photo_max_mb }} MB · Videos bis {{ min($wedding->video_max_mb, 100) }} MB / {{ floor($wedding->video_max_seconds / 60) }} Min.</span>
                    <input id="file-input" type="file" multiple accept=".jpg,.jpeg,.png,.heic,.heif,.webp,.mp4,.mov,.webm" class="hidden">
                </label>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="block text-xs font-semibold text-[#536150]">Name <span class="text-[#a24e44]">*</span><input id="guest-name" maxlength="80" autocomplete="name" required placeholder="z. B. Alex" class="mt-2 w-full rounded-xl border border-[#d8d5ce] bg-white px-4 py-3 text-sm outline-none focus:border-[#72806d]"></label>
                    <label class="block text-xs font-semibold text-[#536150]">E-Mail-Adresse <span class="font-normal text-[#929891]">(optional)</span><input id="guest-email" type="email" maxlength="254" autocomplete="email" placeholder="alex@beispiel.de" class="mt-2 w-full rounded-xl border border-[#d8d5ce] bg-white px-4 py-3 text-sm outline-none focus:border-[#72806d]"></label>
                </div>
                <p class="mt-3 text-[10px] leading-5 text-[#858d84]">Die E-Mail-Adresse ist optional und ausschließlich für den geschützten Admin sichtbar.</p>
                <div id="upload-list" class="mt-5 space-y-3"></div>
                <div id="upload-error" class="mt-4 hidden rounded-xl bg-[#f8e9e6] px-4 py-3 text-sm text-[#9f4c42]"></div>
                <button id="start-upload" type="button" class="mt-6 w-full rounded-full bg-[#465745] px-6 py-4 text-sm font-semibold text-white shadow-lg shadow-[#465745]/15 transition hover:bg-[#354535]">Dateien auswählen</button>
                <p id="start-help" class="mt-3 text-center text-[10px] text-[#858d84]">Wählt mindestens eine Datei aus. Nach dem Upload geht es automatisch zurück zur Galerie.</p>
            </div>

            <aside id="qr-code" class="scroll-mt-4 rounded-[2rem] bg-[#465745] p-7 text-center text-white shadow-[0_20px_60px_rgba(55,65,57,.14)] xl:sticky xl:top-6 xl:self-start">
                <p class="text-[10px] font-semibold uppercase tracking-[.24em] text-white/65">Blende6 Galerie</p>
                <h2 class="mt-3 font-serif text-3xl">QR-Code</h2>
                <div class="mx-auto mt-6 max-w-64 rounded-2xl bg-white p-4"><img src="{{ route('weddings.qr', $wedding) }}" loading="lazy" alt="QR-Code für die Blende6 Galerie" class="w-full"></div>
                <p class="mt-5 text-xs leading-6 text-white/70">Der QR-Code öffnet diese Galerie direkt mit Upload- und Download-Zugang.</p>
                <a href="{{ route('weddings.qr.download', $wedding) }}" class="mt-6 inline-flex w-full justify-center rounded-full bg-white px-5 py-3.5 text-sm font-semibold text-[#465745]">↓ QR-Code herunterladen</a>
            </aside>
    </section>

        <section id="galerie" class="scroll-mt-24 border-t border-[#e3ded6] bg-[#f1ede6] px-5 py-10 md:px-12 md:py-14">
            <div class="mx-auto max-w-7xl">
                @if(request()->string('upload')->toString() === 'success')
                    <div class="mb-7 rounded-2xl bg-[#e3eee1] px-5 py-4 text-sm font-semibold text-[#405440]" role="status">Upload erfolgreich. Eure neuen Fotos und Videos sind jetzt in der Galerie sichtbar.</div>
                @endif

                @if($media->isEmpty())
                    <p class="py-3 text-center text-xs text-[#858d84]">Noch keine Fotos oder Videos hochgeladen.</p>
                @else
                    <div class="mb-7 flex flex-col gap-4 rounded-2xl border border-[#d9d5ce] bg-[#fffdf9] p-4 sm:p-5 lg:flex-row lg:items-center lg:justify-between">
                        <p class="text-xs text-[#7e867e]">{{ $mediaPage->total() }} {{ $mediaPage->total() === 1 ? 'Aufnahme' : 'Aufnahmen' }}</p>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('weddings.archive.download', $wedding) }}" class="rounded-full bg-[#465745] px-4 py-2.5 text-xs font-semibold text-white">↓ Alle als ZIP herunterladen</a>
                            @foreach($guestGalleries as $guestGallery)
                                <a href="{{ route('weddings.guest-album.download', ['wedding' => $wedding, 'guest' => $guestGallery->name]) }}" class="rounded-full bg-[#e8eee5] px-4 py-2.5 text-xs font-semibold text-[#50604f]">↓ {{ $guestGallery->name }} als ZIP <span class="ml-1 opacity-55">{{ $guestGallery->files_count }}</span></a>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" data-gallery-grid>
                        @foreach($media as $item)
                            <article data-media-guest="{{ $item->guest_name ?: 'Blende6' }}" class="group overflow-hidden rounded-2xl border border-[#ded8ce] bg-[#fffdf9] shadow-[0_12px_34px_rgba(55,65,57,.06)]">
                                @if($item->type === 'photo')
                                    <button type="button" class="block w-full overflow-hidden" data-lightbox-id="{{ $item->id }}" aria-label="Foto groß öffnen">
                                        <img src="{{ route('weddings.media.thumbnail', [$wedding, $item]) }}" loading="lazy" decoding="async" alt="Foto in der Blende6 Galerie" class="aspect-[4/3] w-full bg-[#e4e9e2] object-cover transition duration-500 group-hover:scale-[1.025]">
                                    </button>
                                @else
                                    <video src="{{ route('weddings.media.view', [$wedding, $item]) }}" controls preload="metadata" playsinline class="aspect-[4/3] w-full bg-[#263229] object-contain" aria-label="Video in der Blende6 Galerie"></video>
                                @endif
                                <div class="flex items-center justify-between gap-3 px-4 py-3">
                                    <div class="min-w-0"><strong class="block truncate text-[11px] font-semibold text-[#566158]">{{ $item->guest_name ?: 'Blende6' }}</strong><span class="mt-0.5 block text-[9px] text-[#919791]">{{ $item->created_at->format('d.m.Y · H:i') }} Uhr</span></div>
                                    <a href="{{ route('weddings.media.download', [$wedding, $item]) }}" class="shrink-0 text-xs font-semibold text-[#657161]">↓ Original</a>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    @if($mediaPage->hasPages())
                        <div class="mt-10">{{ $mediaPage->fragment('galerie')->onEachSide(1)->links() }}</div>
                    @endif
                @endif
            </div>
        </section>

        <div id="lightbox" class="fixed inset-0 z-[60] hidden bg-[#172019]/96 p-4 text-white backdrop-blur-lg md:p-7" role="dialog" aria-modal="true" aria-labelledby="lightbox-title">
            <button type="button" id="lightbox-close" class="absolute right-4 top-4 z-20 grid size-11 place-items-center rounded-full bg-white/15 text-3xl md:right-7 md:top-6">×</button>
            <div class="mx-auto flex h-full max-w-7xl flex-col">
                <div class="flex min-h-0 flex-1 items-center gap-3 md:gap-6"><button type="button" id="lightbox-prev" class="grid size-11 shrink-0 place-items-center rounded-full bg-white/12 text-2xl">‹</button><div id="lightbox-content" class="flex min-h-0 flex-1 items-center justify-center"></div><button type="button" id="lightbox-next" class="grid size-11 shrink-0 place-items-center rounded-full bg-white/12 text-2xl">›</button></div>
                <div class="mx-auto mt-4 w-full max-w-5xl"><div class="flex items-center justify-between gap-4"><div><p id="lightbox-title" class="font-serif text-2xl"></p><p id="lightbox-count" class="mt-1 text-[10px] text-white/55"></p></div><a id="lightbox-download" href="#" class="rounded-full bg-white px-4 py-2.5 text-xs font-semibold text-[#465745]">↓ Original</a></div><div id="lightbox-thumbs" class="mt-4 flex gap-2 overflow-x-auto pb-1"></div></div>
            </div>
        </div>
    @include('partials.public-footer')
</main>
@endsection

@push('scripts')
<script>
(() => {
    const input = document.querySelector('#file-input');
    const list = document.querySelector('#upload-list');
    const start = document.querySelector('#start-upload');
    const startHelp = document.querySelector('#start-help');
    const errorBox = document.querySelector('#upload-error');
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
        files = next; input.value = ''; render();
    }
    function remove(id) { files = files.filter(item => item.id !== id); render(); }
    function error(message) { errorBox.textContent = message; errorBox.classList.toggle('hidden', !message); }
    function escapeHtml(value) { const div = document.createElement('div'); div.textContent = value; return div.innerHTML; }
    function render() {
        list.innerHTML = '';
        files.forEach(item => {
            const row = document.createElement('div'); row.className = 'upload-row';
            const preview = kind(item.file) === 'photo' && !/\.hei[cf]$/i.test(item.file.name) ? `<img src="${URL.createObjectURL(item.file)}" alt="">` : `<span>${kind(item.file) === 'video' ? '▶' : '◇'}</span>`;
            row.innerHTML = `<div class="upload-preview">${preview}</div><div class="min-w-0 flex-1"><strong>${escapeHtml(item.file.name)}</strong><small>${(item.file.size / 1024 / 1024).toFixed(1)} MB · ${item.status === 'success' ? 'Erfolgreich' : item.status === 'error' ? 'Fehlgeschlagen' : item.status === 'uploading' ? 'Wird hochgeladen' : 'Bereit'}</small><div class="progress-track"><i style="width:${item.progress}%" class="${item.status}"></i></div></div><button type="button" ${item.status === 'uploading' ? 'disabled' : ''} aria-label="Datei entfernen">×</button>`;
            row.querySelector('button').addEventListener('click', () => remove(item.id)); list.appendChild(row);
        });
        start.disabled = uploading;
        start.textContent = uploading ? 'Upload läuft …' : files.length ? `${files.length} ${files.length === 1 ? 'Datei' : 'Dateien'} hochladen` : 'Dateien auswählen';
    }
    start?.addEventListener('click', async () => {
        if (!files.length) { input.click(); return; }
        if (!guestName.reportValidity()) return error('Bitte gebt einen Namen ein.');
        if (!guestEmail.reportValidity()) return error('Bitte prüft die E-Mail-Adresse oder lasst das Feld leer.');
        error(''); uploading = true; render();
        const batch = crypto.randomUUID();
        for (const item of files.filter(value => value.status !== 'success')) await upload(item, batch);
        uploading = false; render();
        if (files.every(item => item.status === 'success')) {
            startHelp.textContent = 'Upload abgeschlossen. Die Galerie wird aktualisiert …';
            window.setTimeout(() => window.location.assign(@json(route('weddings.show', $wedding).'?upload=success#galerie')), 350);
        }
    });
    function upload(item, batch) { return new Promise(resolve => {
        item.status = 'uploading'; render();
        const data = new FormData(); data.append('file', item.file); data.append('batch_id', batch); data.append('guest_name', guestName.value.trim()); data.append('guest_email', guestEmail.value.trim());
        const xhr = new XMLHttpRequest(); xhr.open('POST', @json(route('weddings.upload', $wedding))); xhr.setRequestHeader('X-CSRF-TOKEN', csrf); xhr.setRequestHeader('Accept', 'application/json');
        xhr.upload.onprogress = event => { if (event.lengthComputable) { item.progress = Math.round(event.loaded / event.total * 100); render(); } };
        xhr.onload = () => { item.status = xhr.status >= 200 && xhr.status < 300 ? 'success' : 'error'; item.progress = item.status === 'success' ? 100 : item.progress; if (item.status === 'error') { try { const body = JSON.parse(xhr.responseText); error(body.message || Object.values(body.errors || {})[0]?.[0] || 'Upload fehlgeschlagen.'); } catch { error('Upload fehlgeschlagen.'); } } render(); resolve(); };
        xhr.onerror = () => { item.status = 'error'; error('Netzwerkfehler beim Upload.'); render(); resolve(); }; xhr.send(data);
    }); }

    const lightboxItems = @js($lightboxItems);
    const lightbox = document.querySelector('#lightbox');
    const content = document.querySelector('#lightbox-content');
    const thumbs = document.querySelector('#lightbox-thumbs');
    const title = document.querySelector('#lightbox-title');
    const count = document.querySelector('#lightbox-count');
    const download = document.querySelector('#lightbox-download');
    let current = 0;
    function showLightbox(index) {
        if (!lightboxItems.length) return;
        current = (index + lightboxItems.length) % lightboxItems.length;
        const item = lightboxItems[current];
        content.innerHTML = `<img src="${item.src}" alt="Foto in der Blende6 Galerie" class="max-h-[66vh] max-w-full rounded-xl object-contain shadow-2xl">`;
        title.textContent = item.guest;
        count.textContent = `${current + 1} von ${lightboxItems.length}`;
        download.href = item.download;
        thumbs.innerHTML = lightboxItems.map((value, indexValue) => `<button type="button" data-thumb-index="${indexValue}" class="h-16 w-16 shrink-0 overflow-hidden rounded-lg border-2 ${indexValue === current ? 'border-white' : 'border-transparent opacity-55'}"><img src="${value.thumb}" alt="" class="size-full object-cover"></button>`).join('');
        thumbs.querySelectorAll('[data-thumb-index]').forEach(button => button.addEventListener('click', () => showLightbox(Number(button.dataset.thumbIndex))));
        lightbox.classList.remove('hidden'); document.body.style.overflow = 'hidden';
    }
    function closeLightbox() { lightbox.classList.add('hidden'); content.innerHTML = ''; document.body.style.overflow = ''; }
    document.querySelectorAll('[data-lightbox-id]').forEach(button => button.addEventListener('click', () => showLightbox(lightboxItems.findIndex(item => String(item.id) === button.dataset.lightboxId))));
    document.querySelector('#lightbox-close')?.addEventListener('click', closeLightbox);
    document.querySelector('#lightbox-prev')?.addEventListener('click', () => showLightbox(current - 1));
    document.querySelector('#lightbox-next')?.addEventListener('click', () => showLightbox(current + 1));
    document.addEventListener('keydown', event => { if (lightbox.classList.contains('hidden')) return; if (event.key === 'Escape') closeLightbox(); if (event.key === 'ArrowLeft') showLightbox(current - 1); if (event.key === 'ArrowRight') showLightbox(current + 1); });
})();
</script>
@endpush
