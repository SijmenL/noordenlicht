@extends('layouts.dashboard')
@include('partials.editor')

@php
    use Carbon\Carbon;
    Carbon::setLocale('nl');
@endphp

@php
    $eventStart = Carbon::parse($activity->date_start);
    $eventEnd = Carbon::parse($activity->date_end);

    $eventMonth = $eventStart->translatedFormat('F');

    // --- Price Calculation Logic (UNCHANGED) ---
    $allPrices = $activity->prices->map(fn($p) => $p->price);

    $basePrices = $allPrices->where('type', 0);
    $percentageAdditions = $allPrices->where('type', 1);
    $fixedDiscounts = $allPrices->where('type', 2);
    $extraCosts = $allPrices->where('type', 3);
    $percentageDiscounts = $allPrices->where('type', 4);

    $totalBasePrice = $basePrices->sum('amount');

 $preDiscountVatAmount = 0;
                    foreach ($percentageAdditions as $percentage) {
                        $preDiscountVatAmount += $totalBasePrice * ($percentage->amount / 100);
                    }
                    $preDiscountPrice = $totalBasePrice + $preDiscountVatAmount;

    // 1. Discounts
                    $priceAfterDiscounts = $totalBasePrice;
                    $totalPercentageDiscounts = 0;

                    foreach ($percentageDiscounts as $percentage) {
                        $priceAfterDiscounts -= $totalBasePrice * ($percentage->amount / 100);
                        $totalPercentageDiscounts += $percentage->amount;
                    }
                    $priceAfterDiscounts -= $fixedDiscounts->sum('amount');

                    $taxableAmount = max($priceAfterDiscounts, 0);

                    // 2. Additions (VAT)
                    $totalVatAmount = 0;
                    $totalPercentageAdditions = 0; // Sum of percentage rates
                    foreach ($percentageAdditions as $percentage) {
                        $totalVatAmount += $taxableAmount * ($percentage->amount / 100);
                        $totalPercentageAdditions += $percentage->amount;
                    }

                    $priceInclVat = $taxableAmount + $totalVatAmount;

                    // 3. Extras
                    $calculatedPrice = $priceInclVat;

                    $hasDiscount = $fixedDiscounts->isNotEmpty() || $percentageDiscounts->isNotEmpty();
                    // --- End Price Calculation ---

@endphp

