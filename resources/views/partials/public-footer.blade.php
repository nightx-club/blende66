<footer class="bg-[#435243] text-white">
    <div class="mx-auto grid max-w-7xl gap-10 px-6 py-14 md:grid-cols-[1.2fr_1fr_1fr] md:px-10">
        <div>
            <a href="{{ url('/') }}" class="inline-flex rounded-2xl bg-white/95 px-5 py-3" aria-label="Zur Blende6 Startseite"><img src="{{ asset('images/blende6-logo.png') }}" alt="Blende6" class="w-48"></a>
            <p class="mt-5 max-w-sm text-xs leading-6 text-white/65">Natürliche Hochzeits- und Portraitfotografie in der Wetterau, Büdingen, Hanau, Gießen, Gelnhausen und ganz Hessen.</p>
        </div>
        <div>
            <p class="text-[10px] font-semibold uppercase tracking-[.2em] text-white/50">Kontakt</p>
            <p class="mt-4 text-sm leading-7">Lina-Theresa Dick<br>Freiherr-vom-Stein-Straße 28<br>63695 Glauburg</p>
            <a href="mailto:info@blende6.de" class="mt-2 inline-block text-sm underline underline-offset-4">info@blende6.de</a>
        </div>
        <div>
            <p class="text-[10px] font-semibold uppercase tracking-[.2em] text-white/50">Blende6</p>
            <div class="mt-4 grid grid-cols-2 gap-x-5 gap-y-2 text-sm">
                <a href="{{ url('/') }}">Startseite</a><a href="{{ url('/h/blende6') }}#galerie">Galerie</a>
                <a href="{{ route('marketing.shootings') }}">Shootings</a><a href="{{ route('marketing.portfolio') }}">Portfolio</a>
                <a href="{{ route('marketing.about') }}">Über mich</a><a href="{{ route('marketing.contact') }}">Kontakt</a>
                <a href="{{ route('marketing.imprint') }}">Impressum</a><a href="{{ route('marketing.privacy') }}">Datenschutz</a>
            </div>
            <a href="{{ route('admin.login') }}" class="mt-6 inline-flex items-center rounded-full border border-white/35 px-4 py-2 text-xs font-semibold text-white transition hover:border-white/70 hover:bg-white/10">Admin-Login</a>
        </div>
    </div>
    <div class="border-t border-white/10 px-6 py-5 text-center text-[9px] uppercase tracking-[.18em] text-white/45">© {{ date('Y') }} Blende6 Fotografie · Lina-Theresa Dick</div>
</footer>
