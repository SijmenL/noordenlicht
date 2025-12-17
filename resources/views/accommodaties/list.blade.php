@extends('layouts.app')

@section('content')
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

            <div class="w-100">
                <form id="auto-submit" method="GET">
                    <div class="d-flex">
                        <div class="d-flex flex-row-responsive justify-content-between gap-2 mb-3 w-100">
                            <div class="input-group">
                                <label for="search" class="input-group-text" id="basic-addon1">
                                    <span class="material-symbols-rounded">search</span></label>
                                <input id="search" name="search" type="text" class="form-control"
                                       placeholder="Zoeken op naam, type"
                                       aria-label="Zoeken" aria-describedby="basic-addon1" value="{{ $search }}"
                                       onchange="this.form.submit();">
                            </div>
                        </div>
                    </div>
                </form>

                @if($all_accommodaties->count() > 0)
                    <div class="d-flex flex-column gap-5" style="max-width: 100vw">
                        @foreach ($all_accommodaties as $accommodatie)
                            <div class="p-5 bg-light rounded-5">
                            <h2>{{ $accommodatie->name }}</h2>
                            <h3 style="font-weight: bold; font-style: italic; margin-top: -10px" class="text-dark">{{ $accommodatie->type }}</h3>
                            <div class="d-flex flex-row-responsive align-items-center gap-5">
                                <div class="w-100">
                                    <img src="{{ asset('/files/accommodaties/images/'.$accommodatie->image) }}"
                                         class="zoomable-image"
                                         style="border-radius: 100%; aspect-ratio: 1/1; width: 100%"
                                         alt="{{ $accommodatie->name }}">
                                </div>
                                <div class="w-100">
                                    <div class="row row-cols-3 g-3 text-center">

                                        @foreach($accommodatie->icons as $icon)
                                            <div class="col d-flex flex-column align-items-center">
                                                <div class="icon-color">
                                                    {!! file_get_contents(public_path('files/accommodaties/icons/'.$icon->icon)) !!}
                                                </div>

                                                <style>
                                                    .icon-color svg {
                                                        width: 50px;
                                                        height: 50px;
                                                        aspect-ratio: 1/1;
                                                        object-fit: cover;
                                                        fill: #5a7123;
                                                    }
                                                </style>
                                                <span class="mt-2">{{ $icon->text }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="w-100">
                                    <div>
                                        {{ \Illuminate\Support\Str::limit(
                                            preg_replace('/\s+/', ' ', strip_tags(html_entity_decode($accommodatie->description))), 600, '...') }}
                                    </div>

                                    <a href="{{ route('accommodaties.details', $accommodatie->id) }}" class="mt-5 btn btn-primary">Lees verder</a>
                                </div>
                            </div>
                            </div>
                        @endforeach

                    </div>
                    {{ $all_accommodaties->links() }}
                @else
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <span class="material-symbols-rounded me-2">home</span>Geen accommodaties gevonden...
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
