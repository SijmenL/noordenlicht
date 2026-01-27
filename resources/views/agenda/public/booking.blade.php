@extends('layouts.app')
@include('partials.editor')

@php
    use Carbon\Carbon;
    Carbon::setLocale('nl');
@endphp

@section('content')

    <div class="container col-md-11 mt-5 mb-5">
        @if($activity !== null)

            @php
                $eventStart = Carbon::parse($activity->date_start);
                $eventEnd = Carbon::parse($activity->date_end);

            @endphp

            @if($view === 'month')
                <a href="{{ route('agenda.public.month') }}"
                   class="btn m-4 d-flex flex-row gap-4 align-items-center justify-content-center"
                   style="margin-left: 25%; margin-right: 25%"
                ><span class="material-symbols-rounded me-2">arrow_back</span> <span>Terug naar het overzicht</span></a>
            @else
                <a href="{{ route('agenda.public.schedule') }}"
                   class="btn m-4 d-flex flex-row gap-4 align-items-center justify-content-center"
                   style="margin-left: 25%; margin-right: 25%"
                ><span class="material-symbols-rounded me-2">arrow_back</span> <span>Terug naar het overzicht</span></a>
            @endif

            <div>
                @if(Session::has('error'))
                    <div class="alert alert-danger" role="alert">
                        {{ session('error') }}
                    </div>
                @endif


                <div class="rounded-2 w-100 d-flex flex-column align-items-center">
                    <div class="w-100">

                        <div class="p-3 w-100 d-flex align-items-center flex-column">

                            <h1 class="text-center mt-5">{{ "Activiteit door " . $activity->user->praktijknaam }}</h1>
                            @if($eventStart->isSameDay($eventEnd))
                                <h2 class="text-center">{{ $eventStart->format('j') }} {{ $eventStart->translatedFormat('F') }}
                                    @ {{ $eventStart->format('H:i') }} - {{ $eventEnd->format('H:i') }}</h2>
                            @else
                                <h2 class="text-center">{{ $eventStart->format('d-m-Y') }}
                                    tot {{ $eventEnd->format('d-m-Y') }}</h2>
                            @endif

                            <div class="d-flex flex-row-responsive gap-5 w-100">
                                <div class="d-flex flex-column align-items-center justify-content-center w-100">
                                    <div class="w-100 agenda-content mt-4"
                                         style="align-self: start">{!! $activity->activity_description !!}
                                    </div>
                                </div>

                                    @if(isset($activity->external_link))
                                    <div class="flex-shrink-0" style="min-width: 350px;">
                                        <div class="card shadow-lg border-0 rounded-4 overflow-hidden position-relative"
                                             style="border: 1px solid #e0e0e0;">
                                            <!-- Decorative Header Background -->
                                            <div
                                                style="height: 100px; background-image: url('{{ asset('img/logo/doodles/Treewoman2a.webp') }}'); background-size: cover; background-position: center; filter: brightness(0.9);"></div>

                                            <div class="card-body p-4 position-relative bg-white">
                                                <h3 class="fw-bold text-center mb-1">Aanmelden</h3>
                                                <p class="text-muted text-center small mb-4">Deze activiteit wordt georganiseerd door {{$activity->user->praktijknaam}}. Aanmelden is mogelijk via hun website.</p>

                                                <a href="{{ Str::startsWith($activity->external_link, ['http://', 'https://']) ? $activity->external_link : 'https://' . $activity->external_link }}" target="_blank"
                                                        class="btn btn-primary btn-lg rounded-pill w-100 shadow fw-bold">
                                                    <span class="material-symbols-rounded align-middle me-2">link</span>
                                                    Meld je aan!
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Description Section --}}
                        <div class="bg-light w-100 p-4 rounded mt-3">
                            <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">description</span>Beschrijving
                            </h2>
                            <div class="d-flex flex-row flex-wrap gap-5 justify-content-between">
                                <div>
                                    <h4 class="mb-2">Gegevens</h4>
                                    <div class="d-flex flex-column gap-1">
                                        @if($eventStart->isSameDay($eventEnd))
                                            <p class="m-0"><strong>Datum</strong></p>
                                            <p class="m-0">{{ $eventStart->format('j') }} {{ $eventStart->translatedFormat('F') }} </p>
                                            <p class="m-0"><strong>Tijd</strong></p>
                                            <p class="m-0">{{ $eventStart->format('H:i') }} </p>
                                        @else
                                            <p class="m-0"><strong>Begin</strong></p>
                                            <p class="m-0">{{ $eventStart->format('j') }} {{ $eventStart->translatedFormat('F') }}
                                                om {{ $eventStart->format('H:i') }}</p>
                                            <p class="m-0"><strong>Einde</strong></p>
                                            <p class="m-0">{{ $eventEnd->format('j') }} {{ $eventEnd->translatedFormat('F') }}
                                                om {{ $eventEnd->format('H:i') }}</p>
                                        @endif
                                    </div>
                                </div>
                                @if(isset($activity->accommodatie->name))
                                    <div>
                                        <h4 class="mb-2">Locatie</h4>
                                        <p class="m-0">{{ $activity->accommodatie->name }}</p>
                                    </div>
                                @endif
                                @if(isset($activity->user->praktijknaam))
                                    <div>
                                        <h4 class="mb-2">Organisator</h4>
                                        <a href="{{ Str::startsWith($activity->user->website, ['http://', 'https://']) ? $activity->user->website : 'https://' . $activity->user->website }}" target="_blank">
                                            {{ $activity->user->praktijknaam }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @else
                    {{-- Not Found View --}}
                    <div class="container col-md-11 text-center mt-5">
                        <h1>Activiteit niet gevonden</h1>
                        <p>Het item is mogelijk verwijderd of verplaatst.</p>
                        <a href="{{ route('agenda.public.month') }}" class="btn btn-primary text-white">Ga terug naar
                            het
                            overzicht</a>
                    </div>
                @endif
            </div>
    </div>
@endsection
