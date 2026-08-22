@php $title = 'Über mich – Blende 6 Fotografie'; @endphp
@extends('marketing.layout')
@section('marketing-content')
<main>
    <section class="bg-[#e8ede7] px-5 py-20 text-center md:px-10 md:py-28"><p class="text-[10px] font-semibold uppercase tracking-[.24em] text-[#778579]">Blende 6 Fotografie</p><h1 class="mt-4 font-serif text-6xl tracking-tight md:text-8xl">Über mich</h1></section>
    <section class="mx-auto grid max-w-7xl gap-14 px-5 py-20 md:grid-cols-[.8fr_1.2fr] md:px-10 md:py-28">
        <div><img src="{{ asset('images/blende6/about.png') }}" alt="Lina-Theresa Dick" class="sticky top-32 w-full rounded-[2.5rem] object-cover shadow-[0_28px_80px_rgba(54,67,58,.16)]"></div>
        <article class="marketing-prose">
            <h2>Wie ich zur Fotografie kam</h2>
            <p>Hi, ich bin Lina und ich liebe die Natur. Schon als Kind war ich am glücklichsten, wenn ich draußen war: barfuß durchs Gras rennen, Schafe auf dem Bauernhof zusammentreiben oder mit meinem Zwillingsbruder lachend ins Meer stürmen. Die Natur war für mich nie nur Kulisse, sondern immer ein Ort voller Freiheit, Geborgenheit und Abenteuer.</p>
            <p>Mein Vater hatte auf all diesen Wegen seine Kamera dabei. Anfangs war ich die kleine Beobachterin neben ihm, doch irgendwann wurde Zuschauen langweilig. Also hielt ich bald selbst eine Kamera in den Händen. Damals, mit sechs Jahren, noch ohne Blick für Einstellungen oder Technik, aber schon mit der Freude, Momente einzufangen, bevor sie verfliegen.</p>
            <p>Später, als die ersten Schüleraustausche kamen, verstand ich: Fotos sind mehr als nur Bilder. Sie sind Erinnerungen, die bleiben. Ich begann, tiefer einzutauchen, lernte über Fotokurse und Seminare, übte bei Shootings und spürte mehr und mehr: Hier schlägt mein Herz. Aus Leidenschaft wurde Berufung.</p>
            <h2>Mein Werdegang</h2>
            <p>Ich bin in der Nähe von Aachen aufgewachsen. Nach der Schule zog es mich in die Soziale Arbeit: ein Freiwilliges Soziales Jahr, ein Praktikum im Kinderheim, später ein Studium mit Stationen, die mich prägten. Ich lernte, Menschen zu verstehen, zuzuhören, hinzusehen – Eigenschaften, die heute meine Fotografie genauso tragen wie meine Kamera.</p>
            <p>Durch die Musik habe ich meinen Partner kennengelernt, und so bin ich in Hessen geblieben. Heute lebe ich hier, umgeben von Wäldern, Feldern und der Möglichkeit, jederzeit kleine Auszeiten in der Natur oder Reisen in die Ferne zu genießen.</p>
            <h2>Was mich bewegt</h2>
            <p>Wenn ich nicht fotografiere, findest du mich draußen – wandernd auf einsamen Wegen, schwimmend im See, auf dem Pferderücken oder beim Bouldern. Manchmal mit Gitarre oder Stift in der Hand, Musik machen, Lieder schreiben, zeichnen. Kreativität ist für mich nicht auf eine Form begrenzt, sondern ein Lebensgefühl.</p>
            <p>Und so füllt sich auch meine Bucket List immer weiter: Reisen, Abenteuer, kleine Träume realisieren, die für mich wie Fotografie sind. Erinnerungen, die zu Geschichten werden.</p>
            <h2>Warum Fotografie für mich mehr ist</h2>
            <p>Fotografie ist für mich wie ein leiser Spiegel. Sie zeigt nicht nur, was außen sichtbar ist, sondern auch das, was innen leuchtet. Wenn du vor meiner Kamera stehst, geht es nicht um Perfektion, sondern um dein echtes Lächeln, deine Stärke, deine Zartheit und alles, was dich einzigartig macht.</p>
            <p>Ein Bild kann festhalten, was wir im Alltag oft übersehen: die Wärme in einem Blick, das Strahlen in einem Moment, das Gefühl, genau so richtig zu sein, wie man ist. Genau das möchte ich dir schenken: Erinnerungen, die dich immer wieder daran erinnern, wer du bist.</p>
            <a href="{{ route('marketing.contact') }}" class="mt-8 inline-flex rounded-full bg-[#4d5d50] px-6 py-3.5 text-sm font-semibold text-white">Lass uns kennenlernen</a>
        </article>
    </section>
</main>
@endsection
