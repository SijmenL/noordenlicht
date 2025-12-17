@php use Illuminate\Support\Carbon; @endphp
@extends('layouts.app')

@section('above-nav')
    <div class="bg-white d-flex flex-column align-items-center justify-content-center gap-0 p-2"
         style="z-index: 99999">
        <img style="width: clamp(100px, 75vw, 250px)" src="{{ asset('img/logo/logo zonder tekst.webp') }}"
             alt="Logo Noordenlicht">
        <p style="text-align: center; font-weight: bold; font-family: Georgia,Times,Times New Roman,serif;"
           class="m-0 p-0 text-secondary">Natuurlijk Centrum voor Verbinding en BewustZijn</p>
        <p style="text-align: center; font-weight: bold; font-family: Georgia,Times,Times New Roman,serif;"
           class="m-0 p-0 text-dark">Events en Verhuur accommodaties voor persoonlijke groei</p>
    </div>
@endsection

@section('content')
    <div>
        <div class="rounded-bottom-5 bg-light d-flex flex-column container-block container-block-animated"
             style="position: relative; z-index: 10; background-image: url('{{ asset('img/logo/doodles/Blad StretchA_white.webp') }}');">
            <div class="container justify-content-center align-items-center d-flex flex-row-responsive gap-5">
                <img class="zoomable-image"
                     style="width: clamp(100px, 75vw, 500px); aspect-ratio: 1/1; object-fit: cover; border-radius: 50%"
                     src="{{ asset('img/photo/compressed/NoordenLicht.webp') }}" alt="NoordenLicht Poort">
                <div style="backdrop-filter: blur(2px);">
                    <h1 class="text-center">NoordenLicht nodigt uit tot verbinden en ont-moeten.</h1>
                    <p>NoordenLicht is een krachtige en bijzondere plek, midden in het bos in Schoonoord in Drenthe.
                        Rust, thuis komen bij jezelf en verbinding is wat je er onder andere kunt vinden.</p>
                    <p>De energie hier is uitzonderlijk: slechts 20 centimeter onder het maaiveld ligt nog altijd de
                        oergrond uit de ijstijd, volledig intact. Dit maakt dat er talloze leylijnen samenkomen, wat
                        deze plek tot een ware krachtplaats maakt.</p>
                    <p>De energie van het bos werkt helend en transformerend – simpelweg door er te zijn. We benaderen
                        deze plek met respect en een lichte voetafdruk; als gasten in de natuur.</p>
                    <p>Het is een plek waar je jezelf en de ander kunt ontmoeten zoals je werkelijk bent – in je
                        grootsheid, je licht en je kracht.</p>

                    <div class="d-flex align-items-center justify-content-center">
{{--                        <a class="btn btn-secondary text-white">Ontdek Meer</a> drie stippels--}}
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-bottom-5 bg-white d-flex flex-column container-block"
             style="position: relative; z-index: 9; margin-top: -25px; background-position: unset !important; background-image: url('{{ asset('img/logo/doodles/Wolf.webp') }}'); background-repeat: no-repeat; background-size: cover">
            <div class="container justify-content-center align-items-center d-flex flex-column gap-5">
                <div style="backdrop-filter: blur(2px);">
                    <h1 class="text-center">Ruimtes & Accomodaties</h1>
                    <h2 class="text-center">NoordenLicht is een plek voor en door gelijkgestemden. Een ontmoetingsplek
                        om samen te komen en samen te creëren.</h2>
                    <p class="text-center">NoordenLicht ligt in een bosrijke omgeving in Drenthe en biedt sfeervolle
                        ruimtes die per uur te huur zijn.</p>
                    <p class="text-center">Het is een plek voor verbinding – speciaal voor ondernemers die werken aan
                        fysieke, emotionele, mentale of spirituele gezondheid. Zij delen hier hun kennis en missie via
                        workshops, sessies of andere activiteiten.</p>
                    <p class="text-center">Hun bezoekers zijn mensen die bewuster willen leven, op zoek zijn naar
                        verbinding, groei en een lichter, gelukkiger leven.</p>
                    <p class="text-center">Bij NoordenLicht ontmoeten deze twee groepen elkaar – in een omgeving vol
                        rust, balans en natuurlijke energie.</p>
                </div>


                        <div class="marquee overflow-hidden">
                            <div class="location-marquee-track d-flex gap-3">
                                @foreach ($locations as $location)
                                    <div class="location-card flex-shrink-0">
                                        <a href="{{ route('accommodaties.details', $location->id) }}">
                                            <div class="location-img-wrapper">
                                                <img src="{{ asset('/files/accommodaties/images/'.$location->image) }}" class="img-fluid"
                                                     alt="{{ $location->name }}">
                                                <div class="location-title">{{ $location->name }}</div>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const marqueeTrack = document.querySelector('.location-marquee-track');
                        const marqueeContainer = document.querySelector('.marquee');

                        const cards = Array.from(marqueeTrack.children);
                        const containerWidth = marqueeContainer.offsetWidth;

                        let totalWidth = cards.reduce((sum, card) => sum + card.offsetWidth + parseInt(getComputedStyle(card).marginRight), 0);

                        // duplicate only if totalWidth < containerWidth
                        let clones = [];
                        while (totalWidth < containerWidth * 2) { // duplicate enough to cover 2x container for smooth scroll
                            cards.forEach(card => {
                                const clone = card.cloneNode(true);
                                marqueeTrack.appendChild(clone);
                                clones.push(clone);
                            });
                            totalWidth = Array.from(marqueeTrack.children).reduce((sum, card) => sum + card.offsetWidth + parseInt(getComputedStyle(card).marginRight), 0);
                        }
                    });

                </script>

                <a href="{{ route('accommodaties') }}" class="btn btn-secondary text-white">Bekijk Alles</a>
            </div>
        </div>

        <div class="rounded-bottom-5 d-flex flex-column bg-light container-block"
             style="position: relative; z-index: 8; margin-top: -25px">

            <div class="container justify-content-center align-items-center gap-5">
                <div style="backdrop-filter: blur(2px);">
                    <h1 class="text-center">Nieuwtjes</h1>

                    <div class="d-flex flex-column gap-4">
                        <a href=""
                           class="text-decoration-none news-anchor p-3 rounded-5 d-flex flex-row justify-content-between align-items-center"
                           style="background-image: url('{{ asset('img/logo/doodles/Treewoman2a.webp') }}'); background-repeat: no-repeat; background-size: cover; background-position: top">
                            <div class="d-flex flex-row gap-4 align-items-center">
                                <img class=""
                                     style="width: clamp(15px, 25vw, 250px); aspect-ratio: 1/1; object-fit: cover; border-radius: 50%"
                                     src="{{ asset('img/photo/compressed/Sfeer5.webp') }}"
                                     alt="NoordenLicht Poort">
                                <div>
                                    <h2 class="text-primary">Laatste Nieuwsbrief</h2>
                                    <p class="text-black">Bekijk onze laatste nieuwsbrief, of een uit het archief.</p>
                                </div>
                            </div>
                            <span class="text-primary material-symbols-rounded">arrow_forward_ios</span>
                        </a>

                        <a href=""
                           class="text-decoration-none news-anchor p-3 rounded-5 d-flex flex-row justify-content-between align-items-center"
                           style="background-image: url('{{ asset('img/logo/doodles/Treewoman2a.webp') }}'); background-repeat: no-repeat; background-size: cover; background-position: center">
                            <div class="d-flex flex-row gap-4 align-items-center">
                                <img class=""
                                     style="width: clamp(15px, 25vw, 250px); aspect-ratio: 1/1; object-fit: cover; border-radius: 50%"
                                     src="{{ asset('img/photo/compressed/Sfeer9.webp') }}"
                                     alt="NoordenLicht Poort">
                                <div>
                                    <h2 class="text-primary">NoordenLicht organiseert</h2>
                                    <p class="text-black">Bekijk het eerst volgende evenement.</p>
                                </div>
                            </div>
                            <span class="text-primary material-symbols-rounded">arrow_forward_ios</span>
                        </a>

                        <a href=""
                           class="text-decoration-none news-anchor p-3 rounded-5 d-flex flex-row justify-content-between align-items-center"
                           style="background-image: url('{{ asset('img/logo/doodles/Treewoman2a.webp') }}'); background-repeat: no-repeat; background-size: cover; background-position: bottom">
                            <div class="d-flex flex-row gap-4 align-items-center">
                                <img class=""
                                     style="width: clamp(15px, 25vw, 250px); aspect-ratio: 1/1; object-fit: cover; border-radius: 50%"
                                     src="{{ asset('img/photo/compressed/THuus5.webp') }}"
                                     alt="NoordenLicht Poort">
                                <div>
                                    <h2 class="text-primary">EetSamenlijk</h2>
                                    <p class="text-black">Bekijk de EetSamenlijk ticketshop.</p>
                                </div>
                            </div>
                            <span class="text-primary material-symbols-rounded">arrow_forward_ios</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-bottom-5 bg-white d-flex flex-column container-block"
             style="position: relative; z-index: 7; margin-top: -25px; background-image: url('{{ asset('img/logo/doodles/BuizerdVlucht.webp') }}'); background-repeat: no-repeat; background-size: 150vw; background-position: center">
            <h1 class="text-center">Agenda</h1>

            <div class="container">

                @if(count($activities) > 0)
                    @php
                        $currentMonth = null;
                    @endphp

                    @foreach ($activities as $activity)
                        @php
                            $activitiesStart = Carbon::parse($activity->date_start);
                            $activityEnd = Carbon::parse($activity->date_end);

                            $activityMonth = $activitiesStart->translatedFormat('F');
                        @endphp

                        @if($currentMonth !== $activityMonth)
                            @php
                                $currentMonth = $activityMonth
                            @endphp

                            <div class="d-flex flex-row w-100 align-items-center mt-4 mb-2">
                                <h4 class="month-devider">{{ $activitiesStart->translatedFormat('F') }}</h4>
                                <div class="month-devider-line"></div>
                            </div>
                        @endif

                        <a onclick="breakOut({{ $activity->id }})"
                           class="text-decoration-none"
                           style="color: unset; cursor: pointer">
                            <div class="d-flex flex-row">
                                <div style="width: 50px"
                                     class="d-flex flex-column gap-0 align-items-center justify-content-center">
                                    <p class="day-name">{{ mb_substr($activitiesStart->translatedFormat('l'), 0, 2) }}</p>
                                    <p class="day-number">{{ $activitiesStart->format('j') }}</p>
                                </div>
                                <div
                                    class="p-3 rounded-5 bg-light mt-2 w-100 d-flex flex-row-responsive-reverse align-items-center justify-content-between">
                                    <div class="d-flex flex-column  justify-content-between">
                                        <div>
                                            @if($activitiesStart->isSameDay($activityEnd))
                                                <p>{{ $activitiesStart->format('j') }} {{ $activitiesStart->translatedFormat('F') }}
                                                    @ {{ $activitiesStart->format('H:i') }}
                                                    - {{ $activityEnd->format('H:i') }}</p>
                                            @else
                                                <p>{{ $activitiesStart->format('d-m-Y') }}
                                                    tot {{ $activityEnd->format('d-m-Y') }}</p>
                                            @endif
                                            <h3>{{ $activity->title }}</h3>
                                            <p><strong>{{ $activity->location }}</strong></p>
                                            <p>{{ \Str::limit(strip_tags(html_entity_decode($activity->content)), 300, '...') }}</p>
                                        </div>
                                        <div>
                                            @if(isset($activity->price))
                                                @if($activity->price !== '0')
                                                    <p><strong>{{ $activity->price }}</strong></p>
                                                @else
                                                    <p><strong>gratis</strong></p>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                    @if($activity->image)
                                        <img class="event-image m-0" alt="Activiteit Afbeelding"
                                             src="{{ asset('files/agenda/agenda_images/'.$activity->image) }}">
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                @else
                    <div class="alert alert-warning d-flex align-items-center mt-4" role="alert">
                        <span class="material-symbols-rounded me-2">event_busy</span>Geen activiteiten gevonden...
                    </div>
                @endif

            </div>

        </div>


        <div class="bg-dark rounded-bottom-5 bg-light d-flex flex-column container-block"
             style="position: relative; z-index: 6; margin-top: -25px; background-image: url('{{ asset('img/logo/doodles/') }}'); background-repeat: no-repeat; background-size: cover">
            <div class="container justify-content-center align-items-center d-flex flex-row-responsive-row gap-5">

                <div class="w-100">
                    <h1 class="text-center text-light">Wie zijn wij?</h1>
                    <p class="text-light">Ik ben Nuttah, een Brabantse boerendochter, geboren met een extra antenne. Als
                        kleuter riep ik al:
                        “Ik heb iets belangrijks te doen in dit leven!”.</p>
                    <p class="text-light">Vierenveertig jaar later, heb ik dit belangrijks gevonden. Via banketbakker,
                        kok,
                        groenteboer en
                        verkoper honden-benodigdheden, ben ik opgeleid tot erkend Medium Trance Healing Therapeut en
                        Adem
                        Coach.</p>
                    <p class="text-light">Mijn missie is: “Een lichtje brengen in de bochten van je levenspad.”</p>
                    <p class="text-light">Sinds 2004 ben ik zelfstandig ondernemer. In 2013 heb ik de grote stap genomen
                        om
                        mijn droom na te
                        jagen door naar mijn geliefde vakantieland Drenthe te verhuizen.</p>
                    <p class="text-light">Mijn praktijk KrachtCirkel Schoonoord is de aanzet geweest om dit grootse
                        project
                        geboorte te geven.
                        Het concept en de realisatie hiervan is via Nuttah ontstaan. Om geen concessies te doen in het
                        visioen wat de plek NoordenLicht wil zijn.</p>
                    <p class="text-light">In november 2022 kruisten twee drummers het pad en ontdekten we dat echte
                        liefde
                        toch bestaat. En zo
                        loopt Timo mee op dit pad.</p>
                    <p class="text-light">Timo geboren in Bergentheim is muzikant, drummer, songwriter en gitarist. Als
                        producer heeft hij
                        diverse albums gemixed en speelt in diverse bands.</p>
                    <p class="text-light">Vanaf 1 juni 2024 is Timo volledig werkzaam op NoordenLicht. Hij is dan ook
                        het
                        directe
                        aanspreekpunt en helpt je graag waar nodig..</p>
                    <div class="d-flex align-items-center justify-content-center">
                        <a class="btn btn-secondary text-white">Lees Verder</a>
                    </div>
                </div>

                <div style="width: clamp(100px, 75vw, 450px); min-width: 50%">

                    <x-carousel
                        :images="['img/photo/compressed/Wij1.webp', 'img/photo/compressed/Wij2.webp', 'img/photo/Wij3.webp']"/>
                </div>

            </div>
        </div>

        <div class="rounded-bottom-5 d-flex flex-column container-block bg-light"
             style="position: relative; z-index: 5; margin-top: -25px; background-image: url('{{ asset('img/logo/doodles/Ree2b white.webp') }}'); background-repeat: no-repeat; background-size: cover">
            <div
                class="container justify-content-center align-items-center d-flex flex-row-responsive-reverse-row gap-5">


                <div style="width: clamp(100px, 75vw, 450px); min-width: 50%">
                    <x-carousel
                        :images="['img/photo/compressed/BosVrienden1.webp', 'img/photo/compressed/BosVrienden3.webp', 'img/photo/compressed/BosVrienden4.webp', 'img/photo/compressed/BosVrienden9.webp', 'img/photo/compressed/BosVrienden11.webp']"/>
                </div>
                <div class="w-100">
                    <h1 class="text-center">Bosvrienden</h1>
                    <h2 class="text-center">Het realiseren en het manifesteren van NoordenLicht doen we Samen!</h2>
                    <p>Na vier jaar BosKlussen is het eindelijk zover, de eerste accommodaties zijn klaar en zijn in
                        gebruik
                        genomen. Het resultaat van al het harde werken is nu echt goed zichtbaar. NoordenLicht begint
                        steeds
                        meer vorm te krijgen. De vele geplante bomen, heesters en planten doen hun best om weer een
                        sterk
                        bos te worden. Het heeft vooral tijd nodig…</p>
                    <p>Elke volgende (bouw-) fase brengt weer nieuwe werkSaamheden met zich mee en daarom ontvangen wij,
                        ter
                        versterking en ondersteuning van onze BosVrienden-werkgroep, graag bij verschillende klussen een
                        paar extra helpende handjes.</p>
                    <p>Bijna elke zaterdag is er een bosklusdag (eventuele andere dagen wisselen en zijn in
                        overleg).</p>
                    <p>Voel je op deze dagen van harte welkom om op vrijwillige basis, eens of vaker, aan te
                        sluiten.</p>
                    <strong>
                        <p>We zijn op zoek naar:</p>
                        <ul>
                            <li>Mensen met groene vingers voor team ‘Moedertje Groen’</li>
                            <li>Veelzijdige creatievelingen</li>
                            <li>Handige timmermensen en/of groot-houtbewerkers</li>
                            <li>Versterking voor ons kookteam</li>
                            <li>All round bosklussers</li>
                            <li>Zaaghulp</li>
                            <li>Mensen met groene vingers gezocht voor het groene team</li>
                        </ul>
                    </strong>
                    <div class="d-flex align-items-center justify-content-center">
                        <a class="btn btn-secondary text-white">Bekijk Alles</a>
                    </div>
                </div>
            </div>
        </div>

        <div class=" bg-white d-flex flex-column container-block"
             style="position: relative; z-index: 4; margin-top: -25px; margin-bottom: -125px; background-image: url('{{ asset('img/logo/doodles/BuizerdVlucht.webp') }}'); background-repeat: no-repeat; background-size: 150vw; background-position: center">
            <h1 class="text-center">Blog</h1>
            @if($news->count() > 0)
                <div class="d-flex flex-row-responsive gap-4 justify-content-center"
                     style="margin: 30px; padding: 5px">
                    @foreach($news as $news_item)
                        <a href="{{ route('news.item', $news_item->id) }}"
                           class="text-black text-decoration-none d-flex justify-content-center align-items-center flex-column">
                            <div class="card bg-whited" style="cursor: pointer; margin: 0 auto">
                                <div class="card-img-top d-flex justify-content-center align-items-center">
                                    <img alt="Nieuws afbeelding" class="news-image"
                                         style="border-radius: 50%; aspect-ratio: 1/1; object-fit: cover"
                                         src="{{ asset('/files/news/news_images/'.$news_item->image.' ') }}">
                                </div>
                                <div class="align-items-center news-badge d-flex justify-content-center">
                                    <p class="badge rounded-pill bg-info text-center text-black"
                                       style="font-size: 1rem">{{ $news_item->category }}</p>
                                </div>

                                <div class="card-body d-flex flex-column justify-content-between p-0"
                                     style="margin-top: -20px">
                                    <div>
                                        <h2 class="text-black text-center">{{ $news_item->title }}</h2>
                                        <div class="d-flex flex-row ps-3 pe-3 justify-content-between">
                                            <p class="d-flex align-items-center gap-2"><span
                                                    class="material-symbols-rounded text-secondary">person</span><span>{{ $news_item->user->name }}</span>
                                            </p>
                                            <p class="d-flex align-items-center gap-2"><span
                                                    class="material-symbols-rounded text-secondary">calendar_month</span><span>{{ $news_item->date->format('d-m-Y') }}</span>
                                            </p>
                                        </div>
                                        <p class="text-center">{{ $news_item->description }}</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="alert alert-warning d-flex align-items-center mt-4" role="alert">
                    <span class="material-symbols-rounded me-2">unsubscribe</span>Geen nieuws gevonden...
                </div>
            @endif

            <a href="{{ route('news.list') }}" class="btn btn-primary">Lees het hele Blog</a>
        </div>
    </div>

@endsection
