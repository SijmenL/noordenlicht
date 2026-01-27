@extends('layouts.app')

@vite('resources/js/calendar.js')

@php
    use Carbon\Carbon;
    Carbon::setLocale('nl');
@endphp

@section('content')
    <div class="container col-md-11 mt-5 mb-5">
        <div class="d-flex flex-row-responsive align-items-center gap-5" style="width: 100%">
            <div class="" style="width: 100%;">
                <div id="nav">
                    <ul class="nav nav-tabs flex-row-reverse mb-4">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page"><span class="material-symbols-rounded"
                                                                                 style="transform: translateY(5px)">calendar_view_month</span>
                                Maand</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link"
                               href="{{ route('agenda.public.schedule', ['month' => $monthOffset]) }}#nav"><span
                                    class="material-symbols-rounded"
                                    style="transform: translateY(5px)">calendar_today</span> Planning</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div id="agenda">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex flex-row gap-0">
                    <a href="{{ route('agenda.public.month', ['month' => $monthOffset - 1,]) }}#nav"
                       class="btn d-flex align-items-center justify-content-center">
                        <span class="material-symbols-rounded">arrow_back_ios</span>
                    </a>
                    <a href="{{ route('agenda.public.month', ['month' => 0]) }}#nav"
                       class="btn d-flex align-items-center justify-content-center">
                        <span class="material-symbols-rounded">home</span>
                    </a>
                    <a href="{{ route('agenda.public.month', ['month' => $monthOffset + 1]) }}#nav"
                       class="btn d-flex align-items-center justify-content-center">
                        <span class="material-symbols-rounded">arrow_forward_ios</span>
                    </a>
                </div>
                <div>
                    <h2>{{$monthName}} {{$year}}</h2>
                </div>
            </div>
            <div class="calendar-grid">
                <div class="calendar-day">MA</div>
                <div class="calendar-day">DI</div>
                <div class="calendar-day">WO</div>
                <div class="calendar-day">DO</div>
                <div class="calendar-day">VR</div>
                <div class="calendar-day">ZA</div>
                <div class="calendar-day">ZO</div>

                @php
                    $globalRowTracker = [];
                    $weekEventCounts = [];
                    $currentWeek = 0;

                    for ($i = 1 - $firstDayOfWeek; $i <= $daysInMonth; $i++) {
                        if ($i > 0) {
                            $today = Carbon::create($year, $month, $i)->startOfDay();
                            $activitiesForDay = $activities->filter(function ($activity) use ($today) {
                                $start = Carbon::parse($activity->date_start)->startOfDay();
                                $end = Carbon::parse($activity->date_end)->endOfDay();
                                return $today->between($start, $end);
                            });

                            $weekEventCounts[$currentWeek] = max($weekEventCounts[$currentWeek] ?? 0, $activitiesForDay->count());
                        }

                        if (($i + $firstDayOfWeek) % 7 === 0) {
                            $currentWeek++;
                        }
                    }

                    $currentWeek = 0;
                @endphp

                @for ($i = 0; $i < $firstDayOfWeek; $i++)
                    <div class="calendar-cell empty"></div>
                @endfor

                @php
                    $rowPositions = [];
                @endphp

                @for ($i = 1; $i <= $daysInMonth; $i++)
                    @php
                        $today = Carbon::create($year, $month, $i)->startOfDay();
                        $activitiesForDay = $activities->filter(function ($activity) use ($today) {
                            $start = Carbon::parse($activity->date_start)->startOfDay();
                            $end = Carbon::parse($activity->date_end)->endOfDay();
                            return $today->between($start, $end);
                        });

                        $maxEventsInWeek = $weekEventCounts[$currentWeek] ?? 0;
                        $baseHeight = 100;
                        $additionalHeight = 25 * $maxEventsInWeek;
                        $totalHeight = $baseHeight + $additionalHeight;

                        $isMonday = $today->isMonday();
                    @endphp

                    <div
                        class="calendar-cell {{ $i == $currentDay && $month == $currentMonth && $year == $currentYear ? 'highlight' : '' }}"
                        style="height: {{ $totalHeight }}px;">
                        <p class="calendar-cell-text">{{ $i }}</p>

                        @if ($activitiesForDay->isNotEmpty())
                            @foreach ($activitiesForDay as $activity)
                                @php
                                    $start = Carbon::parse($activity->date_start)->startOfDay();
                                    $end = Carbon::parse($activity->date_end)->endOfDay();
                                    $isFirstDay = $today->isSameDay($start);
                                    $isLastDay = $today->isSameDay($end);

                                    $activityClass = 'calendar-event';
                                    if ($isFirstDay && $isLastDay) {
                                        $activityClass .= ' calendar-event-single';
                                    } else {
                                        if ($isFirstDay) {
                                            $activityClass .= ' calendar-event-first';
                                        }
                                        if ($isLastDay) {
                                            $activityClass .= ' calendar-event-last';
                                        }
                                        if ($isMonday) {
                                            $activityClass .= ' calendar-event-monday';
                                        }
                                    }

                                    if ($activity->should_highlight) {
                                        $activityClass .= ' calendar-event-highlight';
                                    }

                                    $activityImage = $activity->image;
                                    $activityContent = $activity->content;
                                    $activityTitle = $activity->title;

                                    $activitiestart = Carbon::parse($activity->date_start);
                                    $activityEnd = Carbon::parse($activity->date_end);

                                    if ($activitiestart->isSameDay($activityEnd)) {
                                        $formattedStart = $activitiestart->format('H:i');
                                        $formattedEnd = $activityEnd->format('H:i');
                                    } else {
                                        $formattedStart = $activitiestart->format('d-m H:i');
                                        $formattedEnd = $activityEnd->format('d-m H:i');
                                    }

                                     $activitiesStart = Carbon::parse($activity->date_start);

                                    $linkParams = [
                                        'id' => $activity->id,
                                        'month' => $monthOffset,
                                        'startDate' => $activitiesStart->format('Y-m-d'),
                                        'view' => 'month',
                                    ];
                                @endphp

                                <a
                                    @if($activity->booking)
                                        href="{{ route('agenda.public.booking', $linkParams) }}"
                                    @else
                                        href="{{ route('agenda.public.activity', $linkParams) }}"
                                    @endif
                                   style="top: {{ 40 + ($activityPositions[$activity->id] ?? 0) * 35 }}px;"

                                   data-event-id="{{ $activity->id }}"
                                   data-event-start="{{ $formattedStart }}"
                                   data-event-end="{{ $formattedEnd }}"
                                   @if(isset($activityImage))
                                       data-image="{{ asset('files/agenda/agenda_images/'.$activityImage) }}"
                                   @endif
                                   data-content="{{ \Str::limit(strip_tags(html_entity_decode($activityContent)), 200, '...') }}"
                                   data-title="{{ $activityTitle }}"
                                   class="{{ $activityClass }}"
                                >
                                    @if ($isFirstDay || ($isMonday && !$isLastDay))
                                        <div class="calendar-event-title">
                                            {{ $activityTitle }}
                                        </div>
                                    @endif
                                </a>
                            @endforeach
                        @endif
                    </div>

                    @php
                        if (($i + $firstDayOfWeek) % 7 === 0) {
                            $currentWeek++;
                        }
                    @endphp
                @endfor

                @while(($daysInMonth + $firstDayOfWeek) % 7 != 0)
                    <div class="calendar-cell empty"></div>
                    @php($daysInMonth++)
                @endwhile
            </div>

            <div id="event-popup">
                <p><span id="date-start"></span> - <span id="date-end"></span></p>
                <h3 id="popup-title"></h3>
                <div id="popup-content"></div>
                <img id="popup-image" src="" alt="Agenda Afbeelding">
            </div>

        </div>
    </div>
@endsection
