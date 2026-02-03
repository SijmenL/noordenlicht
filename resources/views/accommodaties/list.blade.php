@extends('layouts.app')

@section('content')
    <div class="rounded-bottom-5 bg-white d-flex flex-column container-block"
         style="position: relative; margin-top: -25px; background-position: unset !important; background-image: url('{{ asset('img/logo/doodles/Blad Buizerd.webp') }}'); background-repeat: repeat;">
        <div class="container py-5">
            {{-- Header Sectie --}}
            <div class="container justify-content-center align-items-center d-flex flex-column gap-5 mb-5">
                <div style="backdrop-filter: blur(2px);">
                    <h1 class="text-center fw-bold display-5">Accommodaties &amp; Retraites</h1>
                    <h2 class="text-center">NoordenLicht is een plek voor en door gelijkgestemden. Een ontmoetingsplek om samen te komen en samen te creëren.</h2>
                    <div class="mt-4 max-w-3xl mx-auto">
                        <p class="text-center">NoordenLicht ligt in een bosrijke omgeving in Drenthe en biedt sfeervolle accommodaties die per uur te huur zijn.</p>
                        <p class="text-center">Het is een plek voor verbinding – speciaal voor ondernemers die werken aan fysieke, emotionele, mentale of spirituele gezondheid. Zij delen hier hun kennis en missie via workshops, sessies of andere activiteiten. Hun bezoekers zijn mensen die bewuster willen leven, op zoek zijn naar verbinding, groei en een lichter, gelukkiger leven. Bij NoordenLicht ontmoeten deze twee groepen elkaar – in een omgeving vol rust, balans en natuurlijke energie.</p>
                        <p class="text-center">Stel je eigen retraite samen, kies uit de diverse accommodaties, overnachtingsopties en maaltijden. Heb je een plan, en kom je er niet aan uit, stuur een mailtje met je ideeën en we kijken samen naar de mogelijkheden en bieden je een vrijblijvende offerte aan.</p>
                    </div>
                </div>

                <div class="w-100" style="max-width: 700px;">
                    <form id="auto-submit" method="GET" class="d-flex flex-column gap-4">
                        <div class="position-relative">
                            <span class="material-symbols-rounded position-absolute text-muted"
                                  style="top: 50%; left: 20px; transform: translateY(-50%); font-size: 24px;">search</span>
                            <input id="search" name="search" type="text"
                                   class="form-control form-control-lg border-0 shadow-lg ps-5 py-3 rounded-pill custom-search-input"
                                   placeholder="Zoeken naar accommodaties..."
                                   value="{{ $search ?? '' }}"
                                   autocomplete="off"
                                   onchange="this.form.submit();">
                        </div>
                    </form>
                </div>
            </div>

            <div class="w-100">
                <div class="d-flex flex-column gap-5">

                    <div class="border-0 rounded-5 shadow-lg overflow-hidden text-white mb-4"
                         style="background: linear-gradient(135deg, #5a7123 0%, #3e4e1a 100%);">
                        <div class="row g-0">
                            <div class="col-lg-7 p-5 d-flex flex-column justify-content-center">
                                <div>
                                    <h2 class="display-6 text-white fw-bold mb-3">Retraite</h2>
                                    <p class="lead mb-4" style="opacity: 0.95;">
                                        Breng jouw retraite naar een <em>next level</em> door de magie van NoordenLicht.
                                        Naast de heerlijke accommodaties kun je het unieke belevingsbos integreren in jouw programma.
                                    </p>

                                    <p class="mb-3" style="font-size: 0.95rem; opacity: 0.9;">Ontdek de krachtplekken:</p>
                                    <div class="d-flex flex-wrap gap-2 mb-4">
                                        <span class="badge bg-black bg-opacity-25 fw-normal py-2 px-3 rounded-pill">Labyrint</span>
                                        <span class="badge bg-black bg-opacity-25 fw-normal py-2 px-3 rounded-pill">VoorouderVeld</span>
                                        <span class="badge bg-black bg-opacity-25 fw-normal py-2 px-3 rounded-pill">Opstellingpad</span>
                                        <span class="badge bg-black bg-opacity-25 fw-normal py-2 px-3 rounded-pill">Medicijnwiel</span>
                                        <span class="badge bg-black bg-opacity-25 fw-normal py-2 px-3 rounded-pill">Yoni-tempel</span>
                                    </div>

                                    <p class="small fst-italic mb-4" style="opacity: 0.8;">
                                        Stel je eigen retraite samen naar jouw wensen en geniet van heerlijke buikverwennerijen.
                                    </p>
                                </div>
                                <div>
                                    <btn class="btn btn-light btn-lg rounded-pill px-5 fw-bold text-success shadow-sm disabled"
                                       style="color: #5a7123 !important;">
                                        Binnenkort te boeken!
                                    </btn>
                                </div>
                            </div>

                            <div class="col-lg-5 position-relative rounded-5 overflow-hidden" style="min-height: 200px;">
                                <img src="{{ asset('/img/photo/compressed/Labyrint1.webp') }}"
                                     class="w-100 h-100 object-fit-cover position-absolute top-0 start-0 zoomable-image "
                                     alt="Retraite Belevingsbos"
                                     style="object-position: center;">
                                <div class="d-lg-none position-absolute top-0 start-0 w-100 h-100"
                                     style="background: linear-gradient(to bottom, rgba(90, 113, 35, 0.2), rgba(90, 113, 35, 0));"></div>
                            </div>
                        </div>
                    </div>

                        @if($all_accommodaties->count() > 0)
                            <div class="d-flex flex-column gap-5">
                                @foreach ($all_accommodaties as $accommodatie)
                                    <div class="bg-light rounded-5 shadow-sm overflow-hidden border border-light">
                                        <div class="row g-0 align-items-center flex-column-reverse {{ $loop->even ? 'flex-lg-row' : 'flex-lg-row-reverse' }}">

                                            {{-- Content Deel --}}
                                            <div class="col-lg-7 p-4 p-md-5">
                                                <div class="d-flex flex-column align-items-center gap-3">
                                                    <div>
                                                        <span class="text-primary fw-bold text-center text-uppercase small mb-2 d-block" style="letter-spacing: 1.5px;">{{ $accommodatie->type }}</span>
                                                        <h3 class="display-6 text-center fw-bold text-dark mb-3">{{ $accommodatie->name }}</h3>
                                                    </div>

                                                    <div class="d-flex flex-wrap align-items-center justify-content-center gap-2 mb-2">
                                                        @foreach($accommodatie->icons as $icon)
                                                            <div class="d-flex align-items-center justify-content-between bg-white border border-white rounded-pill px-3 py-2 shadow-sm" title="{{ $icon->text }}">
                                                                <div class="icon-pill-svg me-2" style="height: 22px; width: 22px;">
                                                                    {!! file_get_contents(public_path('files/accommodaties/icons/'.$icon->icon)) !!}
                                                                </div>
                                                                <span class="small fw-semibold text-muted" style="font-size: 0.85rem;">{{ $icon->text }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                    <div class="text-muted mb-4 line-clamp-3">
                                                        {{ \Illuminate\Support\Str::limit(
                                                            preg_replace('/\s+/', ' ', strip_tags(html_entity_decode($accommodatie->description))), 350, '...') }}
                                                    </div>

                                                    <div>
                                                        <a href="{{ route('accommodaties.details', $accommodatie->id) }}"
                                                           class="btn btn-outline-primary btn-lg rounded-pill px-5 fw-bold transition-all">
                                                            Ontdek {{ $accommodatie->name }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-lg-5 p-3 p-lg-4">
                                                <div class="overflow-hidden shadow-sm h-100" style="min-height: 300px; max-height: 450px; border-radius: 50%; aspect-ratio: 1/1; object-fit: cover">
                                                    <img src="{{ asset('/files/accommodaties/images/'.$accommodatie->image) }}"
                                                         class="w-100 h-100 object-fit-cover zoomable-image"
                                                         alt="{{ $accommodatie->name }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-5">
                                {{ $all_accommodaties->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="empty-state-icon mb-3 opacity-25">
                                    <span class="material-symbols-rounded" style="font-size: 64px;">search_off</span>
                                </div>
                                <h3 class="fw-bold text-secondary">Geen accommodaties gevonden</h3>
                                <p class="text-muted">Probeer een andere zoekterm of bekijk al ons aanbod.</p>
                                <a href="{{ url()->current() }}" class="btn btn-primary rounded-pill px-4 mt-2">Toon alles</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    {{-- Extra Styling voor de nieuwe componenten --}}
    <style>
        .icon-pill-svg svg {
            width: 100%;
            height: 100%;
            object-fit: contain;
            fill: #5a7123;
        }

        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .btn-outline-primary {
            border-color: #5a7123;
            color: #5a7123;
        }

        .btn-outline-primary:hover {
            background-color: #5a7123;
            border-color: #5a7123;
            color: white;
        }

        .text-primary {
            color: #5a7123 !important;
        }
    </style>
@endsection
