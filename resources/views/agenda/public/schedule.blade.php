@extends('layouts.app')

@php
    use Carbon\Carbon;
    Carbon::setLocale('nl');

@endphp

@section('content')
    <div class="rounded-bottom-5 bg-white d-flex flex-column container-block"
         style="position: relative;  margin-top: -25px; background-position: unset !important; background-image: url('{{ asset('img/logo/doodles/Blad Buizerd.webp') }}'); background-repeat: repeat;">
        <div class="container py-5">
            {{-- Header Sectie --}}
            <div class="container justify-content-center align-items-center d-flex flex-column gap-5 mb-5">
                <div style="backdrop-filter: blur(2px);">
                    <h1 class="text-center fw-bold display-5">Agenda & Events</h1>
                    <h2 class="text-center">Ontmoeten, verbinden en groeien bij NoordenLicht</h2>
                </div>
            </div>


            @if(Session::has('error'))
                <div class="alert alert-danger" role="alert">
                    {{ session('error') }}
                </div>
            @endif
            @if(Session::has('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            @if($limit === null)
                <div>
                    @else
                        <div>
                            @endif
                            <div class="d-flex flex-row-responsive align-items-center gap-5" style="width: 100%">
                                <div class="" style="width: 100%;">

                                    @if($limit === null)
                                        <div id="nav">
                                            <ul class="nav nav-tabs flex-row-reverse mb-4">
                                                <li class="nav-item">
                                                    <a class="nav-link"
                                                       href="{{ route('agenda.public.month', ['month' => $monthOffset]) }}#nav">
                                                <span class="material-symbols-rounded"
                                                      style="transform: translateY(5px)">calendar_view_month</span>
                                                        Maand
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link active" aria-current="page">
                                <span class="material-symbols-rounded"
                                      style="transform: translateY(5px)">calendar_today</span> Planning
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if($limit === null)
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex flex-row gap-0">
                                        <a href="{{ route('agenda.public.schedule', ['month' => $monthOffset - 1]) }}#agenda"
                                           class="btn d-flex align-items-center justify-content-center">
                                            <span class="material-symbols-rounded">arrow_back_ios</span>
                                        </a>
                                        <a href="{{ route('agenda.public.schedule', ['month' => 0]) }}#agenda"
                                           class="btn d-flex align-items-center justify-content-center">
                                            <span class="material-symbols-rounded">home</span>
                                        </a>
                                        <a href="{{ route('agenda.public.schedule', ['month' => $monthOffset + 1]) }}#agenda"
                                           class="btn d-flex align-items-center justify-content-center">
                                            <span class="material-symbols-rounded">arrow_forward_ios</span>
                                        </a>
                                    </div>
                                    <div>
                                        <h2>{{ $monthName }} {{ $year }}</h2>
                                    </div>
                                </div>
                            @endif

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
                                    @php
                                        $linkParams = [
                                            'id' => $activity->id,
                                            'month' => $monthOffset,
                                            'startDate' => $activitiesStart->format('Y-m-d'),
                                            'view' => 'schedule',
                                        ];
                                    @endphp

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
                                <div class="text-center py-5">
                                    <div class="empty-state-icon mb-3 opacity-25">
                                        <span class="material-symbols-rounded"
                                              style="font-size: 64px;">event_busy</span>
                                    </div>
                                    <h3 class="fw-bold text-secondary">Geen activiteiten gevonden</h3>
                                    <p class="text-muted">Probeer een andere datum of bekijk al ons aanbod.</p>
                                    <a href="{{ url()->current() }}" class="btn btn-primary rounded-pill px-4 mt-2">Toon
                                        alles</a>
                                </div>
                            @endif

                        </div>
                </div>
        </div>
    </div>
@endsection