@section('content')
    @if($user && $user->roles->contains(function ($role) {
                               return in_array($role->role, [
                                   'Dolfijnen Leiding', 'Zeeverkenners Leiding', 'Loodsen Stamoudste',
                                   'Loods', 'Afterloodsen Organisator', 'Administratie', 'Bestuur',
                                   'Praktijkbegeleider', 'Loodsen Mentor', 'Ouderraad'
                               ]);
                           }) || $isTeacher)
        <div id="popUpSubmission" class="popup"
             style="margin-top: -122px; display: none; z-index: 99999; top: 681px; left: 0; position: absolute">
            <div class="popup-body" style="width: 97vw; height: 95vh">
                <h2>Inschrijvingen {{ $activity->title }}</h2>
                <div class="w-100" style="height: 100%; min-height: 0px">
                    <iframe width="100%" height="100%" style="border: none;"
                            src="{{ route('agenda.submissions.activity', ['id' => $activity->id]) . '?startDate=' .  date("Y-m-d", strtotime($activity->date_start)) }}">
                    </iframe>

                </div>
                <div class="button-container">
                    <a id="close-submission-popup" class="btn btn-outline-danger"><span
                            class="material-symbols-rounded">close</span></a>
                </div>
            </div>
        </div>
    @endif

    <div class="container col-md-11">
        <div class="d-flex flex-row justify-content-between align-items-center">
            <div class="d-flex flex-column gap-3">
                <h1>{{ $activity->title }}</h1>

            </div>

            <div>
                @if($activity->user_id === \Illuminate\Support\Facades\Auth::id() ||
                     $user->roles->contains('role', 'Dolfijnen Leiding') ||
                     $user->roles->contains('role', 'Zeeverkenners Leiding') ||
                     $user->roles->contains('role', 'Loodsen Stamoudste') ||
                     $user->roles->contains('role', 'Afterloodsen Organisator') ||
                     $user->roles->contains('role', 'Administratie') ||
                     $user->roles->contains('role', 'Bestuur') ||
                     $user->roles->contains('role', 'Praktijkbegeleider'))
                    <a href="@if(!isset($lesson)){{ route('agenda.edit.activity', ['id' => $activity->id, 'startDate' => $activity->date_start->format('Y-m-d'), 'month' => $month, 'all' => $wantViewAll, 'view' => $view]) }}"
                @else
                    {{ route('agenda.edit.activity', [$activity->id, 'startDate' => $activity->date_start->format('Y-m-d'), 'lessonId' => $lesson->id, 'month' => $month, 'all' => $wantViewAll, 'view' => $view]) }}
                @endif"
                class="d-flex flex-row align-items-center justify-content-center btn btn-info">
                <span
                    class="material-symbols-rounded me-2">edit</span>
                <span class="no-mobile">Bewerk activiteit</span></a>
                @endif
            </div>
        </div>

        @if(isset($lesson))
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('lessons') }}">Lessen</a></li>
                    <li class="breadcrumb-item"><a
                            href="{{ route('lessons.environment.lesson', $lesson->id) }}">{{ $lesson->title }}</a>
                    </li>

                    <li class="breadcrumb-item"><a
                            @if($view === 'month') href="{{ route('agenda.month', ['month' => $month, 'all' => $wantViewAll ? 1 : 0, 'lessonId' => $lesson->id]) }}"
                            @else href="{{ route('agenda.schedule', ['month' => $month, 'all' => $wantViewAll ? 1 : 0, 'lessonId' => $lesson->id]) }}" @endif>Planning</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $activity->title }}</li>
                </ol>
            </nav>
        @else
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item" aria-current="page"><a href="{{route('admin')}}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a
                            @if($view === 'month') href="{{ route('agenda.month', ['month' => $month, 'all' => $wantViewAll ? 1 : 0]) }}"
                            @else href="{{ route('agenda.schedule', ['month' => $month, 'all' => $wantViewAll ? 1 : 0]) }}" @endif>
                            @if($view === 'month')
                                Agenda
                            @else
                                Evenementen
                            @endif</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $activity->title }}</li>
                </ol>
            </nav>
        @endif

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

        <div class="bg-light rounded-2 w-100 d-flex flex-column align-items-center">
            <div class="p-3 w-100 d-flex align-items-center flex-column">
                <h1 class="text-center">{{ $activity->title }}</h1>
                @if($eventStart->isSameDay($eventEnd))
                    <h2 class="text-center">{{ $eventStart->format('j') }} {{ $eventStart->translatedFormat('F') }}
                        @ {{ $eventStart->format('H:i') }} - {{ $eventEnd->format('H:i') }}</h2>
                @else
                    <h2 class="text-center">{{ $eventStart->format('d-m-Y') }} tot {{ $eventEnd->format('d-m-Y') }}</h2>
                @endif
                @if(isset($activity->image))
                    <img class="mt-3 zoomable-image"
                         style="width: 100%; max-width: 400px; object-fit: cover; object-position: center;"
                         alt="Activiteit Afbeelding"
                         src="{{ asset('files/agenda/agenda_images/'.$activity->image) }}">
                @endif

                <div class="mt-4 w-100 agenda-content" style="align-self: start">{!! $activity->content !!}</div>
            </div>


            <div class="bg-white w-100 p-4 rounded">
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
                    @if(isset($activity->location))
                        <div>
                            <h4 class="mb-2">Locatie</h4>
                            <p class="m-0">{{ $activity->location }}</p>
                        </div>
                    @endif
                    @if(isset($activity->organisator))
                        <div>
                            <h4 class="mb-2">Organisator</h4>
                            <p class="m-0">{{ $activity->organisator }}</p>
                        </div>
                    @endif

                    @if($calculatedPrice !== 0)
                        <div>
                            <h4 class="mb-2">Tickets</h4>

                            @if($activity->max_tickets !== null)
                                <p class="m-0">{{ $activity->ticketsSold() }} tickets verkocht van
                                    de {{ $activity->max_tickets }}</p>
                            @else
                                <p class="m-0">{{ $activity->ticketsSold() }} tickets verkocht</p>

                            @endif
                        </div>
                    @endif
                    <div>
                        <h4 class="mb-2">Prijs</h4>
                        @if($hasDiscount)
                            <p class="badge bg-success mb-2">
                                @if($totalPercentageDiscounts > 0)
                                    {{ $totalPercentageDiscounts }}%
                                @endif

                                @if($totalPercentageDiscounts > 0 && $fixedDiscounts->sum('amount') > 0) én @endif

                                @if($fixedDiscounts->sum('amount') > 0)
                                    -€{{ $fixedDiscounts->sum('amount') }}
                                @endif
                                korting!
                            </p>

                            <div class="d-flex flex-row gap-2 align-items-baseline">
                                <h3 class="d-inline-block text-muted"
                                    style="text-decoration: line-through; opacity: 0.6; font-size: 1.2rem;">
                                    &#8364;{{ number_format($preDiscountPrice, 2, ',', '.') }}
                                </h3>
                                @else
                                    <div class="">
                                        @endif

                                        <h3 class="d-inline-block fw-bold text-primary">
                                            &#8364;{{ number_format($calculatedPrice, 2, ',', '.') }}
                                        </h3>
                                    </div>

                                    {{-- Price Breakdown --}}
                                    @if($totalPercentageAdditions !== 0)
                                        <p class="text-muted small mb-0 mt-1">
                                            (incl.
                                            @foreach($percentageAdditions as $index => $cost)
                                                {{ $cost->name }} {{ $cost->amount }}%
                                                (@if($cost->amount > 0)
                                                    +
                                                @endif
                                                &#8364;{{ number_format($totalBasePrice * ($cost->amount / 100), 2) }}
                                                )@if(!$loop->last)
                                                    ,
                                                @endif
                                            @endforeach
                                            )
                                        </p>
                                    @endif

                                    @if($extraCosts->isNotEmpty())
                                        <p class="text-muted small mb-0 mt-1">
                                            (excl.
                                            @foreach($extraCosts as $index => $cost)
                                                {{ $cost->name }}
                                                &#8364;{{ number_format($cost->amount, 2, ',', '.') }}@if(!$loop->last)
                                                    ,
                                                @endif
                                            @endforeach
                                            )
                                        </p>
                                    @endif
                            </div>
                    </div>
                </div>
            </div>

            @if(isset($activity->presence) && $activity->presence !== "0")
                @php
                    $presenceDate = $activity->presence;
                    $today = now(); // Get the current date
                @endphp


                <div class="bg-white w-100 p-4 rounded mt-3" id="presence">
                    @if($activity->presence !== "1" && \Carbon\Carbon::parse($activity->presence)->lessThan($today))
                        <!-- If presence is not 1 and the deadline has passed, show the alert -->
                        <h2 class="flex-row gap-3">
                            <span class="material-symbols-rounded me-2">emoji_people</span>Aanwezigheid
                        </h2>

                        <div class="alert alert-danger d-flex align-items-center mt-4" role="alert">
                            <span class="material-symbols-rounded me-2">event_busy</span>
                            Voor deze activiteit kan je je niet meer aan of afmelden. De deadline was
                            op {{ \Carbon\Carbon::parse($activity->presence)->format('d-m-Y H:i') }}
                        </div>

                    @else
                        <!-- Else, show the availability buttons if presence is "1" or the deadline hasn't passed -->
                        <h2 class="flex-row gap-3">
                            <span class="material-symbols-rounded me-2">emoji_people</span>Aanwezigheid
                        </h2>

                        @if($activity->presence !== "1" && \Carbon\Carbon::parse($activity->presence)->greaterThan($today))
                            <p>
                                De deadline om je aan of af te melden voor deze activiteit is op
                                <strong>{{ \Carbon\Carbon::parse($activity->presence)->format('d-m-Y H:i') }}</strong>.
                            </p>
                        @endif


                        @if($canAlwaysView)
                            <p>Meld je hier aan of af voor {{ $activity->title }}.</p>

                            <!-- Parent's own presence status -->
                            <div>
                                <p><strong>Jouw aanwezigheid:</strong></p>

                                <div class="d-flex flex-row-responsive gap-2">
                                    <a
                                        @if($presenceStatus !== "1")
                                            href="{{ route('agenda.activity.present', [$activity->id, $user->id]) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                                        @endif
                                        class="d-flex flex-row align-items-center justify-content-center btn @if($presenceStatus === "1") btn-success @else btn-outline-success @endif">
                                        <span class="material-symbols-rounded me-2">event_available</span>
                                        <span>Aanmelden</span>
                                    </a>
                                    <a
                                        @if($presenceStatus !== "0")
                                            href="{{ route('agenda.activity.absent', [$activity->id, $user->id]) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                                        @endif
                                        class="d-flex flex-row align-items-center justify-content-center btn @if($presenceStatus === "0") btn-danger @else btn-outline-danger @endif">
                                        <span class="material-symbols-rounded me-2">event_busy</span>
                                        <span>Afmelden</span>
                                    </a>
                                </div>
                            </div>
                        @endif

                        <!-- Handle children presence -->

                    @endif
                    <div class="d-flex flex-row-responsive mt-4">
                        @if($user && $user->roles->contains(function ($role) {
                            return in_array($role->role, [
                                'Dolfijnen Leiding', 'Zeeverkenners Leiding', 'Loodsen Stamoudste',
                                'Loods', 'Afterloodsen Organisator', 'Administratie', 'Bestuur',
                                'Praktijkbegeleider', 'Loodsen Mentor', 'Ouderraad'
                            ]);
                        }) || $isTeacher)
                            <div id="presence-button"
                                 class="d-flex flex-row align-items-center justify-content-center btn btn-info">
                                <span class="material-symbols-rounded me-2">free_cancellation</span>
                                <span>Bekijk alle aan- of afmeldingen</span>
                            </div>

                            <script>
                                let presenceButton = document.getElementById('presence-button');
                                let body = document.getElementById('app');
                                let html = document.querySelector('html');
                                let popUpPresence = document.getElementById('popUpPresence');

                                presenceButton.addEventListener('click', function () {
                                    openPresencePopup();
                                });

                                closePresenceButton = document.getElementById('close-presence-popup');
                                closePresenceButton.addEventListener('click', closePresencePopup);

                                function openPresencePopup() {
                                    let scrollPosition = window.scrollY;
                                    html.classList.add('no-scroll');
                                    window.scrollTo(0, scrollPosition);
                                    popUpPresence.style.display = 'flex';
                                }

                                function closePresencePopup() {
                                    popUpPresence.style.display = 'none';
                                    html.classList.remove('no-scroll');
                                }

                            </script>
                        @endif
                    </div>
                </div>
        </div>
        @endif



        @if($activity->formElements->count() > 0)
            <div class="bg-white w-100 p-4 rounded mt-3">
                <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">app_registration</span>Inschrijfformulier
                </h2>
                <form action="{{ route('agenda.activity.submit', $activity->id) }}" method="POST">
                    @csrf

                    @foreach ($activity->formElements as $formElement)
                        @php
                            $options = $formElement->option_value ? explode(',', $formElement->option_value) : [];
                            $oldValue = old('form_elements.' . $formElement->id);
                        @endphp

                        <div class="form-group">
                            <label
                                for="formElement{{ $formElement->id }}">{{ $formElement->label }} @if($formElement->is_required)
                                    <span class="required-form">*</span>
                                @endif</label>

                            @switch($formElement->type)
                                @case('text')
                                @case('email')
                                @case('number')
                                @case('date')
                                    <input type="{{ $formElement->type }}"
                                           id="formElement{{ $formElement->id }}"
                                           name="form_elements[{{ $formElement->id }}]"
                                           class="form-control"
                                           value="{{ $oldValue ?? '' }}"
                                        {{ $formElement->is_required ? 'required' : '' }}>
                                    @break

                                @case('select')
                                    <select id="formElement{{ $formElement->id }}"
                                            name="form_elements[{{ $formElement->id }}]"
                                            class="form-select w-100"
                                        {{ $formElement->is_required ? 'required' : '' }}>
                                        <option value="">Selecteer een optie</option>
                                        @foreach ($options as $option)
                                            <option value="{{ $option }}"
                                                {{ $oldValue == $option ? 'selected' : '' }}>
                                                {{ $option }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @break

                                @case('radio')
                                    @foreach ($options as $option)
                                        <div class="form-check">
                                            <input type="radio"
                                                   id="formElement{{ $formElement->id }}_{{ $loop->index }}"
                                                   name="form_elements[{{ $formElement->id }}]"
                                                   value="{{ $option }}"
                                                   class="form-check-input"
                                                {{ $oldValue == $option ? 'checked' : '' }}
                                                {{ $formElement->is_required ? 'required' : '' }}>
                                            <label for="formElement{{ $formElement->id }}_{{ $loop->index }}"
                                                   class="form-check-label">
                                                {{ $option }}
                                            </label>
                                        </div>
                                    @endforeach
                                    @break

                                @case('checkbox')
                                    @php
                                        $oldValues = is_array($oldValue) ? $oldValue : [];
                                    @endphp
                                    @foreach ($options as $option)
                                        <div class="form-check">
                                            <input type="checkbox"
                                                   id="formElement{{ $formElement->id }}_{{ $loop->index }}"
                                                   name="form_elements[{{ $formElement->id }}][]"
                                                   value="{{ $option }}"
                                                   class="form-check-input">
                                            <label for="formElement{{ $formElement->id }}_{{ $loop->index }}"
                                                   class="form-check-label">{{ $option }}</label>
                                        </div>
                                    @endforeach
                                    @break
                            @endswitch

                            @if ($errors->has('form_elements.' . $formElement->id))
                                <div class="invalid-feedback">
                                    {{ $errors->first('form_elements.' . $formElement->id) }}
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <button
                        onclick="function handleButtonClick(button) {
                                 button.disabled = true;
                                button.classList.add('loading');

                                // Show the spinner and hide the text
                                button.querySelector('.button-text').style.display = 'none';
                                button.querySelector('.loading-spinner').style.display = 'inline-block';
                                button.querySelector('.loading-text').style.display = 'inline-block';

                                button.closest('form').submit();
                            }
                            handleButtonClick(this)"
                        class="btn btn-success mt-3 flex flex-row align-items-center justify-content-center">
                        <span class="button-text">Opslaan</span>
                        <span style="display: none" class="loading-spinner spinner-border spinner-border-sm"
                              aria-hidden="true"></span>
                        <span style="display: none" class="loading-text" role="status">Laden...</span>
                    </button>
                </form>

                <div class="d-flex flex-row-responsive mt-4">
                    @if($user && $user->roles->contains(function ($role) {
                        return in_array($role->role, [
                            'Dolfijnen Leiding', 'Zeeverkenners Leiding', 'Loodsen Stamoudste',
                            'Loods', 'Afterloodsen Organisator', 'Administratie', 'Bestuur',
                            'Praktijkbegeleider', 'Loodsen Mentor', 'Ouderraad'
                        ]);
                    }) || $isTeacher)
                        <div id="submission-button"
                             class="d-flex flex-row align-items-center justify-content-center btn btn-info">
                            <span class="material-symbols-rounded me-2">inbox</span>
                            <span>Bekijk alle inschrijvingen</span>
                        </div>

                        <script>
                            let submissionButton = document.getElementById('submission-button');
                            let submissionPopUp = document.getElementById('popUpSubmission');
                            let body = document.getElementById('app');
                            let html = document.querySelector('html');

                            submissionButton.addEventListener('click', function () {
                                openSubmissionPopup();
                            });

                            closeButtonSubmission = document.getElementById('close-submission-popup');
                            closeButtonSubmission.addEventListener('click', closeSubmissionPopup);

                            function openSubmissionPopup() {
                                let scrollPosition = window.scrollY;
                                html.classList.add('no-scroll');
                                window.scrollTo(0, scrollPosition);
                                submissionPopUp.style.display = 'flex';
                            }

                            function closeSubmissionPopup() {
                                submissionPopUp.style.display = 'none';
                                html.classList.remove('no-scroll');
                            }

                        </script>
                    @endif
                </div>
            </div>

        @endif

        @php
            $currentTickets = $activity->tickets;
            // If it is a recurring activity, filter tickets to only show those for the current occurrence date.
            // The controller has already updated $activity->date_start to the occurrence date.
            if ($activity->recurrence_rule && $activity->recurrence_rule !== 'never') {
                $currentOccurrenceDate = \Carbon\Carbon::parse($activity->date_start)->startOfDay();
                $currentTickets = $activity->tickets->filter(function ($ticket) use ($currentOccurrenceDate) {
                    if (empty($ticket->start_date)) {
                        return false;
                    }
                    return \Carbon\Carbon::parse($ticket->start_date)->startOfDay()->equalTo($currentOccurrenceDate);
                });
            }
        @endphp

        @if($currentTickets->count() > 0)
            <div class="bg-white w-100 p-4 rounded mt-3">
                <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">local_activity</span>Verkochte tickets
                </h2>
                <p>{{ $currentTickets->count() }} @if($currentTickets->count() == 1)ticket @else tickets @endif verkocht van de {{ $activity->max_tickets }}</p>

                <div class="" style="max-width: 100vw">
                    <table class="table table-striped">
                        <thead class="thead-dark table-bordered table-hover">
                        <tr>
                            <th scope="col">Ticket ID</th>
                            <th scope="col">Eigenaar</th>
                            <th scope="col">Datum</th>
                            <th scope="col">Status</th>
                            <th scope="col">Opties</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach ($currentTickets as $ticket)
                            <tr>
                                <th>
                                <span title="{{ $ticket->uuid }}">
                                    {{ substr($ticket->uuid, 0, 8) }}...
                                </span>
                                </th>

                                <td>
                                    @if($ticket->user)
                                        {{ $ticket->user->name }}
                                    @else
                                        {{ $ticket->order->first_name }} {{ $ticket->order->last_name }}

                                    @endif
                                </td>
                                <td>{{ $ticket->created_at->format('d-m-Y H:i') }}</td>
                                <td>
                                    @php
                                        $statusClass = match($ticket->status) {
                                            'valid' => 'success',
                                            'used' => 'secondary',
                                            'pending' => 'warning',
                                            'cancelled' => 'danger',
                                            default => 'info'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }}">
                                    @if($ticket->status == 'valid') Geldig
                                        @elseif($ticket->status == 'used') Gebruikt
                                        @elseif($ticket->status == 'pending') In afwachting
                                        @elseif($ticket->status == 'warning') Waarschuwing
                                        @elseif($ticket->status == 'cancelled') Niet geldig
                                        @else
                                            {{ ucfirst($ticket->status) }}
                                        @endif
                                </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            Opties
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a href="{{ route('admin.tickets.details', $ticket->uuid) }}" class="dropdown-item">
                                                    Bekijk details
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('ticket.download', $ticket->uuid) }}" class="dropdown-item">
                                                    Download PDF
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

            </div>

        @endif

    </div>
@endsection
