<header class="sticky top-0 z-50 border-b border-[#dfe2dd] bg-[#fffdf9]/95 backdrop-blur-xl" data-public-navigation>
    <div class="mx-auto flex min-h-20 max-w-7xl items-center justify-between gap-4 px-5 py-3 md:px-10">
        <a href="{{ url('/') }}" aria-label="Blende6 Startseite" class="shrink-0">
            <img src="{{ asset('images/blende6-logo.png') }}" alt="Blende6" class="w-36 sm:w-44 xl:w-48">
        </a>

        <nav class="hidden items-center justify-end gap-0.5 text-[10px] font-semibold text-[#526057] lg:flex xl:text-[11px]" aria-label="Öffentliche Navigation">
            <a href="{{ url('/') }}" class="public-nav-link">Startseite</a>
            <a href="{{ url('/h/blende6') }}#galerie" class="public-nav-link">Galerie</a>
            <a href="{{ route('marketing.shootings') }}" class="public-nav-link">Shootings</a>
            <a href="{{ route('marketing.portfolio') }}" class="public-nav-link">Portfolio</a>
            <a href="{{ route('marketing.about') }}" class="public-nav-link">Über mich</a>
            <a href="{{ route('marketing.contact') }}" class="public-nav-link">Kontakt</a>
            <a href="{{ route('marketing.imprint') }}" class="public-nav-link">Impressum</a>
            <a href="{{ route('marketing.privacy') }}" class="public-nav-link">Datenschutz</a>
        </nav>

        <button type="button" class="grid size-11 shrink-0 place-items-center rounded-full border border-[#b9c1b7] text-[#465745] lg:hidden" aria-expanded="false" aria-controls="public-mobile-menu" aria-label="Menü öffnen" data-public-menu-toggle>
            <span class="flex w-5 flex-col gap-1.5" aria-hidden="true"><i class="h-px w-full bg-current"></i><i class="h-px w-full bg-current"></i><i class="h-px w-full bg-current"></i></span>
        </button>
    </div>

    <nav id="public-mobile-menu" class="hidden border-t border-[#e5e5e0] bg-[#fffdf9] px-5 py-4 lg:hidden" aria-label="Mobile Navigation" data-public-mobile-menu>
        <div class="mx-auto grid max-w-7xl grid-cols-2 gap-1 text-sm font-semibold text-[#526057] sm:grid-cols-4">
            <a href="{{ url('/') }}" class="public-mobile-nav-link">Startseite</a>
            <a href="{{ url('/h/blende6') }}#galerie" class="public-mobile-nav-link">Galerie</a>
            <a href="{{ route('marketing.shootings') }}" class="public-mobile-nav-link">Shootings</a>
            <a href="{{ route('marketing.portfolio') }}" class="public-mobile-nav-link">Portfolio</a>
            <a href="{{ route('marketing.about') }}" class="public-mobile-nav-link">Über mich</a>
            <a href="{{ route('marketing.contact') }}" class="public-mobile-nav-link">Kontakt</a>
            <a href="{{ route('marketing.imprint') }}" class="public-mobile-nav-link">Impressum</a>
            <a href="{{ route('marketing.privacy') }}" class="public-mobile-nav-link">Datenschutz</a>
        </div>
    </nav>
</header>

<script>
(() => {
    const navigation = document.currentScript.previousElementSibling;
    const toggle = navigation?.querySelector('[data-public-menu-toggle]');
    const menu = navigation?.querySelector('[data-public-mobile-menu]');
    if (!toggle || !menu) return;
    const close = () => { menu.classList.add('hidden'); toggle.setAttribute('aria-expanded', 'false'); toggle.setAttribute('aria-label', 'Menü öffnen'); };
    toggle.addEventListener('click', () => {
        const open = toggle.getAttribute('aria-expanded') === 'true';
        if (open) close();
        else { menu.classList.remove('hidden'); toggle.setAttribute('aria-expanded', 'true'); toggle.setAttribute('aria-label', 'Menü schließen'); }
    });
    menu.querySelectorAll('a').forEach(link => link.addEventListener('click', close));
    document.addEventListener('keydown', event => { if (event.key === 'Escape') close(); });
})();
</script>
