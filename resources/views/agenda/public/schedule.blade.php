@extends('layouts.app')

@php
    use Carbon\Carbon;
    Carbon::setLocale('nl');

@endphp

@section('content')
    <div class="container col-md-11 mt-5 mb-5">
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
                                        class="event bg-light mt-2 w-100 d-flex flex-row-responsive-reverse justify-content-between">
                                        <div class="d-flex flex-column justify-content-between">
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
                                            <div class="d-flex align-items-center justify-content-center p-2">
                                                <img class="event-image" alt="Activiteit Afbeelding"
                                                     src="{{ asset('files/agenda/agenda_images/'.$activity->image) }}">
                                            </div>
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
        @endsection
