@extends('layouts.app')

@section('content')

    <div class="container py-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-5">
            <h1 class="fw-bold text-dark mb-3 mb-md-0">Winkelwagen</h1>
            <a href="{{ route("shop") }}" class="btn btn-outline-primary rounded-pill">Verder winkelen</a>
        </div>

        <div id="alert-container">
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
        </div>

        <div class="row g-5">
            {{-- Form Section (Left on Desktop, Top on Mobile) --}}
            <div class="col-12 col-lg-7 order-2 order-lg-1">
                <div class="border-0 w-100 bg-white rounded-5 overflow-hidden shadow-sm">
                    <div class="bg-white border-0 pt-4 px-4 pb-0">
                        <h4 class="fw-bold text-primary mb-0">Je gegevens</h4>
                    </div>
                    <div class="p-4">
                        <form class="d-flex flex-column gap-3" action="{{ route('checkout.store') }}" method="POST"
                              id="checkout-form">
                            @csrf
                            @php
                                $fullName = Auth::user()->name ?? '';
                                $nameParts = explode(' ', $fullName, 2);
                                $defaultFirstName = $nameParts[0] ?? '';
                                $defaultLastName = $nameParts[1] ?? '';
                            @endphp

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label for="first_name" class="form-label  d-flex flex-row gap-1 justify-content-center">Voornaam <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" required
                                           value="{{ old('first_name', $defaultFirstName) }}">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="last_name" class="form-label  d-flex flex-row gap-1 justify-content-center">Achternaam <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" required
                                           value="{{ old('last_name', $defaultLastName) }}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="email" class="form-label  d-flex flex-row gap-1 justify-content-center">Email adres <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       value="{{ old('email', Auth::user()->email ?? '') }}">
                            </div>

                            <div class="form-group">
                                <label for="address" class="form-label  d-flex flex-row gap-1 justify-content-center">Straat en huisnummer <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="address" name="address" required
                                       value="{{ old('address', Auth::user()->street ?? '') }}">
                            </div>

                            <div class="row g-3">
                                <div class="col-12 col-md-8">
                                    <label for="city" class="form-label  d-flex flex-row gap-1 justify-content-center">Stad <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="city" name="city" required
                                           value="{{ old('city', Auth::user()->city ?? '') }}">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label for="zipcode" class="form-label  d-flex flex-row gap-1 justify-content-center">Postcode <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="zipcode" name="zipcode" required
                                           value="{{ old('zipcode', Auth::user()->postal_code ?? '') }}">
                                </div>
                            </div>

                            <button class="btn btn-primary btn-lg w-100 rounded-pill py-3 fw-bold shadow-sm mt-4"
                                    type="submit">
                                Afrekenen met Mollie
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Cart Items Section (Right on Desktop, Bottom/Top on Mobile) --}}
            <div class="col-12 col-lg-5 order-1 order-lg-2">
                <div class="border-0 w-100 rounded-5 overflow-hidden bg-white shadow-sm">
                    <div class="border-0 pt-4 px-4 pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="fw-bold text-dark mb-0">Bestelling</h4>
                            <span class="badge bg-primary rounded-pill px-3 py-2" id="cart-item-count">{{ $items->sum('quantity') }} items</span>
                        </div>
                    </div>
                    <div class="p-4">
                        <ul class="list-group mb-4 border-0" id="cart-items-list">
                            @foreach($items as $item)
                                @php
                                    $itemKey = $item->type . '_' . $item->id;
                                @endphp
                                <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-bottom px-0 py-3" id="item-row-{{ $itemKey }}">
                                    <div class="d-flex align-items-center gap-3">
                                        <div
                                            class="rounded-3 bg-light d-flex align-items-center justify-content-center ms-2 overflow-hidden flex-shrink-0"
                                            style="width: 50px; height: 50px;">
                                            @if($item->image && $item->image !== "files/products/images/")
                                                <img src="{{ asset($item->image) }}" alt="{{ $item->name }}"
                                                     style="width: 100%; height: 100%; object-fit: cover;">
                                            @else
                                                <span class="material-symbols-rounded text-muted">shopping_bag</span>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="my-0 fw-bold text-dark text-break">{{ $item->name }}</h6>
                                            @if(isset($item->details))
                                                <small class="text-muted d-block mb-1">
                                                    {{ $item->details }}
                                                </small>
                                            @endif
                                            <div class="d-flex align-items-center gap-2 mt-1">
                                                {{-- JS Controls --}}
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-secondary py-0 px-2 d-flex align-items-center justify-content-center"
                                                        style="width: 24px; height: 24px;"
                                                        onclick="updateCartItem('{{ $item->id }}', '{{ $item->type }}', -1)">
                                                    -
                                                </button>

                                                <span class="mx-2 small fw-bold" id="qty-{{ $itemKey }}">{{ $item->quantity }}</span>

                                                <button type="button"
                                                        class="btn btn-sm btn-outline-secondary py-0 px-2 d-flex align-items-center justify-content-center"
                                                        style="width: 24px; height: 24px;"
                                                        onclick="updateCartItem('{{ $item->id }}', '{{ $item->type }}', 1)">
                                                    +
                                                </button>

                                                <button type="button" class="btn btn-sm text-danger p-0 ms-2"
                                                        title="Verwijderen"
                                                        onclick="removeCartItem('{{ $item->id }}', '{{ $item->type }}')">
                                                    <span class="material-symbols-rounded" style="font-size: 18px;">delete</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="text-dark fw-bold me-2 text-nowrap" id="total-{{ $itemKey }}">€ {{ number_format($item->price * $item->quantity, 2, ',', '.') }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <div class="bg-light rounded-4 p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fs-5 fw-bold text-dark">Totaal</span>
                                <span class="fs-4 fw-bold text-primary" id="grand-total">€ {{ number_format($total, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Frontend Logic for AJAX --}}
    <script>
        // Ensure you have a CSRF token meta tag in your layout or use this fallback
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        async function updateCartItem(id, type, change) {
            // 1. Get current qty
            const itemKey = `${type}_${id}`;
            const qtySpan = document.getElementById(`qty-${itemKey}`);
            let currentQty = parseInt(qtySpan.innerText);
            let newQty = currentQty + change;

            if (newQty < 1) return; // Use remove button for 0

            // Optimistic update (optional, but makes it feel instant)
            qtySpan.innerText = newQty;

            try {
                const response = await fetch('/winkelmandje/api/bewerken', { // Make sure this route exists
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ id, type, quantity: newQty })
                });

                if (!response.ok) {
                    const data = await response.json();
                    if(data.error) {
                        alert(data.error); // Show availability error
                    }
                    // Revert on error
                    qtySpan.innerText = currentQty;
                    return;
                }

                const data = await response.json();
                updateCartUI(data);

            } catch (error) {
                console.error('Error updating cart:', error);
                qtySpan.innerText = currentQty; // Revert
            }
        }

        async function removeCartItem(id, type) {
            if(!confirm('Weet je zeker dat je dit item wilt verwijderen?')) return;

            try {
                const response = await fetch('/winkelmandje/api/verwijderen', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ id, type })
                });

                const data = await response.json();
                updateCartUI(data);

            } catch (error) {
                console.error('Error removing item:', error);
            }
        }

        function updateCartUI(data) {
            if (data.redirect) {
                window.location.href = data.redirect;
                return;
            }

            // Update Item Count
            document.getElementById('cart-item-count').innerText = `${data.item_count} items`;

            // Update Grand Total
            document.getElementById('grand-total').innerText = `€ ${data.total}`;

            // Update Individual Lines
            for (const [key, itemData] of Object.entries(data.items)) {
                // key is like 'product_12' or 'activity_key'
                const qtyEl = document.getElementById(`qty-${key}`);
                const totalEl = document.getElementById(`total-${key}`);

                if (qtyEl) qtyEl.innerText = itemData.quantity;
                if (totalEl) totalEl.innerText = `€ ${itemData.line_total}`;
            }

            // Remove items not in list (if deleted)
            document.querySelectorAll('[id^="item-row-"]').forEach(row => {
                const rowId = row.id.replace('item-row-', '');
                if (!data.items[rowId]) {
                    row.remove();
                }
            });
        }
    </script>
@endsection
