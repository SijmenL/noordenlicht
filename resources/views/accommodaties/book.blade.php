@extends('layouts.app')
@include('partials.editor')
@vite(['resources/js/texteditor.js', 'resources/js/search-user.js', 'resources/css/texteditor.css'])

@section('content')

    <div class="rounded-bottom-5 bg-light mt-5 pb-5"
         style="position: relative; margin-top: 0 !important; padding-top: 50px; padding-bottom: 50px; background-image: url('{{ asset('img/logo/doodles/Blad StretchA_white.webp') }}'); background-repeat: repeat; background-size: cover;">

        <div class="container">


            <h1 class="fw-bold mb-4 text-center">Boek je verblijf: {{ $accommodatie->name }}</h1>
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

            <div class="row justify-content-center mb-5">
                <div class="col-md-10 col-lg-8">
                    <div class="position-relative d-flex justify-content-between align-items-center">
                        <div class="position-absolute w-100 start-0 translate-middle-y bg-secondary-subtle"
                             style="height: 4px; top: 30%; z-index: 0;"></div>
                        <div class="position-absolute start-0 translate-middle-y bg-primary transition-width"
                             style="height: 4px; top: 30%; z-index: 0; width: 0%;" id="progress-line"></div>

                        @foreach(['Datum', 'Tijd', 'Extra\'s', 'Details', 'Overzicht', 'Betalen'] as $index => $label)
                            {{-- Hide 'Details' (form step) circle initially, show via JS if needed, or keep all valid --}}
                            {{-- Adjusted the step count to include the new potential form step --}}
                            <div
                                class="d-flex flex-column align-items-center position-relative z-1 {{ $label == 'Details' ? 'step-indicator-details d-none' : '' }}"
                                id="step-indicator-container-{{ $index+1 }}">
                                <div
                                    class="step-circle bg-{{ $index == 0 ? 'primary' : 'white' }} text-{{ $index == 0 ? 'white' : 'secondary' }} d-flex justify-content-center align-items-center rounded-circle fw-bold shadow-sm"
                                    style="width: 40px; height: 40px;"
                                    id="step-circle-{{ $index+1 }}">{{ $index+1 }}</div>
                                <span
                                    class="small mt-2 bg-light px-2 {{ $index == 0 ? 'fw-bold' : 'text-muted' }}">{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-lg border-0 rounded-5 overflow-hidden">
                <div class="p-0">
                    <form id="booking-form" action="{{ route('accommodatie.store_booking') }}" method="POST">
                        @csrf
                        <input type="hidden" name="accommodatie_id" value="{{ $accommodatie->id }}">
                        <input type="hidden" name="supplements_data" id="supplements_data_input">

                        <input type="hidden" name="start_date" id="input_start_date">
                        <input type="hidden" name="start_time" id="input_start_time">
                        <input type="hidden" name="end_date" id="input_end_date">
                        <input type="hidden" name="end_time" id="input_end_time">

                        {{-- Step 1: Calendar --}}
                        <div id="step-content-1" class="step-content p-4 p-md-5">
                            <h3 class="h4 fw-bold mb-4 text-primary">Kies een datum</h3>

                            <div class="calendar-wrapper position-relative">
                                <div id="calendar-loading"
                                     class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex justify-content-center align-items-center"
                                     style="z-index: 50; display: none; pointer-events: none">
                                    <div class="spinner-border text-primary" role="status"></div>
                                </div>

                                <div id="calendar-view">
                                    <div
                                        class="d-flex justify-content-between align-items-center mb-4 bg-light p-3 rounded">
                                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3"
                                                onclick="prevMonth()">Vorige
                                        </button>
                                        <h4 class="mb-0 fw-bold text-uppercase" id="calendar-month-year"></h4>
                                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3"
                                                onclick="nextMonth()">Volgende
                                        </button>
                                    </div>
                                    <div class="d-grid grid-cols-7 gap-2 mb-2 text-center fw-bold text-muted small">
                                        <div>MA</div>
                                        <div>DI</div>
                                        <div>WO</div>
                                        <div>DO</div>
                                        <div>VR</div>
                                        <div>ZA</div>
                                        <div>ZO</div>
                                    </div>
                                    <div id="calendar-grid" class="d-grid grid-cols-7 gap-2"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Step 2: Time --}}
                        <div id="step-content-2" class="step-content p-4 p-md-5 d-none">
                            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                                <h4 class="fw-bold mb-0 text-primary" id="selected-date-display"></h4>
                            </div>

                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i> Sleep om te
                                    selecteren.</p>
                                <span class="badge bg-light text-secondary border d-md-none">Scroll rechts &rarr;</span>
                            </div>

                            <div class="timeline-wrapper bg-light border rounded position-relative"
                                 style="height: 400px; overflow: hidden; display: flex; flex-direction: column;">
                                <div class="timeline-scroll-area flex-grow-1 position-relative" id="timeline-container"
                                     style="overflow-y: auto; user-select: none; background: white; -webkit-overflow-scrolling: touch;">
                                    <div class="timeline-labels position-absolute top-0 start-0 border-end bg-white"
                                         style="width: 60px; z-index: 10;"></div>
                                    <div class="timeline-grid position-absolute top-0 start-0 w-100 ps-5"
                                         id="timeline-grid" style="padding-left: 60px !important;">
                                        <div id="timeline-lines-layer"
                                             class="position-absolute top-0 start-0 w-100 h-100"></div>
                                        <div id="timeline-events-layer"
                                             class="position-absolute top-0 start-0 w-100 pointer-events-none"></div>
                                        <div id="timeline-selection"
                                             class="timeline-selection position-absolute bg-primary bg-opacity-25 border border-primary text-center text-primary fw-bold d-flex align-items-center justify-content-center"
                                             style="display: none !important; left: 60px; right: 0; pointer-events: none; z-index: 20;">
                                            <span class="small bg-white px-2 rounded shadow-sm">Selectie</span>
                                        </div>
                                        <div id="timeline-interaction-layer" class="position-absolute top-0 start-0"
                                             style="left: 60px; width: calc(85% - 60px); cursor: crosshair; z-index: 30; touch-action: none;"></div>
                                        <div
                                            class="position-absolute top-0 end-0 h-100 bg-secondary bg-opacity-10 border-start d-flex justify-content-center d-md-none"
                                            style="width: 15%; z-index: 25;">
                                            <div class="mt-4 text-muted opacity-50 small"
                                                 style="writing-mode: vertical-rl; text-orientation: mixed;">SCROLL
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4 mt-3">
                                <div class="col-6">
                                    <label class="form-label fw-bold">Start</label>
                                    <input type="time" class="form-control" id="time-start"
                                           onchange="manualTimeChange()">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold">Eind</label>
                                    <input type="time" class="form-control" id="time-end" onchange="manualTimeChange()">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-5">
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                                        onclick="goToStep(1)">Vorige
                                </button>
                                <button type="button" class="btn btn-primary rounded-pill px-5" id="btn-confirm-date"
                                        disabled onclick="confirmDate()">
                                    Bevestig Periode
                                </button>
                            </div>
                        </div>

                        {{-- Step 3: Extras --}}
                        <div id="step-content-3" class="step-content p-4 p-md-5 d-none">
                            <h3 class="h4 fw-bold mb-4 text-primary">Voeg extra's toe</h3>
                            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                                @foreach($supplements as $supplement)
                                    <div class="col">
                                        <div
                                            class="shop-tile h-100 d-flex flex-column bg-white overflow-hidden border rounded-4 position-relative group-hover-trigger">
                                            @if($supplement->image)
                                                <div class="tile-image-wrapper" style="height: 180px; cursor: pointer;"
                                                     onclick="showProductDetails({{ $supplement->id }})">
                                                    <img src="{{ asset('/files/products/images/'.$supplement->image) }}"
                                                         class="w-100 h-100 object-fit-cover">
                                                </div>
                                            @endif

                                            <div class="p-4 d-flex flex-column flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h5 class="fw-bold mb-0" style="cursor: pointer;"
                                                        onclick="showProductDetails({{ $supplement->id }})">{{ $supplement->name }}</h5>
                                                    <button type="button" class="btn btn-sm text-muted p-0 ms-2"
                                                            onclick="showProductDetails({{ $supplement->id }})"
                                                            title="Meer informatie">
                                                        <span class="material-symbols-rounded">info</span>
                                                    </button>
                                                </div>

                                                <p class="small text-muted mb-4"
                                                   onclick="showProductDetails({{ $supplement->id }})"
                                                   style="cursor: pointer;">
                                                    {{ \Illuminate\Support\Str::limit(strip_tags($supplement->description), 60) }}
                                                </p>

                                                <div class="mt-auto d-flex justify-content-between align-items-center">
                                                    <span
                                                        class="fw-bold text-success">€ {{ number_format($supplement->calculated_price, 2, ',', '.') }}</span>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-secondary rounded-circle"
                                                                style="width:32px;height:32px"
                                                                onclick="changeQty({{ $supplement->id }}, -1)">-
                                                        </button>
                                                        <span class="fw-bold px-2" id="qty-{{ $supplement->id }}"
                                                              data-price="{{ $supplement->calculated_price }}">0</span>
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-primary rounded-circle"
                                                                style="width:32px;height:32px"
                                                                onclick="changeQty({{ $supplement->id }}, 1)">+
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="d-flex justify-content-between mt-5">
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                                        onclick="goToStep(2)">Vorige
                                </button>
                                <button type="button" class="btn btn-primary rounded-pill px-5"
                                        onclick="checkFormsAndProceed()">Volgende
                                </button>
                            </div>
                        </div>

                        {{-- Step 3.5: Dynamic Forms for Extras --}}
                        <div id="step-content-forms" class="step-content p-4 p-md-5 d-none">
                            <h3 class="h4 fw-bold mb-4 text-primary">Extra informatie</h3>
                            <p class="text-muted mb-4">Voor enkele gekozen opties hebben we nog wat extra gegevens
                                nodig.</p>

                            <div id="dynamic-forms-container">
                                {{-- Forms will be injected here via JS --}}
                            </div>

                            <div class="d-flex justify-content-between mt-5">
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                                        onclick="goToStep(3)">Vorige
                                </button>
                                <button type="button" class="btn btn-primary rounded-pill px-5"
                                        onclick="validateFormsAndProceed()">Volgende
                                </button>
                            </div>
                        </div>

                        {{-- Step 4: Overview --}}
                        <div id="step-content-4" class="step-content p-4 p-md-5 d-none">
                            <h3 class="h4 fw-bold mb-4 text-primary">Overzicht</h3>
                            <div class="bg-light rounded-4 p-4 mb-4 border">
                                <div class="d-flex justify-content-between mb-3 border-bottom pb-3">
                                    <div><h5 class="fw-bold">{{ $accommodatie->name }}</h5>
                                        <p class="text-muted small" id="overview-dates">...</p></div>
                                    <div class="text-end">
                                        <div class="fw-bold fs-5" id="overview-acco-total">€ 0,00</div>
                                        <div class="small text-muted" id="overview-hours"></div>
                                    </div>
                                </div>
                                <div id="overview-supplements-list"></div>
                                <div class="d-flex justify-content-between border-top pt-3 mt-2"><h4 class="fw-bold">
                                        Totaal</h4><h4 class="fw-bold text-primary" id="overview-grand-total">€
                                        0,00</h4></div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                                        onclick="goBackFromOverview()">Vorige
                                </button>
                                <button type="button" class="btn btn-primary rounded-pill px-5" onclick="goToStep(5)">
                                    Naar betalen
                                </button>
                            </div>
                        </div>

                        {{-- Step 5: User Details --}}
                        <div id="step-content-5" class="step-content p-4 p-md-5 d-none">
                            <h3 class="h4 fw-bold mb-4 text-primary">Gegevens & Agenda</h3>

                            <div class="d-flex flex-column gap-3 w-100">
                                <div class="mb-2">
                                    <div class="mb-4">
                                        <label for="comment" class="form-label fw-bold">Opmerkingen & Speciale wensen</label>
                                        <textarea class="form-control rounded-4" id="comment" name="comment" rows="4" placeholder="Heb je nog vragen of specifieke wensen voor ons?"></textarea>
                                    </div>

                                    <div class="bg-light border-0 rounded-4">
                                        <div class="card-body p-4">
                                            <div class="form-check form-switch">
                                                {{-- Added onchange handler to the toggle --}}
                                                <input class="form-check-input" type="checkbox" role="switch" id="public" name="public" value="1" onchange="togglePublicFields()">
                                                <label class="form-check-label fw-bold" for="public">Zichtbaar in de NoordenLicht agenda</label>
                                            </div>
                                            <p class="small text-muted mb-0">Indien ingeschakeld, wordt jouw activiteit getoond op onze openbare website.</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Container for public-only information --}}
                                <div id="public-fields-container" class="d-none animate-fade-in">
                                    <div class="mb-4">
                                        <label for="external-link" class="form-label fw-bold">Directe aanmeld link</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0 rounded-start-4"><i class="material-symbols-rounded">web_traffic</i></span>
                                            <input type="url" class="form-control border-start-0 rounded-end-4" id="external_link" name="external_link" placeholder="https://jouw-website.nl/aanmelden">
                                        </div>
                                        <div class="form-text">Link naar je eigen inschrijfformulier of ticketpagina.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Beschrijving voor de agenda</label>
                                        <div class="editor-parent">
                                            @yield('editor')
                                            <div id="text-input" contenteditable="true" class="text-input p-3" style="min-height: 150px;">{!! old('activity_description') !!}</div>
                                            <div class="bg-light px-3 py-1 border-top d-flex justify-content-between">
                                                <small class="text-muted">Vertel deelnemers wat ze kunnen verwachten.</small>
                                                <small id="characters" class="text-muted"></small>
                                            </div>
                                        </div>
                                        <input id="content" name="activity_description" type="hidden" value="{{ old('activity_description') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-5 pt-4 border-top">
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="goToStep(4)">
                                    <i class="bi bi-arrow-left me-2"></i>Vorige
                                </button>
                                <button type="submit" class="btn btn-success rounded-pill px-5 btn-lg shadow-sm">
                                    Bevestig & Boek Nu
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="popUp" class="popup" style="display: none; z-index: 99999; top: 0; left: 0; position: fixed">
        <div class="popup-body">
            <div class="page">
                <h2 id="popup-title" class="fw-bold mb-0"></h2>

                <div id="popup-img-wrapper" class="mb-3 text-center d-none">
                    <img id="popup-img" src="" class="img-fluid rounded" style="max-height: 25vh;">
                </div>

                <div id="popup-desc" style="text-align: left; max-height: 50vh; overflow-y: scroll"></div>

            </div>
            <div class="button-container">
                <a id="close-popup" onclick="closePopup()" class="btn btn-outline-danger"><span
                        class="material-symbols-rounded">close</span></a>
            </div>
        </div>
    </div>

    <script>
        // Global
        let currentStep = 1;
        const pricePerHour = {{ $calculatedPrice ?? 0 }};
        // Supplement Data (now includes all fields needed for modal and forms)
        const supplements = @json($supplements);
        let selectedSupplements = {};

        let hasFormsStep = false; // Flag to track if the intermediate form step is active

        // Calendar
        let currentMonth = new Date().getMonth() + 1;
        let currentYear = new Date().getFullYear();
        let bookings = [];
        let selectedDate = null;

        // Settings with Defaults
        let minCheckInStr = "{{ $accommodatie->min_check_in ?? '08:00' }}";
        let maxCheckInStr = "{{ $accommodatie->max_check_in ?? '22:00' }}";
        let minDurationMins = {{ $accommodatie->min_duration_minutes ?? 120 }};

        // Timeline Interaction State
        let isMouseDown = false;
        let isTouchDown = false;
        let hasMoved = false;
        let dragStartY = 0;
        let selectionStartMins = 0;
        let selectionEndMins = 0;
        let startHour = 0;
        let endHour = 24;
        const pxPerHour = 60;

        document.addEventListener('DOMContentLoaded', () => {
            renderCalendar();
            fetchAvailability();
            initMouseHandlers();
            initTouchHandlers();
        });

        function togglePublicFields() {
            const isPublic = document.getElementById('public').checked;
            const container = document.getElementById('public-fields-container');

            if (isPublic) {
                container.classList.remove('d-none');
                // Optional: ensure editor is focused or refreshed if needed
            } else {
                container.classList.add('d-none');
                // Optional: Clear public fields if the user unchecks the box
                document.getElementById('external_link').value = '';
                document.getElementById('text-input').innerHTML = '';
                document.getElementById('content').value = '';
            }
        }

        // --- Data Fetching ---
        function fetchAvailability() {
            const loading = document.getElementById('calendar-loading');
            if (loading) loading.style.opacity = '1';

            fetch(`{{ route('accommodatie.availability', $accommodatie->id) }}?month=${currentMonth}&year=${currentYear}`)
                .then(res => res.json())
                .then(data => {
                    bookings = data.events || [];
                    if (data.settings) {
                        if (data.settings.min_check_in) minCheckInStr = data.settings.min_check_in.substring(0, 5);
                        if (data.settings.max_check_in) maxCheckInStr = data.settings.max_check_in.substring(0, 5);
                        if (data.settings.min_duration) minDurationMins = parseInt(data.settings.min_duration);
                    }
                    renderCalendar();
                })
                .catch(e => {
                    console.error('Calendar Fetch Error:', e);
                    renderCalendar();
                })
                .finally(() => {
                    if (loading) loading.style.opacity = '0';
                });
        }

        // --- Helper: Naive Date Parser (Ignores Timezones) ---
        function parseNaiveDate(dateStr) {
            // Parses "YYYY-MM-DD HH:mm:ss" or "YYYY-MM-DDTHH:mm:ss" exactly as local time
            if (!dateStr) return new Date();
            // Remove 'Z' or sub-seconds to ensure clean parsing
            let cleanStr = dateStr.replace('Z', '').split('.')[0];
            const [datePart, timePart] = cleanStr.split(/[ T]/);
            const [y, m, d] = datePart.split('-').map(Number);
            const [h, min, s] = (timePart || '00:00:00').split(':').map(Number);
            // Construct date using local arguments
            return new Date(y, m - 1, d, h, min, s || 0);
        }

        // --- Render Calendar Month ---
        function renderCalendar() {
            const dt = new Date(currentYear, currentMonth - 1, 1);
            document.getElementById('calendar-month-year').innerText = dt.toLocaleString('nl-NL', {
                month: 'long',
                year: 'numeric'
            });

            const firstDayOfWeek = (dt.getDay() + 6) % 7;
            const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
            const grid = document.getElementById('calendar-grid');
            grid.innerHTML = '';

            for (let i = 0; i < firstDayOfWeek; i++) {
                grid.appendChild(createCell('', 'bg-light border-0'));
            }

            const today = new Date();
            today.setHours(0, 0, 0, 0);

            for (let i = 1; i <= daysInMonth; i++) {
                const dateStr = `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                const cellDate = new Date(dateStr);
                cellDate.setHours(0, 0, 0, 0);

                const cell = document.createElement('div');
                cell.className = 'calendar-day p-2 rounded border d-flex flex-column align-items-center justify-content-center';
                cell.style.minHeight = '100px';

                if (cellDate < today) {
                    cell.classList.add('bg-light', 'text-muted');
                    cell.innerHTML = `<span class="fw-bold fs-5 opacity-50">${i}</span>`;
                    cell.style.cursor = 'not-allowed';
                } else {
                    const status = checkDayStatus(dateStr);
                    cell.innerHTML = `<span class="fw-bold fs-5">${i}</span>`;

                    if (status === 'full') {
                        cell.classList.add('bg-danger-subtle', 'text-danger', 'border-danger-subtle');
                        cell.innerHTML += `<div class="mt-2"><span class="badge bg-danger rounded-pill px-2 py-1" style="font-size:0.65rem"><span class="no-mobile">VOL</span></span></div>`;
                        cell.style.cursor = 'not-allowed';
                    } else {
                        cell.classList.add('bg-white', 'cursor-pointer', 'hover-shadow', 'border-secondary-subtle');
                        if (status === 'partial') {
                            cell.innerHTML += `<div class="mt-2"><span class="badge bg-warning text-dark rounded-pill px-2 py-1" style="font-size:0.65rem"><span class="no-mobile">BEPERKT</span></span></div>`;
                            cell.onclick = () => openDayView(dateStr, i);
                        } else {
                            cell.innerHTML += `<div class="mt-2"><span class="badge bg-success rounded-pill px-2 py-1" style="font-size:0.65rem"><span class="no-mobile">VRIJ</span></span></div>`;
                            cell.onclick = () => openDayView(dateStr, i);
                        }
                    }
                }
                grid.appendChild(cell);
            }
        }

        function createCell(content, classes) {
            const div = document.createElement('div');
            div.className = classes + ' p-2 rounded';
            div.innerHTML = content;
            return div;
        }

        function checkDayStatus(dateStr) {
            const startH = parseInt(minCheckInStr.split(':')[0]) || 0;
            const endH = parseInt(maxCheckInStr.split(':')[0]) || 24;
            const openMins = startH * 60;
            const closeMins = (endH <= startH ? 24 : endH) * 60;

            if (!Array.isArray(bookings)) return 'available';

            // Define the start and end of the current day being checked
            const dayStart = new Date(dateStr + 'T00:00:00');
            const dayEnd = new Date(dateStr + 'T23:59:59');

            // Find all bookings that overlap with this day
            const dayBookings = bookings.filter(b => {
                let s = parseNaiveDate(b.start);
                let e = parseNaiveDate(b.end);

                // Check if the booking overlaps with the current day
                // (Booking Start <= Day End) AND (Booking End >= Day Start)
                return s <= dayEnd && e >= dayStart;
            }).map(b => {
                let s = parseNaiveDate(b.start);
                let e = parseNaiveDate(b.end);

                // If booking starts before today, clamp start to opening time (or 00:00)
                // If booking ends after today, clamp end to closing time (or 23:59)

                // For simplified "full day" logic:
                // If a booking fully encompasses the day (starts before today, ends after today),
                // it consumes the whole available slot.
                if (s < dayStart && e > dayEnd) {
                    return {start: openMins, end: closeMins};
                }

                // Normal partial logic for starts/ends on this day
                if (s < dayStart) s = dayStart; // clamp start
                if (e > dayEnd) e = dayEnd;     // clamp end

                const startM = s.getHours() * 60 + s.getMinutes();
                const endM = e.getHours() * 60 + e.getMinutes();

                // Clamp within operating hours for calculation
                return {
                    start: Math.max(startM, openMins),
                    end: Math.min(endM, closeMins)
                };
            }).sort((a, b) => a.start - b.start);

            let maxGap = 0;
            let cursor = openMins;

            dayBookings.forEach(b => {
                // If there is a gap between cursor and booking start
                if (b.start > cursor) maxGap = Math.max(maxGap, b.start - cursor);
                // Move cursor to end of current booking
                cursor = Math.max(cursor, b.end);
            });

            // Check gap after last booking until close
            if (closeMins > cursor) maxGap = Math.max(maxGap, closeMins - cursor);

            // If the largest available gap is smaller than minimum duration, the day is effectively full
            if (maxGap < minDurationMins) return 'full';

            // If there are any bookings at all, it's partial
            if (dayBookings.length > 0) return 'partial';

            return 'available';
        }

        // --- Timeline Day View ---

        function openDayView(dateStr, dayNum) {
            selectedDate = dateStr;
            const dt = new Date(dateStr);
            document.getElementById('selected-date-display').innerText = dt.toLocaleDateString('nl-NL', {
                weekday: 'long',
                day: 'numeric',
                month: 'long'
            });
            goToStep(2);
            renderTimeline(dateStr);
        }

        function renderTimeline(dateStr) {
            const container = document.getElementById('timeline-grid');
            const labels = document.querySelector('.timeline-labels');
            const eventsLayer = document.getElementById('timeline-events-layer');
            const interactionLayer = document.getElementById('timeline-interaction-layer');
            const linesLayer = document.getElementById('timeline-lines-layer');

            labels.innerHTML = '';
            eventsLayer.innerHTML = '';
            linesLayer.innerHTML = '';

            startHour = parseInt(minCheckInStr.split(':')[0]) || 0;
            let endH = parseInt(maxCheckInStr.split(':')[0]) || 24;
            if (endH <= startHour) endH = 24;
            endHour = endH;

            const totalHours = endHour - startHour;
            const containerHeight = totalHours * pxPerHour;

            container.style.height = containerHeight + 'px';
            labels.style.height = containerHeight + 'px';
            eventsLayer.style.height = containerHeight + 'px';
            interactionLayer.style.height = containerHeight + 'px';
            linesLayer.style.height = containerHeight + 'px';

            for (let h = 0; h <= totalHours; h++) {
                const top = h * pxPerHour;
                const hourLabel = startHour + h;
                const labelStr = (hourLabel < 10 ? '0' : '') + hourLabel + ':00';

                const lDiv = document.createElement('div');
                lDiv.className = 'position-absolute w-100 text-center small text-muted border-bottom';
                lDiv.style.top = top + 'px';
                lDiv.style.height = '0px';
                lDiv.style.lineHeight = '0';
                lDiv.innerHTML = `<span style="position:relative; top:-10px; background:white; padding:0 2px;">${labelStr}</span>`;
                labels.appendChild(lDiv);

                if (h < totalHours) {
                    const line = document.createElement('div');
                    line.className = 'position-absolute w-100 border-bottom border-light';
                    line.style.top = top + 'px';
                    linesLayer.appendChild(line);
                }
            }

            const dayStartMins = startHour * 60;

            if (Array.isArray(bookings)) {
                const selectedDayStart = new Date(dateStr + 'T00:00:00');
                const selectedDayEnd = new Date(dateStr + 'T23:59:59');

                bookings.forEach(b => {
                    let s = parseNaiveDate(b.start);
                    let e = parseNaiveDate(b.end);

                    if (s <= selectedDayEnd && e >= selectedDayStart) {
                        const startM = s.getHours() * 60 + s.getMinutes();
                        const endM = e.getHours() * 60 + e.getMinutes();
                        let displayStartM = startM;
                        let displayEndM = endM;
                        if (s < selectedDayStart) displayStartM = dayStartMins;
                        if (e > selectedDayEnd) displayEndM = endHour * 60;
                        const visibleStart = Math.max(displayStartM, dayStartMins);
                        const visibleEnd = Math.min(displayEndM, endHour * 60);

                        if (visibleEnd > visibleStart) {
                            const top = (visibleStart - dayStartMins) * (pxPerHour / 60);
                            const height = (visibleEnd - visibleStart) * (pxPerHour / 60);
                            const evDiv = document.createElement('div');
                            evDiv.className = 'position-absolute bg-danger bg-opacity-75 text-white small rounded shadow-sm d-flex align-items-center justify-content-center';
                            evDiv.style.top = top + 'px';
                            evDiv.style.height = height + 'px';
                            evDiv.style.left = '60px';
                            evDiv.style.right = '10px';
                            evDiv.style.zIndex = '15';
                            evDiv.innerText = 'Niet Beschikbaar';
                            eventsLayer.appendChild(evDiv);
                        }
                    }
                });
            }
        }

        // --- Interaction Logic: MOUSE HANDLERS ---
        function initMouseHandlers() {
            const layer = document.getElementById('timeline-interaction-layer');
            if (!layer) return;

            layer.addEventListener('mousedown', (e) => {
                e.preventDefault();
                isMouseDown = true;
                hasMoved = false;

                const rect = layer.getBoundingClientRect();
                dragStartY = e.clientY - rect.top;

                updateVisualSelection(dragStartY, dragStartY);
                document.getElementById('timeline-selection').style.display = 'block';
            });

            window.addEventListener('mousemove', (e) => {
                if (!isMouseDown) return;

                const layer = document.getElementById('timeline-interaction-layer');
                const rect = layer.getBoundingClientRect();
                const currentY = e.clientY - rect.top;

                if (Math.abs(currentY - dragStartY) > 3) {
                    hasMoved = true;
                }

                if (hasMoved) {
                    updateVisualSelection(dragStartY, currentY);
                }
            });

            window.addEventListener('mouseup', (e) => {
                if (!isMouseDown) return;
                isMouseDown = false;

                if (hasMoved) {
                    validateSelection();
                } else {
                    createDefaultSelection(dragStartY);
                }
            });
        }

        // --- Interaction Logic: TOUCH HANDLERS ---
        function initTouchHandlers() {
            const layer = document.getElementById('timeline-interaction-layer');
            if (!layer) return;

            layer.addEventListener('touchstart', (e) => {
                if (e.touches.length > 0) {
                    e.preventDefault();
                    isTouchDown = true;
                    hasMoved = false;

                    const rect = layer.getBoundingClientRect();
                    dragStartY = e.touches[0].clientY - rect.top;

                    updateVisualSelection(dragStartY, dragStartY);
                    document.getElementById('timeline-selection').style.display = 'block';
                }
            }, {passive: false});

            layer.addEventListener('touchmove', (e) => {
                if (isTouchDown && e.touches.length > 0) {
                    e.preventDefault();
                    hasMoved = true;

                    const rect = layer.getBoundingClientRect();
                    const currentY = e.touches[0].clientY - rect.top;

                    updateVisualSelection(dragStartY, currentY);
                }
            }, {passive: false});

            layer.addEventListener('touchend', (e) => {
                if (!isTouchDown) return;
                isTouchDown = false;

                if (hasMoved) {
                    validateSelection();
                } else {
                    createDefaultSelection(dragStartY);
                }
            });
        }

        function createDefaultSelection(startY) {
            const minHeight = minDurationMins * (pxPerHour / 60);
            updateVisualSelection(startY, startY + minHeight);
            validateSelection();
        }

        function updateVisualSelection(y1, y2) {
            const top = Math.min(y1, y2);
            const h = Math.abs(y2 - y1);
            const selection = document.getElementById('timeline-selection');
            selection.style.top = top + 'px';
            selection.style.height = h + 'px';
            selection.style.display = 'block';

            const dayStartMins = startHour * 60;
            const startMins = dayStartMins + (top / (pxPerHour / 60));
            const endMins = startMins + (h / (pxPerHour / 60));

            const rStart = Math.round(startMins / 15) * 15;
            const rEnd = Math.round(endMins / 15) * 15;

            selection.innerHTML = `<span class="small bg-white px-2 rounded fw-bold text-primary shadow-sm">${minsToTime(rStart)} - ${minsToTime(rEnd)}</span>`;
            selection.className = 'timeline-selection position-absolute bg-primary bg-opacity-25 border border-primary text-center text-primary fw-bold d-flex align-items-center justify-content-center';
        }

        function manualTimeChange() {
            const sVal = document.getElementById('time-start').value;
            const eVal = document.getElementById('time-end').value;
            if (!sVal || !eVal) return;
            const [sH, sM] = sVal.split(':').map(Number);
            const [eH, eM] = eVal.split(':').map(Number);
            selectionStartMins = sH * 60 + sM;
            selectionEndMins = eH * 60 + eM;

            // Pass false for skipInputUpdate (so inputs auto-correct if invalid)
            // Pass true for fromInput (so we calculate visual FROM time, not time FROM visual)
            validateSelection(false, true);
        }

        function validateSelection(skipInputUpdate = false, fromInput = false) {
            const selection = document.getElementById('timeline-selection');
            const dayStartMins = startHour * 60;

            // Only recalculate minutes from DOM if NOT coming from manual input
            if (!fromInput) {
                const top = parseFloat(selection.style.top);
                const height = parseFloat(selection.style.height);

                const rawStart = dayStartMins + (top / (pxPerHour / 60));
                const rawEnd = dayStartMins + ((top + height) / (pxPerHour / 60));

                selectionStartMins = Math.round(rawStart / 15) * 15;
                selectionEndMins = Math.round(rawEnd / 15) * 15;
            }

            if (selectionEndMins - selectionStartMins < minDurationMins) {
                selectionEndMins = selectionStartMins + minDurationMins;
            }

            const dayEndMins = endHour * 60;
            if (selectionStartMins < dayStartMins) selectionStartMins = dayStartMins;
            if (selectionEndMins > dayEndMins) selectionEndMins = dayEndMins;

            let hasOverlap = false;
            if (selectedDate && Array.isArray(bookings)) {
                const selectedDayStart = new Date(selectedDate + 'T00:00:00');
                const selectedDayEnd = new Date(selectedDate + 'T23:59:59');
                bookings.forEach(b => {
                    let s = parseNaiveDate(b.start);
                    let e = parseNaiveDate(b.end);

                    if (s <= selectedDayEnd && e >= selectedDayStart) {
                        let checkStart = s.getHours() * 60 + s.getMinutes();
                        let checkEnd = e.getHours() * 60 + e.getMinutes();
                        if (s < selectedDayStart) checkStart = dayStartMins;
                        if (e > selectedDayEnd) checkEnd = dayEndMins;
                        if (selectionStartMins < checkEnd && selectionEndMins > checkStart) {
                            hasOverlap = true;
                        }
                    }
                });
            }

            const finalTop = (selectionStartMins - dayStartMins) * (pxPerHour / 60);
            const finalHeight = (selectionEndMins - selectionStartMins) * (pxPerHour / 60);

            selection.style.top = finalTop + 'px';
            selection.style.height = finalHeight + 'px';
            selection.style.display = 'flex';

            if (hasOverlap) {
                selection.className = 'timeline-selection position-absolute bg-opacity-50 bg-danger text-white border border-danger text-center fw-bold d-flex align-items-center justify-content-center';
                selection.style.zIndex = '30';
                selection.innerHTML = `<span class="small px-2">Niet Beschikbaar</span>`;
                document.getElementById('btn-confirm-date').disabled = true;
                return;
            }

            selection.className = 'timeline-selection position-absolute bg-primary bg-opacity-25 border border-primary text-center text-primary fw-bold d-flex align-items-center justify-content-center';
            selection.style.zIndex = '20';

            if (!skipInputUpdate) {
                document.getElementById('time-start').value = minsToTime(selectionStartMins);
                document.getElementById('time-end').value = minsToTime(selectionEndMins);
            }
            selection.innerText = `${minsToTime(selectionStartMins)} - ${minsToTime(selectionEndMins)}`;
            document.getElementById('btn-confirm-date').disabled = false;
        }

        function minsToTime(mins) {
            const h = Math.floor(mins / 60);
            const m = Math.floor(mins % 60);
            return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
        }

        function prevMonth() {
            if (currentMonth === 1) {
                currentMonth = 12;
                currentYear--;
            } else {
                currentMonth--;
            }
            renderCalendar();
            fetchAvailability();
        }

        function nextMonth() {
            if (currentMonth === 12) {
                currentMonth = 1;
                currentYear++;
            } else {
                currentMonth++;
            }
            renderCalendar();
            fetchAvailability();
        }

        function confirmDate() {
            document.getElementById('input_start_date').value = selectedDate;
            document.getElementById('input_start_time').value = document.getElementById('time-start').value;
            document.getElementById('input_end_date').value = selectedDate;
            document.getElementById('input_end_time').value = document.getElementById('time-end').value;
            goToStep(3);
        }

        // --- Main Navigation ---
        function goToStep(step) {

            // Handle hiding/showing the "Details" indicator
            if (step === 'forms') {
                // Show form container
                document.querySelectorAll('.step-content').forEach(el => el.classList.add('d-none'));
                document.getElementById('step-content-forms').classList.remove('d-none');

                // Highlight indicators up to 3 (Extra's) but keep Details hidden from top bar if we want standard numbering
                // Or better, let's make the indicators static and just highlight appropriately.
                // The issue is step 3.5 doesn't map perfectly to 1,2,3,4,5 circles if we don't have a 6th circle.
                // Let's activate "Details" indicator if we are in form step.
                const detailsInd = document.querySelector('.step-indicator-details');
                if (detailsInd) detailsInd.classList.remove('d-none');

                // Highlight up to 4 (Details)
                updateIndicators(4);

                hasFormsStep = true;
                currentStep = 'forms';
            } else {
                if (step === 4) {
                    // Overview Step.
                    // Ensure details indicator is visible if we passed through forms, or hide it if we didn't?
                    // A cleaner UI: If forms exist, show 'Details' circle. If not, hide it.
                    const detailsInd = document.querySelector('.step-indicator-details');
                    if (hasFormsStep && detailsInd) detailsInd.classList.remove('d-none');
                    else if (!hasFormsStep && detailsInd) detailsInd.classList.add('d-none');

                    calculateTotal();
                    updateIndicators(hasFormsStep ? 5 : 4);
                } else if (step === 5) {
                    updateIndicators(hasFormsStep ? 6 : 5);
                } else {
                    updateIndicators(step);
                }

                currentStep = step;
                document.querySelectorAll('.step-content').forEach(el => el.classList.add('d-none'));
                document.getElementById(`step-content-${step}`).classList.remove('d-none');
            }

            window.scrollTo(0, 0);
        }

        function updateIndicators(activeIndex) {
            const circles = document.querySelectorAll('.step-circle');
            const indicators = document.querySelectorAll('[id^="step-indicator-container-"]');

            // Total circles depends on if Details is visible
            // But we are selecting by index 1-based.
            // Circles are: 1, 2, 3, 4(Details), 5(Overzicht), 6(Betalen) IF details is shown
            // If details hidden: 1, 2, 3, 5(Overzicht), 6(Betalen) -> but IDs remain.

            const detailsVisible = !document.querySelector('.step-indicator-details').classList.contains('d-none');

            indicators.forEach((ind, i) => {
                const circle = ind.querySelector('.step-circle');
                const stepNum = i + 1; // 1 to 6

                // Mapping logic:
                // If details is hidden (normal flow):
                // Active 1 -> Circle 1
                // Active 2 -> Circle 2
                // Active 3 -> Circle 3
                // Active 4 (Overview) -> Circle 5
                // Active 5 (Payment) -> Circle 6

                let isActive = false;
                let isPast = false;

                if (detailsVisible) {
                    if (stepNum < activeIndex) isPast = true;
                    if (stepNum === activeIndex) isActive = true;
                } else {
                    // Skip step 4 (Details)
                    // If we are at step 4 (Overview), visually it corresponds to ID 5
                    // This is tricky with static HTML IDs.
                    // Simplification: Just light them up based on ID order.

                    // If step < 4 (1, 2, 3), normal.
                    // If target is 4 (Overview), we want Circle 5 active.

                    // Let's just follow the activeIndex passed.
                    // Step 1 -> 1
                    // Step 2 -> 2
                    // Step 3 -> 3
                    // Step 4 (Overview, no forms) -> We want 'Overzicht' (ID 5) active.

                    // Let's remap the passed activeIndex for the no-forms case
                    let targetId = activeIndex;
                    if (!detailsVisible && activeIndex >= 4) targetId = activeIndex + 1;

                    if (stepNum < targetId) isPast = true;
                    if (stepNum === targetId) isActive = true;
                }

                if (isPast) {
                    circle.className = 'step-circle bg-secondary text-white d-flex justify-content-center align-items-center rounded-circle fw-bold shadow-sm';
                    circle.innerText = '✓';
                } else if (isActive) {
                    circle.className = 'step-circle bg-primary text-white d-flex justify-content-center align-items-center rounded-circle fw-bold shadow-lg';
                    circle.innerText = stepNum > 4 && !detailsVisible ? stepNum - 1 : stepNum; // Fix number display
                    // Actually better to keep original numbers or labels
                    circle.innerText = circle.id.replace('step-circle-', '');
                } else {
                    circle.className = 'step-circle bg-white border text-secondary d-flex justify-content-center align-items-center rounded-circle fw-bold';
                    circle.innerText = circle.id.replace('step-circle-', '');
                }
            });

            // Update progress line
            const totalSteps = detailsVisible ? 6 : 5;
            const currentVisStep = detailsVisible ? activeIndex : (activeIndex >= 4 ? activeIndex : activeIndex);
            // Rough progress
            let progress = 0;
            if (detailsVisible) progress = ((activeIndex - 1) / 5) * 100;
            else progress = ((activeIndex - 1) / 4) * 100;

            document.getElementById('progress-line').style.width = Math.min(progress, 100) + '%';
        }

        // --- Supplements Logic ---
        function changeQty(id, d) {
            const el = document.getElementById('qty-' + id);
            let v = parseInt(el.innerText) + d;
            if (v < 0) v = 0;
            el.innerText = v;
            selectedSupplements[id] = v;
        }

        // --- Custom Popup Logic (Replaces Bootstrap Modal) ---
        function showProductDetails(id) {
            const product = supplements.find(p => p.id === id);
            if (!product) return;

            // Populate data
            document.getElementById('popup-title').innerText = product.name;
            document.getElementById('popup-desc').innerHTML = product.description;

            const imgEl = document.getElementById('popup-img');
            const imgContainer = document.getElementById('popup-img-wrapper');

            if (product.image) {
                imgEl.src = '/files/products/images/' + product.image;
                if (imgContainer) imgContainer.classList.remove('d-none');
            } else {
                if (imgContainer) imgContainer.classList.add('d-none');
            }

            // Show Popup by toggling display directly
            const popup = document.getElementById('popUp');
            if (popup) {
                popup.style.display = 'flex'; // Flex is used to center content
            }
        }

        function closePopup() {
            const popup = document.getElementById('popUp');
            if (popup) popup.style.display = 'none';
        }

        // --- Dynamic Forms Logic ---
        function checkFormsAndProceed() {
            // Check if any selected supplements have form_elements
            let formsNeeded = false;
            const container = document.getElementById('dynamic-forms-container');
            container.innerHTML = ''; // Clear previous

            for (const [id, qty] of Object.entries(selectedSupplements)) {
                if (qty > 0) {
                    const product = supplements.find(p => p.id == id);
                    if (product && product.form_elements && product.form_elements.length > 0) {
                        formsNeeded = true;
                        renderProductForm(product, qty, container);
                    }
                }
            }

            if (formsNeeded) {
                goToStep('forms');
            } else {
                hasFormsStep = false;
                goToStep(4);
            }
        }

        function renderProductForm(product, qty, container) {
            const wrapper = document.createElement('div');
            wrapper.className = 'bg-white border rounded-4 p-4 mb-4 shadow-sm';

            const title = document.createElement('h5');
            title.className = 'fw-bold mb-3 border-bottom pb-2';
            title.innerText = `${product.name} (Opties)`;
            wrapper.appendChild(title);

            // Ask once per product type
            product.form_elements.forEach(el => {
                const group = document.createElement('div');
                group.className = 'mb-3';

                const label = document.createElement('label');
                label.className = 'form-label fw-medium';
                label.innerHTML = el.label + (el.is_required ? ' <span class="text-danger">*</span>' : '');
                group.appendChild(label);

                // IMPORTANT: Use Element ID as key to match backend expectations
                const inputName = `supplement_forms[${product.id}][${el.id}]`;

                let input;

                switch (el.type) {
                    case 'text':
                    case 'email':
                    case 'number':
                    case 'date':
                        input = document.createElement('input');
                        input.type = el.type;
                        input.className = 'form-control';
                        input.name = inputName;
                        if (el.is_required) input.required = true;
                        break;

                    case 'select':
                        input = document.createElement('select');
                        input.className = 'form-select';
                        input.name = inputName;
                        if (el.is_required) input.required = true;
                        const defOpt = document.createElement('option');
                        defOpt.value = '';
                        defOpt.innerText = 'Selecteer...';
                        input.appendChild(defOpt);
                        if (el.option_value) {
                            el.option_value.split(',').forEach(opt => {
                                const o = document.createElement('option');
                                o.value = opt.trim();
                                o.innerText = opt.trim();
                                input.appendChild(o);
                            });
                        }
                        break;

                    case 'radio':
                        input = document.createElement('div');
                        if (el.option_value) {
                            el.option_value.split(',').forEach((opt, idx) => {
                                const div = document.createElement('div');
                                div.className = 'form-check';
                                const r = document.createElement('input');
                                r.type = 'radio';
                                r.className = 'form-check-input';
                                r.name = inputName;
                                r.value = opt.trim();
                                r.id = `radio_${product.id}_${el.id}_${idx}`;
                                if (el.is_required) r.required = true;

                                const l = document.createElement('label');
                                l.className = 'form-check-label';
                                l.htmlFor = r.id;
                                l.innerText = opt.trim();

                                div.appendChild(r);
                                div.appendChild(l);
                                input.appendChild(div);
                            });
                        }
                        break;

                    case 'checkbox':
                        input = document.createElement('div');
                        if (el.option_value) {
                            el.option_value.split(',').forEach((opt, idx) => {
                                const div = document.createElement('div');
                                div.className = 'form-check';
                                const c = document.createElement('input');
                                c.type = 'checkbox';
                                c.className = 'form-check-input';
                                c.name = inputName + '[]'; // Array for checkboxes
                                c.value = opt.trim();
                                c.id = `check_${product.id}_${el.id}_${idx}`;

                                const l = document.createElement('label');
                                l.className = 'form-check-label';
                                l.htmlFor = c.id;
                                l.innerText = opt.trim();

                                div.appendChild(c);
                                div.appendChild(l);
                                input.appendChild(div);
                            });
                        }
                        break;
                }

                if (input) group.appendChild(input);
                wrapper.appendChild(group);
            });

            container.appendChild(wrapper);
        }

        function validateFormsAndProceed() {
            const container = document.getElementById('dynamic-forms-container');
            // Simple validation check
            const inputs = container.querySelectorAll('input, select');
            let valid = true;

            inputs.forEach(i => {
                if (i.checkValidity && !i.checkValidity()) {
                    i.reportValidity();
                    valid = false;
                }
            });

            if (valid) {
                goToStep(4);
            }
        }

        function goBackFromOverview() {
            if (hasFormsStep) {
                goToStep('forms');
            } else {
                goToStep(3);
            }
        }

        // --- Final Calculation ---
        function calculateTotal() {
            const start = new Date(selectedDate + 'T' + document.getElementById('input_start_time').value);
            const end = new Date(selectedDate + 'T' + document.getElementById('input_end_time').value);

            const hours = (end - start) / 36e5;
            // Round up to the nearest whole hour for price calculation
            const billableHours = Math.ceil(hours);
            const total = billableHours * pricePerHour;

            let suppTotal = 0;
            let suppList = '';
            for (const [id, qty] of Object.entries(selectedSupplements)) {
                if (qty > 0) {
                    const el = document.getElementById('qty-' + id);
                    const p = parseFloat(el.dataset.price);
                    suppTotal += p * qty;
                    // Get name from supplements array to be safe
                    const prod = supplements.find(s => s.id == id);
                    const name = prod ? prod.name : 'Extra';
                    suppList += `<div class="d-flex justify-content-between text-muted small"><span>${name} x${qty}</span><span>€ ${(p * qty).toLocaleString('nl-NL', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></div>`;
                }
            }

            // Calculate actual hours and minutes for display
            const actualHours = Math.floor(hours);
            const actualMinutes = Math.round((hours - actualHours) * 60);

            let timeString = "";
            if (actualMinutes > 0) {
                timeString = `${actualHours} uur en ${actualMinutes} min = ${billableHours} uur`;
            } else {
                timeString = `${billableHours} uur`;
            }

            document.getElementById('overview-dates').innerText = `${start.toLocaleDateString()} ${start.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            })} - ${end.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'})}`;
            document.getElementById('overview-hours').innerText = timeString;
            document.getElementById('overview-acco-total').innerText = '€ ' + total.toLocaleString('nl-NL', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('overview-supplements-list').innerHTML = suppList;
            document.getElementById('overview-grand-total').innerText = '€ ' + (total + suppTotal).toLocaleString('nl-NL', {minimumFractionDigits: 2, maximumFractionDigits: 2});

            const arr = [];
            for (const [id, qty] of Object.entries(selectedSupplements)) if (qty > 0) arr.push({id, qty});
            document.getElementById('supplements_data_input').value = JSON.stringify(arr);
        }
    </script>

    <style>
        .grid-cols-7 {
            grid-template-columns: repeat(7, 1fr);
        }

        .hover-shadow:hover {
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
            transform: translateY(-2px);
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .step-circle {
            transition: all 0.3s;
        }

        .transition-width {
            transition: width 0.3s;
        }

        .timeline-grid::-webkit-scrollbar {
            width: 6px;
        }

        .timeline-grid::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 3px;
        }

        @media (max-width: 576px) {
            .no-mobile {
                display: none;
            }
        }

        .group-hover-trigger:hover .tile-image-wrapper img {
            transform: scale(1.05);
            transition: transform 0.3s ease;
        }
    </style>
@endsection
