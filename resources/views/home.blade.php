@php use Illuminate\Support\Carbon; @endphp
@extends('layouts.app')

@section('above-nav')
    <div class="bg-white d-flex flex-column align-items-center justify-content-center gap-0 p-2"
         style="z-index: 99999">
        <img style="width: clamp(100px, 75vw, 250px)" src="{{ asset('img/logo/logo zonder tekst.webp') }}"
             alt="Logo NoordenLicht">
        <p style="text-align: center; font-weight: bold; font-family: Georgia,Times,Times New Roman,serif;"
           class="m-0 p-0 text-secondary">Natuurlijk Centrum voor Verbinding en BewustZijn</p>
        <p style="text-align: center; font-weight: bold; font-family: Georgia,Times,Times New Roman,serif;"
           class="m-0 p-0 text-dark">Events en Verhuur accommodaties voor persoonlijke groei</p>
    </div>
@endsection

@section('content')
    <div>
        <div class="rounded-bottom-5 bg-light d-flex flex-column container-block container-block-animated"
             style="position: relative; top: 50px; z-index: 10; background-image: url('{{ asset('img/logo/doodles/Blad StretchA_white.webp') }}');">
            <div class="container justify-content-center align-items-center d-flex flex-row-responsive gap-5">
                <img class="zoomable-image"
                     style="width: clamp(100px, 75vw, 500px); aspect-ratio: 1/1; object-fit: cover; border-radius: 50%"
                     src="{{ asset('img/photo/compressed/NoordenLicht.webp') }}" alt="NoordenLicht Poort">
                <div style="backdrop-filter: blur(2px);">
                    <h1 class="text-center">NoordenLicht nodigt uit tot verbinden en ont-moeten.</h1>
                    <h2 class="text-center">NoordenLicht brengt magie in je hart</h2>
                    <p class="text-center">NoordenLicht is een krachtige en prachtige plek in het bos in Schoonoord in
                        Drenthe. De energie is bijzonder, doordat de oergrond uit de ijstijd slechts 20 cm
                        onder het maaiveld nog volledig intact is. Dit geeft vele leylijnen, wat de plek tot een
                        ware krachtplek maakt. De energie van het bos geeft healing en transformatie, alleen
                        al door er te zijn. We proberen deze bijzondere plek met een lichte voetprint te
                        betreden en voelen ons dan ook te gast in de natuur. NoordenLicht is een plek om te
                        verbinden en te ont-moeten. Een plek waar je jezelf en de ander mag zien voor wie je

                        echt bent. In je grootsheid, licht en kracht.</p>
                </div>
            </div>
        </div>

        <div class="rounded-bottom-5 bg-white d-flex flex-column container-block"
             style="position: relative; z-index: 9; margin-top: 10px; background-position: unset !important; background-image: url('{{ asset('img/logo/doodles/Wolf.webp') }}'); background-repeat: no-repeat; background-size: cover">
            <div class="container justify-content-center align-items-center d-flex flex-column gap-5">
                <div style="backdrop-filter: blur(2px);">
                    <h1 class="text-center">Accommodaties &amp; Retraites</h1>
                    <h2 class="text-center">NoordenLicht is een plek voor en door gelijkgestemden. Een
                        ontmoetingsplek om samen te komen en samen te creëren.</h2>
                    <p class="text-center">De accommodaties zijn te huur voor 1 op 1 sessies, groepsactiviteiten en
                        retraites,
                        welke bijdragen aan de persoonlijke- en intuïtieve ontwikkeling.</p>
                    <p class="text-center">Alle accommodaties zijn duurzaam en gebouwd met natuurlijke materialen als
                        hout,
                        leem, kurk en sedum en hebben grote raampartijen wat veel licht en een grootse
                        bosbeleving geeft.</p>
                    <p class="text-center">Dit bepaalt de bijzondere sfeer op deze krachtplek. De gebouwen zijn
                        vrijstaand en
                        gaan op in de natuur. Zo is het hout aan de buitenzijde afkomstig van eigen bomen
                        die eerder op deze plek hebben gestaan. In de ruimte is een kachel, zodat je als
                        gebruiker zelf de temperatuur kunt bepalen, een theekeuken en een toilet.</p>
                    <p class="text-center">Op kampeerveld Het VogelNest kun je overnachten middels eigen tenten, of een
                        ingerichte tent boeken. Caravans en campers kunnen op de ruime parkeerplaats
                        geplaatst worden. Het VogelNest beschikt over een sanitaire ruimte met toilets,
                        douches en een buitenkeuken.</p>
                </div>


                <div class="marquee overflow-hidden">
                    <div class="location-marquee-track d-flex gap-3">
                        @if(count($locations) > 0)

                            @foreach ($locations as $location)
                                <div class="location-card flex-shrink-0">
                                    <a href="{{ route('accommodaties.details', $location->id) }}">
                                        <div class="location-img-wrapper">
                                            <img src="{{ asset('/files/accommodaties/images/'.$location->image) }}"
                                                 class="img-fluid"
                                                 alt="{{ $location->name }}">
                                            <div class="location-title">{{ $location->name }}</div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
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
                        @endif
                    </div>
                </div>

                <a href="{{ route('accommodaties') }}" class="btn btn-secondary text-white btn-lg rounded-pill shadow">Bekijk
                    Alles</a>
            </div>
        </div>

        <div class="rounded-bottom-5 d-flex flex-column bg-light container-block"
             style="position: relative; z-index: 8; margin-top: -25px">

            <div class="container justify-content-center align-items-center gap-5">
                <div style="backdrop-filter: blur(2px);">
                    <h1 class="text-center">Nieuwtjes</h1>

                    <div class="d-flex flex-column gap-4">
                        <a href="{{ route('newsletters') }}"
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

                        <a href="{{ route('agenda.public.schedule') }}"
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

                            $linkParams = [
                                'id' => $activity->id,
                                'month' => $monthOffset,
                                'startDate' => $activitiesStart->format('Y-m-d'),
                                'view' => 'schedule',
                            ];
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

                        <a
                            @if($activity->booking)
                                href="{{ route('agenda.public.booking', $linkParams) }}"
                            @else
                                href="{{ route('agenda.public.activity', $linkParams) }}"
                            @endif
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

                                    {{-- Added min-width: 0 to prevent flex items from forcing overflow --}}
                                    <div class="d-flex flex-column justify-content-between" style="min-width: 0;">
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

                                            {{-- Added word-break styles to force wrapping --}}
                                            <p style="overflow-wrap: break-word; word-break: break-word;">{{ \Str::limit(strip_tags(html_entity_decode($activity->content)), 300, '...') }}</p>
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

            <a href="{{ route('agenda.public.month') }}" class="mt-4 btn btn-primary btn-lg rounded-pill shadow">Bekijk
                Alles</a>


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
                    <p>NoordenLicht is ontstaan door vele handen. Sinds juli 2021 komen vrijwilligers op de
                        zaterdag samen om deze plek te laten groeien en bloeien. Nadat we ca 3000 door de
                        Letterzetter gestorven bomen hebben opgeruimd, doen vele geplante bomen, heesters
                        en planten hun best om weer een sterk bos te worden. Het heeft vooral tijd nodig…</p>
                    <p>Ook voor dit seizoen zijn Helpende Handjes meer dan welkom. Er staan grote en
                        kleine projecten op stapel om volgend seizoen nog mooier te kunnen stralen.</p>
                    <p>Wil jij meedragen aan de opbouw van NoordenLicht? Ben je goed gezind en kun je
                        jezelf dragen?</p>
                    <p>Voel je welkom om op een zaterdag kennis te komen maken. Wanneer je &quot;ingewerkt&quot;
                        ben, is het ook mogelijk om op andere dagen te bosklussen. Laat je even weten als je
                        komt of af te stemmen via 06-31223045.</p>
                    <p>We hebben de taken in groepen opgedeeld:</p>
                    <p><strong>Moedertje Groen</strong>: Kom Sandra helpen met het verplanten, snoeien en bemestenvan
                        alle nieuwe aanplanting.</p>
                    <p><strong>Stef Special</strong>: Stef is onze hout kunstenaar. Hij maakt banken, tafels en
                        ornamentenvan ons eigen hout. Ook staat er een heksen boshuisje bouwen op zijn programma.
                    </p>
                    <p><strong>Elfeling</strong>: Liane kan creatieve hulp gebruiken bij het beschilderen,
                        decoreren en deopbouw van oa Gloepertieland. Dit zijn ook creatieve klusjes die je thuis
                        kunt doen</p>
                    <p><strong>De Breurs</strong>: Timo en Jeroen bouwen erop los. Zij kunnen timmerhulp gebruiken
                        bijhet bouwen van het atelier/muziekstudio.</p>
                    <p><strong>Woodiewood</strong>: Nuttah heeft enorme stapels boomstammen liggen diegetransformeerd
                        willen worden tot planken middels de lintzaag. Het veilig kunnenwerken met een kettingzaag
                        is een pr&eacute;.</p>
                    <p><strong>De droomtram</strong>: Deze 100-jarige wordt omgetoverd tot een magischeovernachtingsplek.
                        Voor dit project zoeken we handige handjes die van timmeren,schuren en schilderen houden.
                    </p>
                    <p><strong>Kookfee en Afwasfee</strong>: Omdat wij ieder buikje graag gezond vullen, en Nuttah
                        nietalles tegelijk kan, zoeken we iemand die van niets iets kan maken. Voor op dezaterdag,
                        maar ook als oproepkracht door de weeks.</p>
                    <p>Ieder seizoen maken we de balans op en een nieuwe planning. Je hoeft je dus niet
                        voor lange tijd te binden als vrijwilliger aan NoordenLicht.</p>
                    <div class="d-flex align-items-center justify-content-center">
                        <a class="btn btn-secondary btn-lg text-white rounded-pill shadow">Bekijk Alles</a>

                    </div>
                </div>
            </div>
        </div>

        <div class=" bg-white d-flex flex-column container-block"
             style="position: relative; z-index: 2; margin-top: -25px; background-image: url('{{ asset('img/logo/doodles/BuizerdVlucht.webp') }}'); background-repeat: no-repeat; background-size: 150vw; background-position: center">
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
            <a href="{{ route('news.list') }}" class="btn btn-primary btn-lg rounded-pill shadow">Lees het hele Blog</a>
        </div>
    </div>

    <!-- Custom Newsletter Popup -->
    <div id="newsletterPopup" class="popup"
         style="display: none; z-index: 99999; top: 0; left: 0; position: fixed; width: 100vw; height: 100vh; background: rgba(0,0,0,0.7); align-items: center; justify-content: center;">
        <div class="popup-body"
             style="position: relative; width: clamp(300px, 90%, 800px); max-height: 90vh; margin: 0; background: white; border-radius: 12px; overflow-y: auto; display: flex; flex-direction: column; box-shadow: 0px 10px 30px rgba(0,0,0,0.5);">

            <div class="d-flex justify-content-between align-items-center p-3 border-bottom w-100">
                <h5 class="mb-0 fw-bold">Blijf op de hoogte!</h5>
                <button type="button" class="btn btn-outline-danger d-flex align-items-center" id="closePopupBtn">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>

            <!-- Content Area -->
            <div class="p-4 d-flex flex-column flex-md-row gap-4 align-items-center justify-content-center"
                 style="flex-grow: 1;">

                <!-- Optional Image (visible on md+) -->
                <div class="d-none d-md-block" style="flex: 1; text-align: center;">
                    <img src="{{ asset('img/logo/logo zonder tekst.webp') }}"
                         style="width: 100%; max-width: 300px; height: auto; object-fit: contain;" alt="Nieuwsbrief">
                </div>

                <!-- Form -->
                <div style="flex: 1; width: 100%;">
                    <div class="text-center">
                        <span class="material-symbols-rounded text-primary mb-2" style="font-size: 4rem;">mail</span>
                        <p class="mb-4 text-muted">Meld je aan voor onze nieuwsbrief en mis geen enkel evenement of
                            update van NoordenLicht.</p>
                    </div>

                    <form id="newsletter-form">
                        <div class="mb-3">
                            <input type="email" class="form-control form-control-lg text-center" id="newsletter-email"
                                   name="email" placeholder="Jouw e-mailadres" required>
                        </div>
                        <div id="newsletter-message" class="mb-3 d-none text-center"></div>
                        <button type="submit" class="btn btn-primary rounded-pill w-100 btn-lg">Aanmelden</button>
                    </form>

                    <div class="text-center mt-3">
                        <small class="text-muted" style="cursor: pointer;" id="dismissPopupLink">Nee bedankt, ik kijk
                            liever gewoon rond.</small>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Popup Elements
            const popup = document.getElementById('newsletterPopup');
            const closeBtn = document.getElementById('closePopupBtn');
            const dismissLink = document.getElementById('dismissPopupLink');

            // Form Elements
            const form = document.getElementById('newsletter-form');
            const messageDiv = document.getElementById('newsletter-message');
            const emailInput = document.getElementById('newsletter-email');
            const submitBtn = form.querySelector('button[type="submit"]');

            function showPopup() {
                popup.style.display = 'flex'; // Use flex to enable centering
            }

            function closePopup() {
                popup.style.display = 'none';
                localStorage.setItem('newsletter_popup_seen', 'true');
            }

            // Check if user has already seen or subscribed
            const hasSeenPopup = localStorage.getItem('newsletter_popup_seen');
            const hasSubscribed = localStorage.getItem('newsletter_subscribed');

            if (!hasSeenPopup && !hasSubscribed) {
                setTimeout(showPopup, 10000); // 10 seconds
            }

            // Close listeners
            closeBtn.addEventListener('click', closePopup);
            dismissLink.addEventListener('click', closePopup);

            // Close on click outside (optional)
            popup.addEventListener('click', function (e) {
                if (e.target === popup) {
                    closePopup();
                }
            });

            // Handle Form Submission
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                // Disable button
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Even geduld...';
                messageDiv.classList.add('d-none');
                messageDiv.className = 'mb-3 d-none text-center'; // reset classes

                const email = emailInput.value;
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch("{{ route('newsletters.subscribe') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({email: email})
                })
                    .then(response => response.json())
                    .then(data => {
                        messageDiv.classList.remove('d-none');
                        if (data.success) {
                            messageDiv.classList.add('text-success');
                            messageDiv.innerText = data.message;
                            localStorage.setItem('newsletter_subscribed', 'true');
                            form.reset();
                            setTimeout(closePopup, 3000);
                        } else {
                            messageDiv.classList.add('text-danger');
                            messageDiv.innerText = data.message || 'Er ging iets mis.';
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = 'Aanmelden';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        messageDiv.classList.remove('d-none');
                        messageDiv.classList.add('text-danger');
                        messageDiv.innerText = 'Er is een fout opgetreden. Probeer het later opnieuw.';
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Aanmelden';
                    });
            });
        });
    </script>
@endsection
