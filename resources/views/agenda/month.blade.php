@extends('layouts.dashboard')

@include('partials.editor')

@vite('resources/js/calendar.js')

@php
    use Carbon\Carbon;
    Carbon::setLocale('nl');
@endphp

@section('content')
    <div class="container col-md-11">

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="d-flex flex-row-responsive align-items-center gap-5 mb-3" style="width: 100%">
            <div class="" style="width: 100%;">
                <div class="d-flex flex-row justify-content-between align-items-center">
                    <h1 class="">Agenda</h1>

                    <div class="dropdown">
                        <button class="btn btn-primary text-white dropdown-toggle" type="button" id="calendarDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false">
                            Exporteer de agenda
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="calendarDropdown">
                            <li><a class="dropdown-item calendar-link" target="_blank" href="#" data-type="google">Google
                                    Calendar</a>
                            </li>
                            <li><a class="dropdown-item calendar-link" target="_blank" href="#" data-type="ical">iCalendar</a>
                            </li>
                            <li><a class="dropdown-item calendar-link" target="_blank" href="#" data-type="outlook">Outlook
                                    365</a></li>
                            <li><a class="dropdown-item calendar-link" target="_blank" href="#" data-type="download">Download
                                    bestand</a></li>
                        </ul>
                    </div>
                </div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item" aria-current="page"><a href="{{route('admin')}}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Agenda</li>
                    </ol>
                </nav>


                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const dropdownButton = document.getElementById('calendarDropdown');
                        let tokenLoaded = false;

                        dropdownButton.addEventListener('click', function () {
                            if (tokenLoaded) return;

                            fetch('/dashboard/agenda/token', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                }
                            })
                                .then(response => response.json())
                                .then(data => {
                                    const token = data.token;
                                    const webcalUrl = `webcal://${window.location.host}/agenda/feed/${token}.ics`;
                                    const encodedWebcal = encodeURIComponent(webcalUrl);
                                    const calendarName = encodeURIComponent('MHG Agenda');

                                    document.querySelectorAll('.calendar-link').forEach(link => {
                                        const type = link.dataset.type;

                                        if (type === 'google') {
                                            link.href = `https://calendar.google.com/calendar/u/0/r?cid=${webcalUrl}`;
                                        } else if (type === 'outlook') {
                                            link.href = `https://outlook.office.com/owa?path=/calendar/action/compose&rru=addsubscription&url=${encodedWebcal}&name=${calendarName}`;
                                        } else if (type === 'ical') {
                                            link.href = webcalUrl;
                                        } else {
                                            link.href = webcalUrl;
                                        }

                                        link.setAttribute('target', '_blank');
                                    });

                                    tokenLoaded = true;
                                })
                                .catch(error => {
                                    console.error('Failed to fetch calendar token', error);
                                });
                        });
                    });


                </script>

                <script>
                    let showAll = document.getElementById('show-all');
                    showAll.addEventListener('change', function () {
                        const url = new URL(window.location.href);
                        url.searchParams.set('all', this.checked ? 'true' : 'false');
                        window.location.href = url.toString();
                        console.log("New URL:", url.toString());
                    });
                </script>

            </div>
        </div>

        <div id="agenda">
            <div class="d-flex justify-content-between align-items-center">
                @if(!isset($lesson))
                    <div class="d-flex flex-row gap-0">
                        <a href="{{ route('agenda.month', ['month' => $monthOffset - 1, 'all' => $wantViewAll]) }}#nav"
                           class="btn d-flex align-items-center justify-content-center">
                            <span class="material-symbols-rounded">arrow_back_ios</span>
                        </a>
                        <a href="{{ route('agenda.month', ['month' => 0, 'all' => $wantViewAll]) }}#nav"
                           class="btn d-flex align-items-center justify-content-center">
                            <span class="material-symbols-rounded">home</span>
                        </a>
                        <a href="{{ route('agenda.month', ['month' => $monthOffset + 1, 'all' => $wantViewAll]) }}#nav"
                           class="btn d-flex align-items-center justify-content-center">
                            <span class="material-symbols-rounded">arrow_forward_ios</span>
                        </a>
                    </div>
                @else
                    <div class="d-flex flex-row gap-0">
                        <a href="{{ route('agenda.month', ['month' => $monthOffset - 1, 'all' => $wantViewAll, 'lessonId' => $lesson->id]) }}#nav"
                           class="btn d-flex align-items-center justify-content-center">
                            <span class="material-symbols-rounded">arrow_back_ios</span>
                        </a>
                        <a href="{{ route('agenda.month', ['month' => 0, 'all' => $wantViewAll, 'lessonId' => $lesson->id]) }}#nav"
                           class="btn d-flex align-items-center justify-content-center">
                            <span class="material-symbols-rounded">home</span>
                        </a>
                        <a href="{{ route('agenda.month', ['month' => $monthOffset + 1, 'all' => $wantViewAll, 'lessonId' => $lesson->id]) }}#nav"
                           class="btn d-flex align-items-center justify-content-center">
                            <span class="material-symbols-rounded">arrow_forward_ios</span>
                        </a>
                    </div>
                @endif
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
                            $activitiesForDayCount = $activities->filter(function ($activity) use ($today) {
                                $start = Carbon::parse($activity->date_start)->startOfDay();
                                $end   = Carbon::parse($activity->date_end)->endOfDay();
                                return $today->between($start, $end);
                            });
                            $weekEventCounts[$currentWeek] = max($weekEventCounts[$currentWeek] ?? 0, $activitiesForDayCount->count());
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
                        // Filter events for this day…
                        $activitiesForDay = $activities->filter(function ($activity) use ($today) {
                            $start = Carbon::parse($activity->date_start)->startOfDay();
                            $end   = Carbon::parse($activity->date_end)->endOfDay();
                            return $today->between($start, $end);
                        });

                        // Only one event per unique (id + startDate) combination will pass.
                        $activitiesForDay = $activitiesForDay->unique(function($item) {
                            return $item->id . '-' . Carbon::parse($item->date_start)->format('Y-m-d');
                        })->sortBy(function ($activity) {
                            return Carbon::parse($activity->date_start);
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
                            @foreach ($activitiesForDay as $index => $activity)
                                @php
                                    $start = Carbon::parse($activity->date_start)->startOfDay();
                                    $end   = Carbon::parse($activity->date_end)->endOfDay();
                                    $isFirstDay = $today->isSameDay($start);
                                    $isLastDay  = $today->isSameDay($end);

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

                                    // If lesson_id exists, load lesson info and adjust classes accordingly.
                                    $lessonActivity = $activity->lesson;
                                    $lessonUsers = $lessonActivity ? $lessonActivity->users->pluck('id')->toArray() : [];
                                    if ($activity->lesson_id !== null) {
                                        if (!$isTeacher) {
                                            if (!in_array($user->id, $lessonUsers)) {
                                                $activityClass .= ' calendar-event-highlight-disabled';
                                            } else {
                                                $activityClass .= ' calendar-event-lesson';
                                            }
                                        } else {
                                            $activityClass .= ' calendar-event-lesson';
                                        }
                                    }

                                    $activityImage = $activity->image;
                                    $activityContent = $activity->content;
                                    $activityTitle = $activity->title;
                                    $activitiesStart = Carbon::parse($activity->date_start);
                                    $activitiestart = Carbon::parse($activity->date_start);
                                    $activityEnd   = Carbon::parse($activity->date_end);
                                    if ($activitiestart->isSameDay($activityEnd)) {
                                        $formattedStart = $activitiestart->format('H:i');
                                        $formattedEnd = $activityEnd->format('H:i');
                                    } else {
                                        $formattedStart = $activitiestart->format('d-m H:i');
                                        $formattedEnd = $activityEnd->format('d-m H:i');
                                    }

                                    // Build route parameters and pass the occurrence's start date.
                                    $routeParams = [
                                        'month'     => $monthOffset,
                                        'all'       => $wantViewAll,
                                        'view'      => 'month',
                                        'startDate' => $activitiesStart->format('Y-m-d'),
                                        'id'        => $activity->id,
                                    ];
                                    if ($activity->lesson_id !== null) {
                                        $routeParams['lessonId'] = $lessonActivity->id;
                                    }

                                    // Construct a composite key to fetch the correct positioning.
                                    $compositeKey = $activity->id . '-' . $activitiesStart->format('Y-m-d');

    // Calculate contrast color logic inline
    $textColor = '#000000'; // Default black
    if (isset($activity->color) && !empty($activity->color)) {
        $hex = ltrim($activity->color, '#');
        // Handle standard #RRGGBB and shorthand #RGB
        if (ctype_xdigit($hex) && (strlen($hex) == 6 || strlen($hex) == 3)) {
            if (strlen($hex) == 3) {
                $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
            }
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            // YIQ brightness formula
            $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
            $textColor = ($yiq >= 128) ? '#000000' : '#ffffff';
        }
    }
                                @endphp

                                <a @if($activity->isBooking) href="{{ route('admin.bookings.details', [$activity->id, 'location' => 'agenda']) }}"
                                   @else href="{{ route('agenda.activity', $routeParams) }}" @endif
                                   style="top: {{ 40 + ($activityPositions[$compositeKey] ?? 0) * 35 }}px; @if(isset($activity->color)) background-color: {{ $activity->color }} !important; color: {{ $textColor }} !important; @endif"
                                   data-event-id="{{ $activity->id }}"
                                   data-event-start="{{ $formattedStart }}"
                                   data-event-start-date="{{ $activitiesStart->format('Y-m-d') }}"
                                   data-event-end="{{ $formattedEnd }}"
                                   @if(isset($activityImage))
                                       data-image="{{ asset('files/agenda/agenda_images/'.$activityImage) }}"
                                   @endif
                                   data-content="{{ \Str::limit(strip_tags(html_entity_decode($activityContent)), 200, '...') }}"
                                   data-title="{{ $activityTitle }}"
                                   class="{{ $activityClass }}">
                                    @if ($isFirstDay || ($isMonday && !$isLastDay))
                                        <div class="calendar-event-title">
                                            <span>{{ $activityTitle }} </span>
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
