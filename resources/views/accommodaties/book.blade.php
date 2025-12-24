@extends('layouts.app')

@section('content')

    @php
        // --- Price Calculation Logic (UNCHANGED) ---
                   $allPrices = $accommodatie->prices->map(fn($p) => $p->price);

                   $basePrices = $allPrices->where('type', 0);
                   $percentageAdditions = $allPrices->where('type', 1);
                   $fixedDiscounts = $allPrices->where('type', 2);
                   $extraCosts = $allPrices->where('type', 3);
                   $percentageDiscounts = $allPrices->where('type', 4);

                   $totalBasePrice = $basePrices->sum('amount');
                   $preDiscountPrice = $totalBasePrice;

                   // 1. Apply percentage additions
                   $totalPercentageAdditions = 0;
                   foreach ($percentageAdditions as $percentage) {
                       $preDiscountPrice += $totalBasePrice * ($percentage->amount / 100);
                       $totalPercentageAdditions += $percentage->amount;
                   }

                   $calculatedPrice = $preDiscountPrice;

                   $totalPercentageDiscounts = 0;
                   // 2. Apply percentage discounts
                   foreach ($percentageDiscounts as $percentage) {
                       $calculatedPrice -= $preDiscountPrice * ($percentage->amount / 100);
                       $totalPercentageDiscounts += $percentage->amount;
                   }

                   // 3. Apply fixed amount discounts
                   $calculatedPrice -= $fixedDiscounts->sum('amount');

                   $hasDiscount = $fixedDiscounts->isNotEmpty() || $percentageDiscounts->isNotEmpty();
                   // --- End Price Calculation ---


    @endphp

    <div class="rounded-bottom-5 bg-light mt-5 pb-5"
         style="position: relative; margin-top: 0 !important; z-index: 10; background-image: url('{{ asset('img/logo/doodles/Blad StretchA_white.webp') }}'); background-repeat: repeat; background-size: cover;">

        <div class="container">
            <h1 class="fw-bold mb-4 text-center">Boek je verblijf: {{ $accommodatie->name }}</h1>

            <!-- Progress Bar -->
            <div class="row justify-content-center mb-5">
                <div class="col-md-10 col-lg-8">
                    <div class="position-relative d-flex justify-content-between align-items-center">
                        <!-- Line -->
                        <div class="position-absolute w-100 start-0 translate-middle-y bg-secondary-subtle" style="height: 4px; top: 30%; z-index: 0;"></div>
                        <div class="position-absolute start-0 translate-middle-y bg-primary transition-width" style="height: 4px; top: 30%; z-index: 0; width: 0%;" id="progress-line"></div>

                        <!-- Step 1 -->
                        <div class="d-flex flex-column align-items-center position-relative z-1">
                            <div class="step-circle bg-primary text-white d-flex justify-content-center align-items-center rounded-circle fw-bold shadow-sm" style="width: 40px; height: 40px;" id="step-circle-1">1</div>
                            <span class="small fw-bold mt-2 bg-light px-2">Datum & Tijd</span>
                        </div>

                        <!-- Step 2 -->
                        <div class="d-flex flex-column align-items-center position-relative z-1">
                            <div class="step-circle bg-white border border-2 border-secondary-subtle text-secondary d-flex justify-content-center align-items-center rounded-circle fw-bold" style="width: 40px; height: 40px;" id="step-circle-2">2</div>
                            <span class="small mt-2 bg-light px-2 text-muted">Extra's</span>
                        </div>

                        <!-- Step 3 -->
                        <div class="d-flex flex-column align-items-center position-relative z-1">
                            <div class="step-circle bg-white border border-2 border-secondary-subtle text-secondary d-flex justify-content-center align-items-center rounded-circle fw-bold" style="width: 40px; height: 40px;" id="step-circle-3">3</div>
                            <span class="small mt-2 bg-light px-2 text-muted">Overzicht</span>
                        </div>

                        <!-- Step 4 -->
                        <div class="d-flex flex-column align-items-center position-relative z-1">
                            <div class="step-circle bg-white border border-2 border-secondary-subtle text-secondary d-flex justify-content-center align-items-center rounded-circle fw-bold" style="width: 40px; height: 40px;" id="step-circle-4">4</div>
                            <span class="small mt-2 bg-light px-2 text-muted">Betalen</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content bg-white -->
            <div class="bg-white shadow-lg border-0 rounded-5 overflow-hidden">
                <div class=" p-0">
                    <form id="booking-form" action="{{ route('accommodatie.store_booking') }}" method="POST">
                        @csrf
                        <input type="hidden" name="accommodatie_id" value="{{ $accommodatie->id }}">
                        <input type="hidden" name="supplements_data" id="supplements_data_input">

                        <!-- Step 1: Date & Time -->
                        <div id="step-content-1" class="step-content p-4 p-md-5">
                            <h3 class="h4 fw-bold mb-4 text-primary">Kies je datum en tijd</h3>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Startdatum & Tijd</label>
                                    <div class="input-group">
                                        <input type="date" class="form-control form-control-lg bg-light border-0" name="start_date" id="start_date" required min="{{ date('Y-m-d') }}">
                                        <input type="time" class="form-control form-control-lg bg-light border-0" name="start_time" id="start_time" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Einddatum & Tijd</label>
                                    <div class="input-group">
                                        <input type="date" class="form-control form-control-lg bg-light border-0" name="end_date" id="end_date" required min="{{ date('Y-m-d') }}">
                                        <input type="time" class="form-control form-control-lg bg-light border-0" name="end_time" id="end_time" required>
                                    </div>
                                </div>
                            </div>
                            <div id="availability-message" class="mt-3"></div>
                            <div class="d-flex justify-content-end mt-4">
                                <button type="button" class="btn btn-primary rounded-pill px-5 btn-lg" onclick="validateStep1()">Volgende <i class="bi bi-arrow-right ms-2"></i></button>
                            </div>
                        </div>

                        <!-- Step 2: Supplements -->
                        <div id="step-content-2" class="step-content p-4 p-md-5 d-none">
                            <h3 class="h4 fw-bold mb-4 text-primary">Voeg extra's toe (Optioneel)</h3>
                            <p class="text-muted mb-4">Maak je verblijf compleet met deze extra opties.</p>

                            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">

                                @foreach($supplements as $supplement)
                                    <a href="{{ route('shop.details', $supplement->id) }}" class="text-decoration-none" style="min-width: 200px;">
                                        <div
                                            class="shop-tile h-100 d-flex flex-column bg-white overflow-hidden position-relative">
                                            @if($supplement->image !== null)
                                        <div class="tile-image-wrapper position-relative">
                                            <img src="{{ asset('/files/products/images/'.$supplement->image) }}"
                                                 class="w-100 h-100 object-fit-cover tile-img"
                                                 alt="{{ $supplement->name }}">

                                            {{-- Type Badge --}}
                                            <span class="tile-badge">
                                                {{ $categoryNames[$supplement->type] ?? 'Extra' }}
                                            </span>
                                        </div>
                                            @endif

                                        {{-- Content Section --}}
                                        <div class="p-4 d-flex flex-column flex-grow-1">
                                            <h3 class="h5 fw-bold text-dark mb-2">{{ $supplement->name }}</h3>

                                            <div class="text-muted small mb-4 flex-grow-1 tile-description">
                                                {{ \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode($supplement->description))), 80, '...') }}
                                            </div>

                                            <div
                                                class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top border-light">
                                                <div class="price-tag">
                                                    € {{ number_format($supplement->calculated_price, 2, ',', '.') }}
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle" style="width: 30px; height: 30px; padding: 0;" onclick="changeQty({{ $supplement->id }}, -1)">-</button>
                                                    <span class="fw-bold" id="qty-{{ $supplement->id }}" data-price="{{ $supplement->calculated_price }}">0</span>
                                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-circle" style="width: 30px; height: 30px; padding: 0;" onclick="changeQty({{ $supplement->id }}, 1)">+</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="d-flex justify-content-between mt-5">
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="goToStep(1)"><i class="bi bi-arrow-left me-2"></i> Vorige</button>
                                <button type="button" class="btn btn-primary rounded-pill px-5" onclick="goToStep(3)">Volgende <i class="bi bi-arrow-right ms-2"></i></button>
                            </div>
                        </div>

                        <!-- Step 3: Overview -->
                        <div id="step-content-3" class="step-content p-4 p-md-5 d-none">
                            <h3 class="h4 fw-bold mb-4 text-primary">Overzicht van je boeking</h3>

                            <div class="bg-light rounded-4 p-4 mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-3">
                                    <div>
                                        <h5 class="fw-bold mb-1">{{ $accommodatie->name }}</h5>
                                        <p class="text-muted mb-0 small" id="overview-dates">...</p>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold fs-5" id="overview-acco-total">€ 0,00</div>
                                        <div class="small text-muted" id="overview-hours">0 uur x € {{ number_format($calculatedPrice, 2, ',', '.') }}</div>
                                    </div>
                                </div>

                                <div id="overview-supplements-list">
                                    <!-- JS populates this -->
                                </div>

                                <div class="d-flex justify-content-between align-items-center pt-3 mt-2 border-top border-2">
                                    <h4 class="fw-bold mb-0">Totaal</h4>
                                    <h4 class="fw-bold text-primary mb-0" id="overview-grand-total">€ 0,00</h4>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="goToStep(2)"><i class="bi bi-arrow-left me-2"></i> Vorige</button>
                                <button type="button" class="btn btn-primary rounded-pill px-5" onclick="goToStep(4)">Naar betalen <i class="bi bi-arrow-right ms-2"></i></button>
                            </div>
                        </div>

                        <!-- Step 4: Details & Pay -->
                        <div id="step-content-4" class="step-content p-4 p-md-5 d-none">
                            <h3 class="h4 fw-bold mb-4 text-primary">Vul je gegevens in</h3>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Voornaam</label>
                                    <input type="text" class="form-control bg-light border-0" name="first_name" value="{{ Auth::check() ? Auth::user()->name : '' }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Achternaam</label>
                                    <input type="text" class="form-control bg-light border-0" name="last_name" value="{{ Auth::check() ? (Auth::user()->infix ? Auth::user()->infix . ' ' : '') . Auth::user()->last_name : '' }}" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">E-mailadres</label>
                                    <input type="email" class="form-control bg-light border-0" name="email" value="{{ Auth::check() ? Auth::user()->email : '' }}" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Adres</label>
                                    <input type="text" class="form-control bg-light border-0" name="address" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Postcode</label>
                                    <input type="text" class="form-control bg-light border-0" name="zipcode" required>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Woonplaats</label>
                                    <input type="text" class="form-control bg-light border-0" name="city" required>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-5">
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="goToStep(3)"><i class="bi bi-arrow-left me-2"></i> Vorige</button>
                                <button type="submit" class="btn btn-success rounded-pill px-5 btn-lg shadow">Betalen & Boeken <i class="bi bi-check-lg ms-2"></i></button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for Logic -->
    <script>
        let currentStep = 1;
        const accommodationPricePerHour = {{ $calculatedPrice }};
        const supplements = @json($supplements);
        const selectedSupplements = {}; // { id: qty }

        function updateProgress() {
            // Update Line
            const progress = ((currentStep - 1) / 3) * 100;
            document.getElementById('progress-line').style.width = progress + '%';

            // Update Circles
            for (let i = 1; i <= 4; i++) {
                const circle = document.getElementById(`step-circle-${i}`);
                const label = circle.nextElementSibling;

                if (i < currentStep) {
                    circle.className = 'step-circle bg-secondary text-white d-flex justify-content-center align-items-center rounded-circle fw-bold shadow-sm';
                    circle.innerHTML = i;
                    label.classList.remove('text-muted');
                    label.classList.add('text-success');
                } else if (i === currentStep) {
                    circle.className = 'step-circle bg-primary text-white d-flex justify-content-center align-items-center rounded-circle fw-bold shadow-lg';
                    circle.innerHTML = i;
                    label.classList.remove('text-muted');
                    label.classList.add('fw-bold');
                } else {
                    circle.className = 'step-circle bg-white border border-2 border-secondary-subtle text-secondary d-flex justify-content-center align-items-center rounded-circle fw-bold';
                    circle.innerHTML = i;
                    label.classList.add('text-muted');
                    label.classList.remove('fw-bold', 'text-success');
                }
            }

            // Show Content
            document.querySelectorAll('.step-content').forEach(el => el.classList.add('d-none'));
            document.getElementById(`step-content-${currentStep}`).classList.remove('d-none');
        }

        function changeQty(id, delta) {
            const el = document.getElementById(`qty-${id}`);
            let qty = parseInt(el.innerText);
            qty += delta;
            if (qty < 0) qty = 0;
            el.innerText = qty;
            selectedSupplements[id] = qty;
        }

        function calculateTotal() {
            const start = new Date(document.getElementById('start_date').value + 'T' + document.getElementById('start_time').value);
            const end = new Date(document.getElementById('end_date').value + 'T' + document.getElementById('end_time').value);

            const hours = (end - start) / 36e5; // diff in hours
            const accoTotal = hours * accommodationPricePerHour;

            let suppTotal = 0;
            let suppHtml = '';

            supplements.forEach(sup => {
                const qty = selectedSupplements[sup.id] || 0;
                if (qty > 0) {
                    // Assuming calculated_price is available on the model passed to view
                    const price = parseFloat(document.getElementById(`qty-${sup.id}`).dataset.price);
                    const total = price * qty;
                    suppTotal += total;
                    suppHtml += `
                        <div class="d-flex justify-content-between align-items-center mb-2 small text-muted">
                            <span>${sup.name} (x${qty})</span>
                            <span>€ ${total.toLocaleString('nl-NL', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                        </div>
                    `;
                }
            });

            const grandTotal = accoTotal + suppTotal;

            // Update UI
            document.getElementById('overview-dates').innerText = `${start.toLocaleString('nl-NL')} tot ${end.toLocaleString('nl-NL')}`;
            document.getElementById('overview-hours').innerText = `${hours.toLocaleString('nl-NL', {maximumFractionDigits: 1})} uur x € ${accommodationPricePerHour.toLocaleString('nl-NL', {minimumFractionDigits: 2})}`;
            document.getElementById('overview-acco-total').innerText = '€ ' + accoTotal.toLocaleString('nl-NL', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('overview-supplements-list').innerHTML = suppHtml;
            document.getElementById('overview-grand-total').innerText = '€ ' + grandTotal.toLocaleString('nl-NL', {minimumFractionDigits: 2, maximumFractionDigits: 2});

            // Prepare Input for Form
            const supplementDataArray = [];
            for (const [id, qty] of Object.entries(selectedSupplements)) {
                if (qty > 0) supplementDataArray.push({id: id, qty: qty});
            }
            document.getElementById('supplements_data_input').value = JSON.stringify(supplementDataArray);
        }

        async function validateStep1() {
            const sDate = document.getElementById('start_date').value;
            const sTime = document.getElementById('start_time').value;
            const eDate = document.getElementById('end_date').value;
            const eTime = document.getElementById('end_time').value;
            const msg = document.getElementById('availability-message');

            if (!sDate || !sTime || !eDate || !eTime) {
                msg.innerHTML = '<div class="alert alert-warning">Vul alle datum- en tijdvelden in.</div>';
                return;
            }

            const start = new Date(`${sDate}T${sTime}`);
            const end = new Date(`${eDate}T${eTime}`);

            if (end <= start) {
                msg.innerHTML = '<div class="alert alert-danger">De eindtijd moet na de starttijd liggen.</div>';
                return;
            }

            // AJAX Check
            msg.innerHTML = '<div class="text-primary"><div class="spinner-border spinner-border-sm me-2"></div>Beschikbaarheid controleren...</div>';

            try {
                const response = await fetch("{{ route('accommodatie.check_availability') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        start_date: sDate, start_time: sTime,
                        end_date: eDate, end_time: eTime
                    })
                });
                const data = await response.json();

                if (data.available) {
                    msg.innerHTML = '';
                    goToStep(2);
                } else {
                    msg.innerHTML = `<div class="alert alert-danger">${data.message || 'Niet beschikbaar.'}</div>`;
                }
            } catch (e) {
                msg.innerHTML = '<div class="alert alert-danger">Er ging iets mis bij het controleren. Probeer het opnieuw.</div>';
            }
        }

        function goToStep(step) {
            if (step === 3) calculateTotal();
            currentStep = step;
            updateProgress();
            window.scrollTo(0, 0);
        }
    </script>

    <style>
        .step-circle {
            transition: all 0.3s ease;
            z-index: 2;
        }
        .transition-width {
            transition: width 0.3s ease;
        }

             /* Shop Tile */
         .shop-tile {
             border-radius: 16px;
             box-shadow: 0 2px 15px rgba(0, 0, 0, 0.04);
             transition: transform 0.3s ease, box-shadow 0.3s ease;
             border: 1px solid rgba(0, 0, 0, 0.02);
         }

        .shop-tile:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.08);
        }

        /* Image Area */
        .tile-image-wrapper {
            height: 200px; /* Slightly smaller for supplements */
            overflow: hidden;
        }

        .tile-img {
            transition: transform 0.5s ease;
        }

        .shop-tile:hover .tile-img {
            transform: scale(1.05);
        }

        .tile-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(4px);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            color: #212529;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        /* Text & Layout */
        .tile-description {
            line-height: 1.6;
            opacity: 0.8;
        }

        .price-tag {
            font-size: 1.1rem;
            font-weight: 700;
            color: #198754;
        }

        /* Action Buttons */
        .action-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-view {
            background: #f1f3f5;
            color: #495057;
        }

        .btn-view:hover {
            background: #e9ecef;
            color: #212529;
        }

        .btn-cart {
            background: #212529;
            color: white;
        }

        .btn-cart:hover {
            background: #000;
            transform: scale(1.05);
        }
    </style>
@endsection
