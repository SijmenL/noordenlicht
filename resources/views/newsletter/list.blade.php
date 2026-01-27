@extends('layouts.app')

@section('content')
    <div class="rounded-bottom-5 bg-white d-flex flex-column container-block"
         style="position: relative; z-index: 9; margin-top: -25px; background-position: center !important; background-image: url('{{ asset('img/logo/doodles/Wolf.webp') }}'); background-repeat: no-repeat; background-size: cover; min-height: 100vh;">

        <div class="container py-5">
            <div class="container justify-content-center align-items-center d-flex flex-column gap-5">
                <div style="backdrop-filter: blur(2px);">
                    <h1 class="text-center fw-bold display-5">Nieuwsbrief Archief</h1>
                    <h2 class="text-center fs-4 text-secondary fw-light">Het archief van onze e-mail nieuwsbrieven.</h2>
                </div>

                <div class="w-100" style="max-width: 900px;">
                    <form id="auto-submit" method="GET" class="d-flex flex-column gap-4">

                        {{-- 1. Prominent Search Bar --}}
                        <div class="position-relative">
                            <span class="material-symbols-rounded position-absolute text-muted"
                                  style="top: 50%; left: 20px; transform: translateY(-50%); font-size: 24px;">search</span>
                            <input id="search" name="search" type="text"
                                   class="form-control form-control-lg border-0 shadow-sm ps-5 py-3 rounded-pill custom-search-input"
                                   placeholder="Zoeken naar een nieuwsbrief"
                                   value="{{ $search ?? '' }}"
                                   autocomplete="off"
                                   onchange="this.form.submit();">
                        </div>
                    </form>
                </div>

                @if($campaigns->count() > 0)
                    <div class="d-flex flex-row-responsive flex-wrap gap-4 justify-content-center"
                         style="margin: 30px; padding: 5px">
                        @foreach ($campaigns as $campaign)
                            <a target="_blank" href="{{ $campaign['campaign']['web'] }}"
                               class="text-black text-decoration-none d-flex justify-content-center align-items-center flex-column">
                                <div class="card bg-whited" style="cursor: pointer; margin: 0 auto">
                                    <div class="card-img-top d-flex justify-content-center align-items-center">
                                        <img alt="Nieuws afbeelding" class="news-image"
                                             style="border-radius: 50%; aspect-ratio: 1/1; object-fit: cover"
                                             src="{{ $campaign['campaign']['screenshot']["226x268"] }}">
                                    </div>

                                    <div class="card-body d-flex flex-column justify-content-between p-0">
                                        <div>
                                            <h4 class="text-black text-center">{{$campaign['campaign']['name'] }}</h4>
                                            <div class="d-flex flex-row ps-3 pe-3 justify-content-center">

                                                <p class="d-flex align-items-center gap-2"><span
                                                        class="material-symbols-rounded text-secondary">calendar_month</span><span>{{ \Carbon\Carbon::parse($campaign['campaign']['delivery_requested'])->format('d M, Y') }}</span>
                                                </p>
                                            </div>
                                            <p class="card-text text-center text-muted flex-grow-1">
                                                {{ $campaign['campaign']['subject'] ?? 'Geen onderwerp beschikbaar.' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-center mt-5">
                        {{ $campaigns->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="display-1 text-muted"><i class="bi bi-mailbox"></i></div>
                        <p class="h4 mt-3">Geen nieuwsbrieven gevonden.</p>
                        <a href="{{ route('newsletters') }}" class="btn btn-link">Wis zoekopdracht</a>
                    </div>
                @endif
            </div>


            <style>
                .hover-lift:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 1rem 3rem rgba(0, 0, 0, .175) !important;
                }

                .bg-soft-primary {
                    background-color: rgba(13, 110, 253, 0.1);
                }
            </style>

        </div>
@endsection
