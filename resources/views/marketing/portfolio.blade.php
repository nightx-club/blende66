@php $title = 'Portfolio – Blende 6 Fotografie'; @endphp
@extends('marketing.layout')
@section('marketing-content')
<main>
    <section class="bg-[#e8ede7] px-5 py-20 text-center md:px-10 md:py-28"><p class="text-[10px] font-semibold uppercase tracking-[.24em] text-[#778579]">Natürliche Momente</p><h1 class="mt-4 font-serif text-6xl tracking-tight md:text-8xl">Portfolio</h1><p class="mx-auto mt-5 max-w-2xl text-sm leading-7 text-[#687169]">Hochzeiten, Portraits und Geschichten, die nicht gestellt wirken, sondern sich echt anfühlen.</p></section>

    <section id="hochzeiten" class="mx-auto max-w-[1500px] scroll-mt-28 px-4 py-20 md:px-8 md:py-28">
        <div class="mb-10 flex flex-col justify-between gap-4 md:flex-row md:items-end"><div><p class="text-[10px] font-semibold uppercase tracking-[.24em] text-[#778579]">73 Motive</p><h2 class="mt-3 font-serif text-5xl tracking-tight">Hochzeiten</h2></div><p class="max-w-lg text-sm leading-7 text-[#687169]">Große Emotionen, kleine Augenblicke und all die leisen Geschichten dazwischen.</p></div>
        <div class="portfolio-masonry">
            @foreach($weddings as $index => $image)
                <button type="button" class="portfolio-image" data-portfolio-image="{{ $image }}" aria-label="Hochzeitsfoto {{ $index + 1 }} vergrößern"><img src="{{ $image }}" alt="Hochzeitsfotografie von Blende 6 – Motiv {{ $index + 1 }}" loading="lazy"></button>
            @endforeach
        </div>
    </section>

    <section id="portraits" class="scroll-mt-28 bg-[#e9ede7] px-4 py-20 md:px-8 md:py-28"><div class="mx-auto max-w-[1500px]"><div class="mb-10 flex flex-col justify-between gap-4 md:flex-row md:items-end"><div><p class="text-[10px] font-semibold uppercase tracking-[.24em] text-[#778579]">49 Motive</p><h2 class="mt-3 font-serif text-5xl tracking-tight">Portraits & Geschichten</h2></div><p class="max-w-lg text-sm leading-7 text-[#687169]">Stark, lebendig und einzigartig – draußen in der Natur und im schönsten natürlichen Licht.</p></div>
        <div class="portfolio-masonry">
            @foreach($portraits as $index => $image)
                <button type="button" class="portfolio-image" data-portfolio-image="{{ $image }}" aria-label="Portraitfoto {{ $index + 1 }} vergrößern"><img src="{{ $image }}" alt="Portraitfotografie von Blende 6 – Motiv {{ $index + 1 }}" loading="lazy"></button>
            @endforeach
        </div></div>
    </section>

    <dialog id="portfolio-lightbox" class="portfolio-dialog"><button type="button" data-close-portfolio aria-label="Ansicht schließen">×</button><img src="" alt="Vergrößerte Portfolioaufnahme"></dialog>
</main>
@push('scripts')
<script>
(() => {
    const dialog = document.querySelector('#portfolio-lightbox');
    const image = dialog?.querySelector('img');
    document.querySelectorAll('[data-portfolio-image]').forEach(button => button.addEventListener('click', () => {
        image.src = button.dataset.portfolioImage;
        dialog.showModal();
    }));
    document.querySelector('[data-close-portfolio]')?.addEventListener('click', () => dialog.close());
    dialog?.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
})();
</script>
@endpush
@endsection
