@php
    use App\Models\Accommodatie;
    use App\Models\Activity;
    use App\Models\Product;
@endphp
@extends('layouts.app')

@section('content')
    <div class="container mt-5 mb-5 py-5 col-md-11">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Boeking {{ $booking->accommodatie->name }} {{ $booking->start->format('d-m-Y')  }}</h1>
            @php
                $statusLabel = match($booking->status) {
                    'confirmed' => 'Bevestigd',
                    'reserved' => 'Gereserveerd',
                    'completed' => 'Afgerond',
                    'pending'   => 'In afwachting',
                    'cancelled' => 'Geannuleerd',
                    default     => 'Onbekend'
                };
                $statusClass = match($booking->status) {
                    'confirmed', 'completed' => 'success',
                    'pending', 'reserved'   => 'warning',
                    'cancelled' => 'danger',
                    default     => 'secondary'
                };
            @endphp
            <span class="badge bg-{{ $statusClass }} fs-6 px-3 py-2 rounded-pill">{{ $statusLabel }}</span>
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

        <div class="d-flex flex-column gap-3">

            {{-- 1. MAIN BOOKING & EXTRAS CARD --}}
            <div>
                <div class="bg-white border w-100 p-4 rounded mt-3">
                    <h2 class="flex-row gap-3">
                        <span class="material-symbols-rounded me-2">house</span>
                        Boeking & Extra's
                    </h2>

                    <div class="p-0">
                        <div class="d-none d-md-flex justify-content-between text-muted fw-bold small text-uppercase px-4 py-2 border-bottom">
                            <div class="">Omschrijving</div>
                            <div class="text-center">Aantal/Duur</div>
                        </div>

                        {{-- A. The Main Accommodation Item --}}
                        <div class="border-bottom border-3 bg-light p-4">
                            <div class="row justify-content-between align-items-center">
                                <div class="col-12 col-md-6">
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center mb-1">
                                            @if($booking->accommodatie && $booking->accommodatie->image)
                                                <img alt="accommodatie" class="rounded me-3 zoomable-image"
                                                     style="width: 100px; aspect-ratio: 1/1; object-fit: cover"
                                                     src="{{ asset('/files/accommodaties/images/'.$booking->accommodatie->image) }}">
                                            @else
                                                <div class="rounded me-3 bg-light d-flex align-items-center justify-content-center"
                                                     style="width: 100px; aspect-ratio: 1/1;">
                                                    <span class="material-symbols-rounded text-muted">home</span>
                                                </div>
                                            @endif

                                            <div>
                                                <span class="fw-bold text-dark d-block fs-5">{{ $booking->accommodatie->name ?? 'Accommodatie' }}</span>
                                                <span class="small text-muted">{{ $booking->accommodatie->type ?? '' }}</span>
                                            </div>
                                        </div>

                                        {{-- Comments specific to the booking itself --}}
                                        @if($booking->comment)
                                            <div class="mt-2 p-3 bg-light rounded border small">
                                                <span class="fw-bold text-secondary">Opmerking:</span>
                                                <span class="text-dark">{{ $booking->comment }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class=" col-md-4 text-md-end text-muted">
                                    <div class="small">
                                        <span class="fw-bold">In:</span> {{ $booking->start->format('d-m-Y H:i') }}
                                    </div>
                                    <div class="small">
                                        <span class="fw-bold">Uit:</span> {{ $booking->end->format('d-m-Y H:i') }}
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- B. The Extras (Looping through Order Items associated with Booking) --}}
                        @if($booking->order && $booking->order->items)
                            @foreach($booking->order->items as $item)
                                @php
                                    // Skip the item if it IS the accommodation name (to avoid duplicate showing)
                                    if($booking->accommodatie && str_contains($item->product_name, $booking->accommodatie->name)) {
                                        continue;
                                    }

                                    $product = $item->product_id ? Product::find($item->product_id) : null;
                                    // Determine image
                                    $imagePath = null;
                                    if($product && $product->image) {
                                        $imagePath = asset('/files/products/images/'.$product->image);
                                    }

                                    // --- UPDATED LOGIC START ---
                                    $itemResponses = collect();

                                    if ($item->product_id) {
                                        // 1. Get ALL responses for this product type
                                        $allProductResponses = $formResponses->where('product_id', $item->product_id);

                                        // 2. Group them by submission ID so we have distinct "sets" of answers
                                        $groupedResponses = $allProductResponses->groupBy('submitted_id')->values();

                                        // 3. Get all items in this specific order that match this product ID
                                        // We use values() to ensure keys are 0, 1, 2...
                                        $sameItemsInOrder = $booking->order->items->where('product_id', $item->product_id)->values();

                                        // 4. Find the index of the CURRENT item ($item) within that list
                                        $currentIndex = $sameItemsInOrder->search(function($val) use ($item) {
                                            return $val->id === $item->id;
                                        });

                                        // 5. Pick the response group that matches the index of the item
                                        if ($groupedResponses->has($currentIndex)) {
                                            $itemResponses = $groupedResponses->get($currentIndex);
                                        }

                                    } else {
                                        $activityResponses = $formResponses->where('location', 'activity');
                                        foreach($activityResponses->groupBy('activity_id') as $actId => $responses) {
                                             $activity = $responses->first()->activity ?? null;
                                             if($activity && str_contains($item->product_name, $activity->title)) {
                                                 $itemResponses = $responses;
                                                 break;
                                             }
                                        }
                                    }
                                    // --- UPDATED LOGIC END ---
                                @endphp

                                <div class="border-bottom p-4">
                                    <div class="row justify-content-between gy-3 align-items-center">
                                        <div class="col-12 col-md-6">
                                            <div class="d-flex flex-column">
                                                <div class="d-flex align-items-center mb-1">
                                                    @if($imagePath)
                                                        <img alt="product" class="rounded me-3 zoomable-image"
                                                             style="width: 100px; aspect-ratio: 1/1; object-fit: cover"
                                                             src="{{ $imagePath }}">
                                                    @else
                                                        <div class="rounded me-3 bg-light d-flex align-items-center justify-content-center"
                                                             style="width: 100px; aspect-ratio: 1/1;">
                                                            <span class="material-symbols-rounded text-muted">local_mall</span>
                                                        </div>
                                                    @endif

                                                    <span class="fw-bold text-dark">{{ $item->product_name }}</span>
                                                </div>

                                                {{-- Form Responses --}}
                                                @if($itemResponses->isNotEmpty())
                                                    <div class="mt-2 p-3 bg-light rounded border small">
                                                        @foreach($itemResponses->groupBy('submitted_id') as $index => $group)
                                                            @if($itemResponses->groupBy('submitted_id')->count() > 1)
                                                                <div class="fw-bold text-secondary mb-1 border-bottom pb-1">
                                                                    Aanvraag {{ $loop->iteration }}</div>
                                                            @endif

                                                            <div class="d-flex flex-column gap-1 mb-2 last-mb-0">
                                                                @foreach($group as $response)
                                                                    <div class="d-flex justify-content-between">
                                                                        <span class="text-muted">{{ $response->formElement->label ?? 'Veld' }}:</span>
                                                                        <span class="text-dark text-end fw-medium">{{ $response->response }}</span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-md-4 text-md-end text-muted">
                                            <span class="d-md-none small text-uppercase">Aantal: </span>
                                            {{ $item->quantity }}x
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                    </div>
                </div>

                {{-- 2. SUPPLEMENTS CARD --}}
                <div class="bg-white border w-100 p-4 rounded mt-3">
                    <form action="{{ route('cart.bulk_add') }}" method="POST" id="bulk-add-form">
                        @csrf
                        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                            <h2 class="d-flex align-items-center gap-3 mb-0">
                                <span class="material-symbols-rounded">shop</span>
                                Bestel meer extra's bij je boeking
                            </h2>

                            <input type="hidden" name="items" id="bulk-items-input">
                            <input type="hidden" name="existing_order_id" value="{{ $booking->order->id ?? '' }}">

                            {{-- Actions --}}
                            <div class="d-flex gap-2">
{{--                                <button type="button" onclick="checkFormsAndProceed()" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" id="btn-bulk-order" disabled>--}}
{{--                                    <span class="material-symbols-rounded fs-6 me-1 align-middle">shopping_cart</span>--}}
{{--                                    Bijbestellen--}}
{{--                                </button>--}}

                                <button
                                    onclick="checkFormsAndProceed()"
                                    class="btn btn-success flex flex-row align-items-center justify-content-center" id="btn-bulk-order">
                                    <span class="button-text" id="btn-bulk-order-text"> <span class="material-symbols-rounded fs-6 me-1 align-middle">shopping_cart</span>
                                    Bijbestellen</span>
                                    <span style="display: none" class="loading-spinner spinner-border spinner-border-sm" aria-hidden="true"></span>
                                    <span style="display: none" class="loading-text" role="status">Laden...</span>
                                </button>
                            </div>
                        </div>

                        {{-- Grid View --}}
                        <div id="supplements-grid" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                            @foreach ($supplements as $supplement)
                                <div class="col">
                                    <div class="shop-tile h-100 d-flex flex-column bg-white overflow-hidden border rounded-4 position-relative group-hover-trigger">
                                        @if($supplement->image !== null)
                                            {{-- Image Section with Popup Trigger --}}
                                            <div class="tile-image-wrapper position-relative" style="height: 180px; cursor: pointer;" onclick="showProductDetails({{ $supplement->id }})">
                                                <img src="{{ asset('/files/products/images/'.$supplement->image) }}"
                                                     class="w-100 h-100 object-fit-cover tile-img"
                                                     alt="{{ $supplement->name }}">
                                            </div>
                                        @endif

                                        {{-- Content Section --}}
                                        <div class="p-4 d-flex flex-column flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h5 class="h6 fw-bold text-dark mb-0" style="cursor: pointer;" onclick="showProductDetails({{ $supplement->id }})">{{ $supplement->name }}</h5>
                                                <button type="button" class="btn btn-sm text-muted p-0 ms-2" onclick="showProductDetails({{ $supplement->id }})" title="Meer informatie">
                                                    <span class="material-symbols-rounded" style="font-size: 1.2rem;">info</span>
                                                </button>
                                            </div>

                                            <div class="text-muted small mb-4 flex-grow-1 tile-description" style="cursor: pointer;" onclick="showProductDetails({{ $supplement->id }})">
                                                {{ \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode($supplement->description))), 80, '...') }}
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top border-light">
                                                <div class="price-tag fw-bold text-success">
                                                    € {{ number_format($supplement->calculated_price, 2, ',', '.') }}
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle" style="width:32px;height:32px" onclick="changeQty({{ $supplement->id }}, -1)">-</button>
                                                    <span class="fw-bold px-2" id="qty-{{ $supplement->id }}">0</span>
                                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-circle" style="width:32px;height:32px" onclick="changeQty({{ $supplement->id }}, 1)">+</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </form>
                </div>

                {{-- 3. GUEST DETAILS CARD --}}
                <div class="bg-white border w-100 p-4 rounded mt-3">
                    <h2 class="flex-row gap-3">
                        <span class="material-symbols-rounded me-2">person</span>
                        Gastgegevens
                    </h2>

                    <div class="d-flex align-items-center mb-3 mt-4">
                        <div class="bg-light rounded-5-circle p-2 me-3 text-secondary">
                            <span class="material-symbols-rounded fs-3">account_circle</span>
                        </div>
                        <div>
                            {{-- Prefer User relation, fallback to Order data --}}
                            <div class="fw-bold text-dark">
                                {{ $booking->user->name ?? ($booking->order->first_name ?? '') }}
                                {{ $booking->user->last_name ?? ($booking->order->last_name ?? '') }}
                            </div>
                            <a href="mailto:{{ $booking->user->email ?? ($booking->order->email ?? '') }}"
                               class="small text-decoration-none">
                                {{ $booking->user->email ?? ($booking->order->email ?? '') }}
                            </a>
                        </div>
                    </div>

                    <hr class="text-muted opacity-25">

                    <div class="small text-muted">
                        <div class="fw-bold text-uppercase mb-1" style="font-size: 0.7rem;">Adres</div>
                        <div>{{ $booking->user->address ?? ($booking->order->address ?? '-') }}</div>
                        <div>
                            {{ $booking->user->zipcode ?? ($booking->order->zipcode ?? '') }}
                            {{ $booking->user->city ?? ($booking->order->city ?? '') }}
                        </div>
                        <div>{{ $booking->user->country ?? ($booking->order->country ?? '') }}</div>
                    </div>
                </div>

                {{-- 4. INFO / STATUS CARD --}}
                <div class="bg-white border w-100 p-4 rounded mt-3">
                    <h2 class="flex-row gap-3">
                        <span class="material-symbols-rounded me-2">analytics</span>
                        Details
                    </h2>
                    <ul class="list-unstyled mb-0 small">
                        <li class="d-flex justify-content-between py-2 border-bottom border-light">
                            <span class="text-muted">Boekingsnummer</span>
                            <span class="fw-medium text-dark">#{{ $booking->id }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom border-light">
                            <span class="text-muted">Aankomst</span>
                            <span class="fw-medium text-dark">{{ $booking->start->format('d-m-Y H:i') }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom border-light">
                            <span class="text-muted">Vertrek</span>
                            <span class="fw-medium text-dark">{{ $booking->end->format('d-m-Y H:i') }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-2">
                            <span class="text-muted">Status</span>
                            <span class="badge bg-{{ $statusClass }} bg-opacity-10 text-{{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </div>


    <div id="productPopup" class="popup" style="display: none; z-index: 99999; top: 0; left: 0; position: fixed">
        <div class="popup-body">
            <div class="page">
                <h2 id="popup-title" class="fw-bold mb-0"></h2>

                <div id="popup-img-wrapper" class="mb-3 text-center d-none">
                    <img id="popup-img" src="" class="img-fluid rounded" style="max-height: 300px;">
                </div>

                <div id="popup-desc" style="text-align: left; max-height: 50vh; overflow-y: scroll"></div>

            </div>
            <div class="popup-header">
                <h3 id="popup-title" class="fw-bold mb-0"></h3>
                <button onclick="closeProductPopup()" class="btn btn-outline-danger"><span class="material-symbols-rounded">close</span></button>
            </div>
        </div>
    </div>

    <div id="formsPopup" class="popup" style="display: none; z-index: 99999; top: 0; left: 0; position: fixed">

        <div class="popup-body">
            <div class="popup-header">
                <h3 class="fw-bold mb-0 text-primary">Extra informatie nodig</h3>
            </div>
            <div class="popup-body-scroll">
                <div class="alert alert-info border-0 rounded-3 mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="material-symbols-rounded">info</span>
                        <small>Voor enkele van je gekozen opties hebben we aanvullende gegevens nodig.</small>
                    </div>
                </div>
                {{-- Dynamic forms will be moved here by JS --}}
                <div id="dynamic-forms-container" style="max-height: 400px; overflow-y: scroll"></div>
            </div>
            <div class="popup-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill me-2" onclick="closeFormsPopup()">Annuleren</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" onclick="submitBulkOrder(true)">
                    Bevestigen & Doorgaan
                </button>
            </div>
        </div>
    </div>

    <style>
        /* Helper */
        .last-mb-0:last-child { margin-bottom: 0 !important; }

        /* Tiles */
        .group-hover-trigger:hover .tile-image-wrapper img {
            transform: scale(1.05);
            transition: transform 0.3s ease;
        }
        .tile-image-wrapper { overflow: hidden; }

        .btn-close-custom {
            background: none;
            border: none;
            color: #999;
            transition: color 0.2s;
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
        }
        .btn-close-custom:hover { color: #333; }
    </style>

    <script>
        // Data
        const supplements = @json($supplements);
        let selectedSupplements = {};

        function changeQty(id, d) {
            const el = document.getElementById('qty-'+id);
            let currentVal = parseInt(el.innerText);
            let newVal = currentVal + d;

            if(newVal < 0) newVal = 0;

            el.innerText = newVal;
            selectedSupplements[id] = newVal;

            updateOrderButton();
        }

        function updateOrderButton() {
            let totalQty = 0;
            for(const qty of Object.values(selectedSupplements)) {
                totalQty += qty;
            }

            const btn = document.getElementById('btn-bulk-order-text');
            if(totalQty > 0) {
                btn.disabled = false;
                btn.innerHTML = `<span class="material-symbols-rounded fs-6 me-1 align-middle">shopping_cart</span> Bijbestellen (${totalQty})`;
            } else {
                btn.disabled = true;
                btn.innerHTML = `<span class="material-symbols-rounded fs-6 me-1 align-middle">shopping_cart</span> Bijbestellen`;
            }
        }

        // --- CHECK LOGIC & FORMS ---
        function checkFormsAndProceed() {
            let button = document.getElementById('btn-bulk-order')

            button.disabled = true;
            button.classList.add('loading');

            // Show the spinner and hide the text
            button.querySelector('.button-text').style.display = 'none';
            button.querySelector('.loading-spinner').style.display = 'inline-block';
            button.querySelector('.loading-text').style.display = 'inline-block';

            // 1. Check if any selected supplement has forms
            let formsNeeded = false;
            const container = document.getElementById('dynamic-forms-container');
            container.innerHTML = ''; // Clear previous

            for(const [id, qty] of Object.entries(selectedSupplements)) {
                if(qty > 0) {
                    const product = supplements.find(p => p.id == id);
                    if(product && product.form_elements && product.form_elements.length > 0) {
                        formsNeeded = true;
                        renderProductForm(product, qty, container);
                    }
                }
            }

            // 2. Decide flow
            if(formsNeeded) {
                openFormsPopup();
            } else {
                // No forms -> Direct submit
                submitBulkOrder(false);
            }
        }

        // --- SUBMISSION LOGIC ---
        function submitBulkOrder(fromPopup = false) {
            // Validation if submitting from popup
            if (fromPopup) {
                const container = document.getElementById('dynamic-forms-container');
                const inputs = container.querySelectorAll('input, select');
                let valid = true;
                inputs.forEach(i => {
                    if(i.checkValidity && !i.checkValidity()) {
                        i.reportValidity();
                        valid = false;
                    }
                });
                if(!valid) return;
            }

            // Prepare JSON
            const items = [];
            for (const [id, qty] of Object.entries(selectedSupplements)) {
                if (qty > 0) {
                    items.push({ id: id, qty: qty });
                }
            }

            document.getElementById('bulk-items-input').value = JSON.stringify(items);

            // If from popup, we need to make sure the form inputs are inside the form tag
            // Since the popup is outside the form, we can just append the hidden container to the form before submit?
            // OR: simpler: the inputs in the popup have 'form="bulk-add-form"' attribute?
            // OR: we move the inputs back to the form hiddenly.

            if(fromPopup) {
                const container = document.getElementById('dynamic-forms-container');
                // Move the container back into the form temporarily to submit
                const form = document.getElementById('bulk-add-form');
                // Hide it
                container.style.display = 'none';
                form.appendChild(container);
            }

            document.getElementById('bulk-add-form').submit();
        }

        // --- RENDER HELPERS ---
        function renderProductForm(product, qty, container) {
            const wrapper = document.createElement('div');
            wrapper.className = 'bg-light border rounded-3 p-3 mb-3';

            const title = document.createElement('h6');
            title.className = 'fw-bold mb-3 border-bottom pb-2 text-primary';
            title.innerText = `${product.name} (Opties)`;
            wrapper.appendChild(title);

            product.form_elements.forEach(el => {
                const group = document.createElement('div');
                group.className = 'mb-3';

                const label = document.createElement('label');
                label.className = 'form-label fw-medium small text-muted';
                label.innerHTML = el.label + (el.is_required ? ' <span class="text-danger">*</span>' : '');
                group.appendChild(label);

                const inputName = `supplement_forms[${product.id}][${el.id}]`;

                let input;
                switch(el.type) {
                    case 'text':
                    case 'email':
                    case 'number':
                    case 'date':
                        input = document.createElement('input');
                        input.type = el.type;
                        input.className = 'form-control';
                        input.name = inputName;
                        if(el.is_required) input.required = true;
                        break;
                    case 'select':
                        input = document.createElement('select');
                        input.className = 'form-select';
                        input.name = inputName;
                        if(el.is_required) input.required = true;
                        const defOpt = document.createElement('option');
                        defOpt.value = '';
                        defOpt.innerText = 'Selecteer...';
                        input.appendChild(defOpt);
                        if(el.option_value) {
                            el.option_value.split(',').forEach(opt => {
                                const o = document.createElement('option');
                                o.value = opt.trim();
                                o.innerText = opt.trim();
                                input.appendChild(o);
                            });
                        }
                        break;
                    case 'radio':
                    case 'checkbox':
                        input = document.createElement('div');
                        if(el.option_value) {
                            el.option_value.split(',').forEach((opt, idx) => {
                                const div = document.createElement('div');
                                div.className = 'form-check';
                                const c = document.createElement('input');
                                c.type = el.type;
                                c.className = 'form-check-input';
                                c.name = el.type === 'checkbox' ? inputName + '[]' : inputName;
                                c.value = opt.trim();
                                c.id = `field_${product.id}_${el.id}_${idx}`;
                                if(el.is_required && el.type === 'radio') c.required = true;

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

                if(input) group.appendChild(input);
                wrapper.appendChild(group);
            });

            container.appendChild(wrapper);
        }


        // --- CUSTOM POPUP MANAGERS ---

        // 1. FORMS POPUP
        function openFormsPopup() {
            const el = document.getElementById('formsPopup');
            el.style.display = 'flex';
        }
        function closeFormsPopup() {
            const el = document.getElementById('formsPopup');
            el.style.display = 'none';
            // Allow animation to finish before clearing? No need for simple implementation
        }

        // 2. PRODUCT INFO POPUP
        function showProductDetails(id) {
            const product = supplements.find(p => p.id === id);
            if (!product) return;

            document.getElementById('popup-title').innerText = product.name;
            document.getElementById('popup-desc').innerHTML = product.description;

            const imgEl = document.getElementById('popup-img');
            const imgContainer = document.getElementById('popup-img-wrapper');

            if (product.image) {
                imgEl.src = '/files/products/images/' + product.image;
                imgContainer.classList.remove('d-none');
            } else {
                imgContainer.classList.add('d-none');
            }

            const popup = document.getElementById('productPopup');
            popup.style.display = 'flex';
        }

        function closeProductPopup() {
            document.getElementById('productPopup').style.display = 'none';
        }

        // Close on backdrop click
        window.onclick = function(event) {
            const p1 = document.getElementById('productPopup');
            const p2 = document.getElementById('formsPopup');
            if (event.target == p1) closeProductPopup();
            if (event.target == p2) closeFormsPopup();
        }
    </script>
@endsection
