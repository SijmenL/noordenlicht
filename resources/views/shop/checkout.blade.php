@extends('layouts.app')

@section('content')

    <div class="container py-5">
        <div class="d-flex flex-row justify-content-between align-items-center mb-5">
            <h1 class="fw-bold text-dark">Winkelwagen</h1>
            <a href="{{ route("shop") }}" class="btn btn-outline-primary">Verder winkelen</a>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="d-flex flex-row flex-nowrap gap-5">
            {{-- Form Section --}}
            <div class="border-0 w-100 bg-white rounded-5 overflow-hidden">
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

                        <div style="display: flex; gap: 1rem;">
                            <div class="d-flex flex-column w-50">
                                <label for="first_name">Voornaam <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required
                                       value="{{ old('first_name', $defaultFirstName) }}">
                            </div>
                            <div class="d-flex flex-column w-50">
                                <label for="last_name">Achternaam <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required
                                       value="{{ old('last_name', $defaultLastName) }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email adres <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required
                                   value="{{ old('email', Auth::user()->email ?? '') }}">
                        </div>

                        <div class="form-group">
                            <label for="address">Straat en huisnummer <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="address" name="address" required
                                   value="{{ old('address', Auth::user()->address ?? '') }}">
                        </div>

                        <div style="display: flex; gap: 1rem;">
                            <div style="flex: 2;">
                                <label for="city">Stad <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="city" name="city" required
                                       value="{{ old('city', Auth::user()->city ?? '') }}">
                            </div>
                            <div style="flex: 1;">
                                <label for="zipcode">Postcode <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="zipcode" name="zipcode" required
                                       value="{{ old('zipcode', Auth::user()->zipcode ?? '') }}">
                            </div>
                        </div>

                        <button class="btn btn-primary btn-lg w-100 rounded-pill py-3 fw-bold shadow-sm mt-4"
                                type="submit">
                            Afrekenen met Mollie
                        </button>
                    </form>
                </div>
            </div>

            {{-- Cart Items Section --}}
            <div class="border-0 w-100 rounded-5 overflow-hidden">
                <div class="border-0 pt-4 px-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="fw-bold text-dark mb-0">Bestelling</h4>
                        <span class="badge bg-primary rounded-pill px-3 py-2">{{ $items->sum('quantity') }} items</span>
                    </div>
                </div>
                <div class="p-4">
                    <ul class="list-group mb-4 border-0">
                        @foreach($items as $item)
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-bottom px-0 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div
                                        class="rounded-3 bg-light d-flex align-items-center justify-content-center ms-2 overflow-hidden"
                                        style="width: 50px; height: 50px;">
                                        <img src="{{ asset($item->image) }}" alt="{{ $item->name }}"
                                             style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                    <div>
                                        <h6 class="my-0 fw-bold text-dark">{{ $item->name }}</h6>
                                        @if(isset($item->details))
                                            <small class="text-muted d-block mb-1">
                                                {{ $item->details }}
                                            </small>
                                        @endif
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            {{-- Update Quantity Form --}}
                                            <form action="{{ route('cart.update', $item->id) }}" method="POST"
                                                  class="d-flex align-items-center">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $item->id }}">
                                                <input type="hidden" name="type" value="{{ $item->type }}">

                                                <button type="submit" name="quantity" value="{{ $item->quantity - 1 }}"
                                                        class="btn btn-sm btn-outline-secondary py-0 px-2">-
                                                </button>
                                                <span class="mx-2 small">{{ $item->quantity }}</span>
                                                <button type="submit" name="quantity" value="{{ $item->quantity + 1 }}"
                                                        class="btn btn-sm btn-outline-secondary py-0 px-2">+
                                                </button>
                                            </form>

                                            {{-- Remove Form --}}
                                            <form action="{{ route('cart.remove', $item->id) }}" method="POST"
                                                  class="ms-2">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $item->id }}">
                                                <input type="hidden" name="type" value="{{ $item->type }}">
                                                <button type="submit" class="btn btn-sm text-danger p-0"
                                                        title="Verwijderen">
                                                    <span class="material-symbols-rounded" style="font-size: 18px;">delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <span
                                    class="text-dark fw-bold me-2">€ {{ number_format($item->price * $item->quantity, 2, ',', '.') }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="bg-light rounded-4 p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fs-5 fw-bold text-dark">Totaal</span>
                            <span class="fs-4 fw-bold text-primary">€ {{ number_format($total, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
