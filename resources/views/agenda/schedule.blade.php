@extends('layouts.dashboard')

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

        <div class="d-flex flex-row-responsive align-items-center gap-5" style="width: 100%">
            <div class="" style="width: 100%;">
                <div class="d-flex flex-row justify-content-between align-items-center">
                    <h1 class="">Evenementen</h1>
                    @if($user && $user->roles->contains('role', 'Administratie'))
                        <a href="{{ route('agenda.new', ['month' => $monthOffset, 'all' => $wantViewAll, 'view' => 'schedule']) }}"
                           class="d-flex flex-row align-items-center justify-content-center btn btn-info">
                            <span
                                class="material-symbols-rounded me-2">calendar_add_on</span>
                            <span class="no-mobile">Evenement toevoegen</span></a>
                    @endif
                </div>

                <script>
                    let showAll = document.getElementById('show-all')
                    showAll.addEventListener('change', function () {
                        // Get the current URL
                        const url = new URL(window.location.href);

                        // Update the 'all' parameter based on the checkbox state
                        url.searchParams.set('all', this.checked ? 'true' : 'false');

                        // Optionally redirect to the new URL
                        window.location.href = url.toString();

                        // Log the new URL (for debugging purposes)
                        console.log("New URL:", url.toString());
                    });
                </script>

            </div>
        </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item" aria-current="page"><a href="{{route('admin')}}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Evenementen</li>
                </ol>
            </nav>

        <div class="d-flex justify-content-between align-items-center">
            @if(!isset($lesson))
                <div class="d-flex flex-row gap-0">
                    <a href="{{ route('agenda.schedule', ['month' => $monthOffset - 1, 'all' => $wantViewAll]) }}#agenda"
                       class="btn d-flex align-items-center justify-content-center">
                        <span class="material-symbols-rounded">arrow_back_ios</span>
                    </a>
                    <a href="{{ route('agenda.schedule', ['month' => 0, 'all' => $wantViewAll]) }}#agenda"
                       class="btn d-flex align-items-center justify-content-center">
                        <span class="material-symbols-rounded">home</span>
                    </a>
                    <a href="{{ route('agenda.schedule', ['month' => $monthOffset + 1, 'all' => $wantViewAll]) }}#agenda"
                       class="btn d-flex align-items-center justify-content-center">
                        <span class="material-symbols-rounded">arrow_forward_ios</span>
                    </a>
                </div>
            @else
                <div class="d-flex flex-row gap-0">
                    <a href="{{ route('agenda.schedule', ['month' => $monthOffset - 1, 'all' => $wantViewAll, 'lessonId' => $lesson->id]) }}#agenda"
                       class="btn d-flex align-items-center justify-content-center">
                        <span class="material-symbols-rounded">arrow_back_ios</span>
                    </a>
                    <a href="{{ route('agenda.schedule', ['month' => 0, 'all' => $wantViewAll, 'lessonId' => $lesson->id]) }}#agenda"
                       class="btn d-flex align-items-center justify-content-center">
                        <span class="material-symbols-rounded">home</span>
                    </a>
                    <a href="{{ route('agenda.schedule', ['month' => $monthOffset + 1, 'all' => $wantViewAll, 'lessonId' => $lesson->id]) }}#agenda"
                       class="btn d-flex align-items-center justify-content-center">
                        <span class="material-symbols-rounded">arrow_forward_ios</span>
                    </a>
                </div>
            @endif
            <div>
                <h2>{{ $monthName }} {{ $year }}</h2>
            </div>
        </div>

        @if(count($activities) > 0)
            @php
                $currentMonth = null;
            @endphp

            @foreach ($activities as $activity)
                @php
                    $activitiesStart = Carbon::parse($activity->date_start);
                    $activityEnd = Carbon::parse($activity->date_end);

                    $activityMonth = $activitiesStart->translatedFormat('F');

                    $lessonActivity = $activity->lesson; // Load the related lesson
                    $lessonUsers = $lessonActivity ? $lessonActivity->users->pluck('id')->toArray() : [];

                    $activityClass = '';

                    if ($activity->lesson_id !== null) {
                                            if (!$isTeacher) {
                                                // If the user is not a teacher, check if the user is in the lesson
                                                if (!in_array($user->id, $lessonUsers)) {
                                                    // User is not in the lesson users, disable the event highlight
                                                    $activityClass .= ' schedule-event-highlight-disabled';
                                                } else {
                                                    // User is in the lesson users, mark as a lesson-related event
                                                    $activityClass .= ' schedule-event-lesson';
                                                }
                                            } else {
                                                // If the user is a teacher, always mark as a lesson-related event
                                                $activityClass .= ' schedule-event-lesson';
                                            }
                                        }
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
                        'all' => $wantViewAll,
                        'startDate' => $activitiesStart->format('Y-m-d'),
                        'view' => 'schedule',
                    ];

                    if ($activity->lesson_id !== null) {
                        $linkParams['lessonId'] = $activity->lesson_id;
                    }

                    $canAccessLesson = $activity->lesson_id === null || in_array($user->id, $lessonUsers) || $isTeacher;
                @endphp

                <a
                    @if ($canAccessLesson)
                        href="{{ route('agenda.activity', $linkParams) }}"
                    @endif
                    style="color: unset; text-decoration: none"
                >


                    <div class="d-flex flex-row">
                        <div style="width: 50px"
                             class="d-flex flex-column gap-0 align-items-center justify-content-center">
                            <p class="day-name">{{ mb_substr($activitiesStart->translatedFormat('l'), 0, 2) }}</p>
                            <p class="day-number">{{ $activitiesStart->format('j') }}</p>
                        </div>
                        <div
                            class="event mt-2 w-100 d-flex flex-row-responsive-reverse justify-content-between {{$activityClass}}">
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
@endsection
