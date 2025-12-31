@extends('layouts.app')

@section('content')

    <div class="rounded-bottom-5 bg-light mt-5 pb-5"
         style="position: relative; margin-top: 0 !important; padding-top: 50px; padding-bottom: 50px; z-index: 10; background-image: url('{{ asset('img/logo/doodles/Blad StretchA_white.webp') }}'); background-repeat: repeat; background-size: cover;">

        <div class="container">
            <h1 class="fw-bold mb-4 text-center">Boek je verblijf: {{ $accommodatie->name }}</h1>

            <div class="row justify-content-center mb-5">
                <div class="col-md-10 col-lg-8">
                    <div class="position-relative d-flex justify-content-between align-items-center">
                        <div class="position-absolute w-100 start-0 translate-middle-y bg-secondary-subtle" style="height: 4px; top: 30%; z-index: 0;"></div>
                        <div class="position-absolute start-0 translate-middle-y bg-primary transition-width" style="height: 4px; top: 30%; z-index: 0; width: 0%;" id="progress-line"></div>

                        @foreach(['Datum', 'Tijd', 'Extra\'s', 'Overzicht', 'Betalen'] as $index => $label)
                            <div class="d-flex flex-column align-items-center position-relative z-1">
                                <div class="step-circle bg-{{ $index == 0 ? 'primary' : 'white' }} text-{{ $index == 0 ? 'white' : 'secondary' }} d-flex justify-content-center align-items-center rounded-circle fw-bold shadow-sm"
                                     style="width: 40px; height: 40px;" id="step-circle-{{ $index+1 }}">{{ $index+1 }}</div>
                                <span class="small mt-2 bg-light px-2 {{ $index == 0 ? 'fw-bold' : 'text-muted' }}">{{ $label }}</span>
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

                        <div id="step-content-1" class="step-content p-4 p-md-5">
                            <h3 class="h4 fw-bold mb-4 text-primary">Kies een datum</h3>

                            <div class="calendar-wrapper position-relative">
                                <div id="calendar-loading" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex justify-content-center align-items-center" style="z-index: 50; display: none; pointer-events: none">
                                    <div class="spinner-border text-primary" role="status"></div>
                                </div>

                                <div id="calendar-view">
                                    <div class="d-flex justify-content-between align-items-center mb-4 bg-light p-3 rounded">
                                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="prevMonth()">Vorige</button>
                                        <h4 class="mb-0 fw-bold text-uppercase" id="calendar-month-year"></h4>
                                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="nextMonth()">Volgende</button>
                                    </div>
                                    <div class="d-grid grid-cols-7 gap-2 mb-2 text-center fw-bold text-muted small">
                                        <div>MA</div><div>DI</div><div>WO</div><div>DO</div><div>VR</div><div>ZA</div><div>ZO</div>
                                    </div>
                                    <div id="calendar-grid" class="d-grid grid-cols-7 gap-2"></div>
                                </div>
                            </div>
                        </div>

                        <div id="step-content-2" class="step-content p-4 p-md-5 d-none">
                            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                                <h4 class="fw-bold mb-0 text-primary" id="selected-date-display"></h4>
                            </div>

                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i> Sleep om te selecteren.</p>
                                <span class="badge bg-light text-secondary border d-md-none">Scroll rechts &rarr;</span>
                            </div>

                            <div class="timeline-wrapper bg-light border rounded position-relative" style="height: 400px; overflow: hidden; display: flex; flex-direction: column;">
                                <div class="timeline-scroll-area flex-grow-1 position-relative" id="timeline-container" style="overflow-y: auto; user-select: none; background: white; -webkit-overflow-scrolling: touch;">

                                    <div class="timeline-labels position-absolute top-0 start-0 border-end bg-white" style="width: 60px; z-index: 10;"></div>

                                    <div class="timeline-grid position-absolute top-0 start-0 w-100 ps-5" id="timeline-grid" style="padding-left: 60px !important;">

                                        <div id="timeline-lines-layer" class="position-absolute top-0 start-0 w-100 h-100"></div>

                                        <div id="timeline-events-layer" class="position-absolute top-0 start-0 w-100 pointer-events-none"></div>

                                        <div id="timeline-selection" class="timeline-selection position-absolute bg-primary bg-opacity-25 border border-primary text-center text-primary fw-bold d-flex align-items-center justify-content-center" style="display: none !important; left: 60px; right: 0; pointer-events: none; z-index: 20;">
                                            <span class="small bg-white px-2 rounded shadow-sm">Selectie</span>
                                        </div>

                                        <div id="timeline-interaction-layer" class="position-absolute top-0 start-0" style="left: 60px; width: calc(85% - 60px); cursor: crosshair; z-index: 30; touch-action: none;"></div>

                                        <div class="position-absolute top-0 end-0 h-100 bg-secondary bg-opacity-10 border-start d-flex justify-content-center d-md-none" style="width: 15%; z-index: 25;">
                                            <div class="mt-4 text-muted opacity-50 small" style="writing-mode: vertical-rl; text-orientation: mixed;">SCROLL</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4 mt-3">
                                <div class="col-6">
                                    <label class="form-label fw-bold">Start</label>
                                    <input type="time" class="form-control" id="time-start" onchange="manualTimeChange()">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold">Eind</label>
                                    <input type="time" class="form-control" id="time-end" onchange="manualTimeChange()">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-5">
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="goToStep(1)">Vorige</button>
                                <button type="button" class="btn btn-primary rounded-pill px-5" id="btn-confirm-date" disabled onclick="confirmDate()">
                                    Bevestig Periode
                                </button>
                            </div>
                        </div>

                        <div id="step-content-3" class="step-content p-4 p-md-5 d-none">
                            <h3 class="h4 fw-bold mb-4 text-primary">Voeg extra's toe</h3>
                            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                                @foreach($supplements as $supplement)
                                    <div class="col">
                                        <div class="shop-tile h-100 d-flex flex-column bg-white overflow-hidden border rounded-4">
                                            @if($supplement->image)
                                                <div class="tile-image-wrapper" style="height: 180px;"><img src="{{ asset('/files/products/images/'.$supplement->image) }}" class="w-100 h-100 object-fit-cover"></div>
                                            @endif
                                            <div class="p-4 d-flex flex-column flex-grow-1">
                                                <h5 class="fw-bold">{{ $supplement->name }}</h5>
                                                <p class="small text-muted mb-4">{{ \Illuminate\Support\Str::limit(strip_tags($supplement->description), 60) }}</p>
                                                <div class="mt-auto d-flex justify-content-between align-items-center">
                                                    <span class="fw-bold text-success">€ {{ number_format($supplement->calculated_price, 2, ',', '.') }}</span>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle" style="width:32px;height:32px" onclick="changeQty({{ $supplement->id }}, -1)">-</button>
                                                        <span class="fw-bold px-2" id="qty-{{ $supplement->id }}" data-price="{{ $supplement->calculated_price }}">0</span>
                                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-circle" style="width:32px;height:32px" onclick="changeQty({{ $supplement->id }}, 1)">+</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="d-flex justify-content-between mt-5">
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="goToStep(2)">Vorige</button>
                                <button type="button" class="btn btn-primary rounded-pill px-5" onclick="goToStep(4)">Volgende</button>
                            </div>
                        </div>

                        <div id="step-content-4" class="step-content p-4 p-md-5 d-none">
                            <h3 class="h4 fw-bold mb-4 text-primary">Overzicht</h3>
                            <div class="bg-light rounded-4 p-4 mb-4 border">
                                <div class="d-flex justify-content-between mb-3 border-bottom pb-3">
                                    <div><h5 class="fw-bold">{{ $accommodatie->name }}</h5><p class="text-muted small" id="overview-dates">...</p></div>
                                    <div class="text-end"><div class="fw-bold fs-5" id="overview-acco-total">€ 0,00</div><div class="small text-muted" id="overview-hours"></div></div>
                                </div>
                                <div id="overview-supplements-list"></div>
                                <div class="d-flex justify-content-between border-top pt-3 mt-2"><h4 class="fw-bold">Totaal</h4><h4 class="fw-bold text-primary" id="overview-grand-total">€ 0,00</h4></div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="goToStep(3)">Vorige</button>
                                <button type="button" class="btn btn-primary rounded-pill px-5" onclick="goToStep(5)">Naar betalen</button>
                            </div>
                        </div>

                        <div id="step-content-5" class="step-content p-4 p-md-5 d-none">
                            <h3 class="h4 fw-bold mb-4 text-primary">Gegevens</h3>
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label">Voornaam</label><input type="text" class="form-control" name="first_name" value="{{ Auth::user()->name ?? '' }}" required></div>
                                <div class="col-md-6"><label class="form-label">Achternaam</label><input type="text" class="form-control" name="last_name" value="{{ Auth::user()->last_name ?? '' }}" required></div>
                                <div class="col-12"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="{{ Auth::user()->email ?? '' }}" required></div>
                                <div class="col-12"><label class="form-label">Adres</label><input type="text" class="form-control" name="address" required></div>
                                <div class="col-4"><label class="form-label">Postcode</label><input type="text" class="form-control" name="zipcode" required></div>
                                <div class="col-8"><label class="form-label">Woonplaats</label><input type="text" class="form-control" name="city" required></div>
                            </div>
                            <div class="d-flex justify-content-between mt-5">
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="goToStep(4)">Vorige</button>
                                <button type="submit" class="btn btn-success rounded-pill px-5 btn-lg">Boeken</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global
        let currentStep = 1;
        const pricePerHour = {{ $calculatedPrice ?? 0 }};
        const supplements = @json($supplements);
        let selectedSupplements = {};

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

        // --- Data Fetching ---
        function fetchAvailability() {
            const loading = document.getElementById('calendar-loading');
            if (loading) loading.style.opacity = '1';

            fetch(`{{ route('accommodatie.availability', $accommodatie->id) }}?month=${currentMonth}&year=${currentYear}`)
                .then(res => res.json())
                .then(data => {
                    bookings = data.events || [];
                    if(data.settings) {
                        if(data.settings.min_check_in) minCheckInStr = data.settings.min_check_in.substring(0,5);
                        if(data.settings.max_check_in) maxCheckInStr = data.settings.max_check_in.substring(0,5);
                        if(data.settings.min_duration) minDurationMins = parseInt(data.settings.min_duration);
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
            document.getElementById('calendar-month-year').innerText = dt.toLocaleString('nl-NL', { month: 'long', year: 'numeric' });

            const firstDayOfWeek = (dt.getDay() + 6) % 7;
            const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
            const grid = document.getElementById('calendar-grid');
            grid.innerHTML = '';

            for(let i=0; i<firstDayOfWeek; i++) {
                grid.appendChild(createCell('', 'bg-light border-0'));
            }

            const today = new Date();
            today.setHours(0,0,0,0);

            for(let i=1; i<=daysInMonth; i++) {
                const dateStr = `${currentYear}-${String(currentMonth).padStart(2,'0')}-${String(i).padStart(2,'0')}`;
                const cellDate = new Date(dateStr);
                cellDate.setHours(0,0,0,0);

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
                        if(status === 'partial') {
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

            const dayBookings = bookings.filter(b => b.start.startsWith(dateStr) || b.end.startsWith(dateStr))
                .map(b => {
                    // FIX: Use naive parser to avoid timezone shifts
                    let s = parseNaiveDate(b.start);
                    let e = parseNaiveDate(b.end);

                    if(s < new Date(dateStr + 'T00:00')) s = new Date(dateStr + 'T00:00');
                    if(e > new Date(dateStr + 'T23:59:59')) e = new Date(dateStr + 'T23:59:59');
                    const startM = s.getHours()*60 + s.getMinutes();
                    const endM = e.getHours()*60 + e.getMinutes();
                    return { start: Math.max(startM, openMins), end: Math.min(endM, closeMins) };
                })
                .sort((a,b) => a.start - b.start);

            let maxGap = 0;
            let cursor = openMins;

            dayBookings.forEach(b => {
                if(b.start > cursor) maxGap = Math.max(maxGap, b.start - cursor);
                cursor = Math.max(cursor, b.end);
            });
            if(closeMins > cursor) maxGap = Math.max(maxGap, closeMins - cursor);

            if(maxGap < minDurationMins) return 'full';
            if(dayBookings.length > 0) return 'partial';
            return 'available';
        }

        // --- Timeline Day View ---

        function openDayView(dateStr, dayNum) {
            selectedDate = dateStr;
            const dt = new Date(dateStr);
            document.getElementById('selected-date-display').innerText = dt.toLocaleDateString('nl-NL', { weekday: 'long', day: 'numeric', month: 'long' });
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
            if(endH <= startHour) endH = 24;
            endHour = endH;

            const totalHours = endHour - startHour;
            const containerHeight = totalHours * pxPerHour;

            container.style.height = containerHeight + 'px';
            labels.style.height = containerHeight + 'px';
            eventsLayer.style.height = containerHeight + 'px';
            interactionLayer.style.height = containerHeight + 'px';
            linesLayer.style.height = containerHeight + 'px';

            for(let h = 0; h <= totalHours; h++) {
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

                if(h < totalHours) {
                    const line = document.createElement('div');
                    line.className = 'position-absolute w-100 border-bottom border-light';
                    line.style.top = top + 'px';
                    linesLayer.appendChild(line);
                }
            }

            const dayStartMins = startHour * 60;

            if (Array.isArray(bookings)) {
                // Ensure the bounds are also treated as naive local time boundaries
                const selectedDayStart = new Date(dateStr + 'T00:00:00');
                const selectedDayEnd = new Date(dateStr + 'T23:59:59');

                bookings.forEach(b => {
                    // FIX: Use naive parser here as well
                    let s = parseNaiveDate(b.start);
                    let e = parseNaiveDate(b.end);

                    if (s <= selectedDayEnd && e >= selectedDayStart) {
                        const startM = s.getHours()*60 + s.getMinutes();
                        const endM = e.getHours()*60 + e.getMinutes();
                        let displayStartM = startM;
                        let displayEndM = endM;
                        if (s < selectedDayStart) displayStartM = dayStartMins;
                        if (e > selectedDayEnd) displayEndM = endHour * 60;
                        const visibleStart = Math.max(displayStartM, dayStartMins);
                        const visibleEnd = Math.min(displayEndM, endHour * 60);

                        if(visibleEnd > visibleStart) {
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

            // 1. Mouse Down on the Layer
            layer.addEventListener('mousedown', (e) => {
                e.preventDefault(); // Prevent text selection cursor
                isMouseDown = true;
                hasMoved = false; // Reset "moved" flag

                const rect = layer.getBoundingClientRect();
                dragStartY = e.clientY - rect.top; // Store start relative to layer

                // Show a 0-height selection immediately so visual feedback is instant
                updateVisualSelection(dragStartY, dragStartY);
                document.getElementById('timeline-selection').style.display = 'block';
            });

            // 2. Mouse Move on Window (prevents losing drag if moving fast)
            window.addEventListener('mousemove', (e) => {
                if (!isMouseDown) return;

                const layer = document.getElementById('timeline-interaction-layer');
                const rect = layer.getBoundingClientRect();
                const currentY = e.clientY - rect.top;

                // Only consider it a "drag" if moved more than 3 pixels
                if (Math.abs(currentY - dragStartY) > 3) {
                    hasMoved = true;
                }

                if (hasMoved) {
                    updateVisualSelection(dragStartY, currentY);
                }
            });

            // 3. Mouse Up on Window
            window.addEventListener('mouseup', (e) => {
                if (!isMouseDown) return;
                isMouseDown = false;

                if (hasMoved) {
                    // It was a drag: Validate the dragged range
                    validateSelection();
                } else {
                    // It was a simple click: Create default selection
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
                    e.preventDefault(); // Stop native scrolling
                    isTouchDown = true;
                    hasMoved = false;

                    const rect = layer.getBoundingClientRect();
                    dragStartY = e.touches[0].clientY - rect.top;

                    updateVisualSelection(dragStartY, dragStartY);
                    document.getElementById('timeline-selection').style.display = 'block';
                }
            }, { passive: false });

            layer.addEventListener('touchmove', (e) => {
                if (isTouchDown && e.touches.length > 0) {
                    e.preventDefault(); // Stop scrolling while dragging
                    hasMoved = true;

                    const rect = layer.getBoundingClientRect();
                    const currentY = e.touches[0].clientY - rect.top;

                    updateVisualSelection(dragStartY, currentY);
                }
            }, { passive: false });

            layer.addEventListener('touchend', (e) => {
                if (!isTouchDown) return;
                isTouchDown = false;

                if (hasMoved) {
                    validateSelection();
                } else {
                    // Tap / Click logic
                    createDefaultSelection(dragStartY);
                }
            });
        }

        // --- Helper: Create Default Block (Click) ---
        function createDefaultSelection(startY) {
            // Create a block downwards from the click point with min duration
            const minHeight = minDurationMins * (pxPerHour / 60);
            updateVisualSelection(startY, startY + minHeight);
            validateSelection();
        }

        // --- Helper: Visual Update Only (No Logic Check) ---
        function updateVisualSelection(y1, y2) {
            const top = Math.min(y1, y2);
            const h = Math.abs(y2 - y1);
            const selection = document.getElementById('timeline-selection');
            selection.style.top = top + 'px';
            selection.style.height = h + 'px';
            selection.style.display = 'block'; // Ensure visible

            // Calculate Times just for display label (don't save to globals yet)
            const dayStartMins = startHour * 60;
            const startMins = dayStartMins + (top / (pxPerHour/60));
            const endMins = startMins + (h / (pxPerHour/60));

            const rStart = Math.round(startMins / 15) * 15;
            const rEnd = Math.round(endMins / 15) * 15;

            selection.innerHTML = `<span class="small bg-white px-2 rounded fw-bold text-primary shadow-sm">${minsToTime(rStart)} - ${minsToTime(rEnd)}</span>`;
            selection.className = 'timeline-selection position-absolute bg-primary bg-opacity-25 border border-primary text-center text-primary fw-bold d-flex align-items-center justify-content-center';
        }

        function manualTimeChange() {
            const sVal = document.getElementById('time-start').value;
            const eVal = document.getElementById('time-end').value;
            if(!sVal || !eVal) return;
            const [sH, sM] = sVal.split(':').map(Number);
            const [eH, eM] = eVal.split(':').map(Number);
            selectionStartMins = sH * 60 + sM;
            selectionEndMins = eH * 60 + eM;
            validateSelection(true);
        }

        function validateSelection(skipInputUpdate = false) {
            // 1. Calculate Minutes from CSS Position
            const selection = document.getElementById('timeline-selection');
            const top = parseFloat(selection.style.top);
            const height = parseFloat(selection.style.height);

            const dayStartMins = startHour * 60;
            const rawStart = dayStartMins + (top / (pxPerHour/60));
            const rawEnd = dayStartMins + ((top + height) / (pxPerHour/60));

            // 2. Round to nearest 15
            selectionStartMins = Math.round(rawStart / 15) * 15;
            selectionEndMins = Math.round(rawEnd / 15) * 15;

            // 3. Ensure Min Duration
            if(selectionEndMins - selectionStartMins < minDurationMins) {
                selectionEndMins = selectionStartMins + minDurationMins;
            }

            // 4. Clamp to Day Bounds
            const dayEndMins = endHour * 60;
            if (selectionStartMins < dayStartMins) selectionStartMins = dayStartMins;
            if (selectionEndMins > dayEndMins) selectionEndMins = dayEndMins;

            // 5. Overlap Check
            let hasOverlap = false;
            if (selectedDate && Array.isArray(bookings)) {
                const selectedDayStart = new Date(selectedDate + 'T00:00:00');
                const selectedDayEnd = new Date(selectedDate + 'T23:59:59');
                bookings.forEach(b => {
                    // FIX: Use naive parser here too
                    let s = parseNaiveDate(b.start);
                    let e = parseNaiveDate(b.end);

                    if (s <= selectedDayEnd && e >= selectedDayStart) {
                        let checkStart = s.getHours()*60 + s.getMinutes();
                        let checkEnd = e.getHours()*60 + e.getMinutes();
                        if(s < selectedDayStart) checkStart = dayStartMins;
                        if(e > selectedDayEnd) checkEnd = dayEndMins;
                        if (selectionStartMins < checkEnd && selectionEndMins > checkStart) {
                            hasOverlap = true;
                        }
                    }
                });
            }

            // 6. Snap Visuals to the Validated Minutes
            const finalTop = (selectionStartMins - dayStartMins) * (pxPerHour/60);
            const finalHeight = (selectionEndMins - selectionStartMins) * (pxPerHour/60);

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

            if(!skipInputUpdate) {
                document.getElementById('time-start').value = minsToTime(selectionStartMins);
                document.getElementById('time-end').value = minsToTime(selectionEndMins);
            }
            selection.innerText = `${minsToTime(selectionStartMins)} - ${minsToTime(selectionEndMins)}`;
            document.getElementById('btn-confirm-date').disabled = false;
        }

        function minsToTime(mins) {
            const h = Math.floor(mins / 60);
            const m = Math.floor(mins % 60);
            return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}`;
        }

        function prevMonth() {
            if(currentMonth === 1) { currentMonth = 12; currentYear--; } else { currentMonth--; }
            renderCalendar(); fetchAvailability();
        }
        function nextMonth() {
            if(currentMonth === 12) { currentMonth = 1; currentYear++; } else { currentMonth++; }
            renderCalendar(); fetchAvailability();
        }

        function confirmDate() {
            document.getElementById('input_start_date').value = selectedDate;
            document.getElementById('input_start_time').value = document.getElementById('time-start').value;
            document.getElementById('input_end_date').value = selectedDate;
            document.getElementById('input_end_time').value = document.getElementById('time-end').value;
            goToStep(3);
        }

        function goToStep(step) {
            if(step === 4) calculateTotal();
            currentStep = step;
            document.querySelectorAll('.step-content').forEach(el => el.classList.add('d-none'));
            document.getElementById(`step-content-${step}`).classList.remove('d-none');
            for(let i=1; i<=5; i++) {
                const c = document.getElementById(`step-circle-${i}`);
                if(i < step) { c.className = 'step-circle bg-secondary text-white d-flex justify-content-center align-items-center rounded-circle fw-bold shadow-sm'; c.innerText = '✓'; }
                else if(i === step) { c.className = 'step-circle bg-primary text-white d-flex justify-content-center align-items-center rounded-circle fw-bold shadow-lg'; c.innerText = i; }
                else { c.className = 'step-circle bg-white border text-secondary d-flex justify-content-center align-items-center rounded-circle fw-bold'; c.innerText = i; }
            }
            document.getElementById('progress-line').style.width = ((step-1)/4)*100 + '%';
            window.scrollTo(0,0);
        }

        function calculateTotal() {
            const start = new Date(selectedDate + 'T' + document.getElementById('input_start_time').value);
            const end = new Date(selectedDate + 'T' + document.getElementById('input_end_time').value);
            const hours = (end - start) / 36e5;
            const total = hours * pricePerHour;

            let suppTotal = 0;
            let suppList = '';
            for(const [id, qty] of Object.entries(selectedSupplements)) {
                if(qty > 0) {
                    const el = document.getElementById('qty-'+id);
                    const p = parseFloat(el.dataset.price);
                    suppTotal += p*qty;
                    suppList += `<div class="d-flex justify-content-between text-muted small"><span>Extra x${qty}</span><span>€ ${(p*qty).toFixed(2).replace('.',',')}</span></div>`;
                }
            }

            document.getElementById('overview-dates').innerText = `${start.toLocaleDateString()} ${start.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'})} - ${end.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'})}`;
            document.getElementById('overview-hours').innerText = `${hours.toFixed(1)} uur`;
            document.getElementById('overview-acco-total').innerText = '€ ' + total.toLocaleString('nl-NL',{minimumFractionDigits:2});
            document.getElementById('overview-supplements-list').innerHTML = suppList;
            document.getElementById('overview-grand-total').innerText = '€ ' + (total+suppTotal).toLocaleString('nl-NL',{minimumFractionDigits:2});

            const arr = [];
            for(const [id, qty] of Object.entries(selectedSupplements)) if(qty>0) arr.push({id, qty});
            document.getElementById('supplements_data_input').value = JSON.stringify(arr);
        }

        function changeQty(id, d) {
            const el = document.getElementById('qty-'+id);
            let v = parseInt(el.innerText) + d;
            if(v < 0) v = 0;
            el.innerText = v;
            selectedSupplements[id] = v;
        }
    </script>

    <style>
        .grid-cols-7 { grid-template-columns: repeat(7, 1fr); }
        .hover-shadow:hover { box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; transform: translateY(-2px); }
        .cursor-pointer { cursor: pointer; }
        .step-circle { transition: all 0.3s; }
        .transition-width { transition: width 0.3s; }
        .timeline-grid::-webkit-scrollbar { width: 6px; }
        .timeline-grid::-webkit-scrollbar-thumb { background: #ccc; border-radius: 3px; }
        @media (max-width: 576px) { .no-mobile { display: none; } }
    </style>
@endsection
