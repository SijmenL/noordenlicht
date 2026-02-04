@extends('layouts.app')

@section('content')
    <div class="container my-5">
        <div class="shadow-sm w-100 border-0">
            <div class=" text-center p-5">
                <span class="material-symbols-rounded text-success" style="font-size: 64px;">check_circle</span>
                @if($order->created_at->diffInSeconds($order->updated_at) > 60)
                    <h1 class="mt-3">Bedankt voor je nabestelling!</h1>
                @else
                    <h1 class="mt-3">Bedankt voor je bestelling!</h1>
                @endif

                @if($order->created_at->diffInSeconds($order->updated_at) > 60)
                    <p class="lead text-muted">De nabestelling van #{{ $order->order_number }} is succesvol afgerond. We hebben de producten toegevoegd aan je bestelling.</p>
                @endif
                <hr class="my-4">

                <div class="row text-start justify-content-center">
                    <div class="col-md-8">
                        <h4>Overzicht</h4>
                        <ul class="list-group mb-4">

                            @foreach($order->items as $item)
                                <li class="list-group-item justify-content-between align-items-center">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <div>
                                            <span class="fw-bold">{{ $item->product_name }}</span>
                                            <div class="small text-muted">Aantal: {{ $item->quantity }}</div>
                                        </div>
                                        <span class="fw-bold">&euro;{{ number_format($item->total_price, 2, ',', '.') }}</span>
                                    </div>

                                    {{-- Expanded Price Details --}}
                                    <div class="mt-2 ps-2 border-start border-3" style="font-size: 0.85rem;">
                                        @php
                                            $meta = $item->price_metadata ?? [];
                                            $hasDiscount = $item->unit_discount_amount > 0 || $item->unit_discount_percentage > 0;

                                            // Normal Price Calculation (Base + Full VAT + Extras)
                                            $basePrice = $item->unit_base_price;
                                            $normalVat = 0;
                                            if (!empty($meta['additions'])) {
                                                foreach($meta['additions'] as $add) {
                                                    // VAT on full base price
                                                    $normalVat += $basePrice * ($add['amount'] / 100);
                                                }
                                            } else {
                                                $normalVat = $item->unit_vat;
                                            }

                                            $normalExtras = 0;
                                            if(!empty($meta['extras'])) {
                                                foreach($meta['extras'] as $ex) {
                                                    $normalExtras += $ex['amount'];
                                                }
                                            } elseif ($item->unit_extra > 0) {
                                                $normalExtras = $item->unit_extra;
                                            }

                                            $normalPrice = $basePrice + $normalVat + $normalExtras;
                                        @endphp

                                        @if($item->unit_base_price > 0)
                                            <div class="mb-1 text-muted" style="font-size: 0.8em;">
                                                <span class="me-3">Basis excl. btw: &euro; {{ number_format($item->unit_base_price, 2, ',', '.') }}</span>
                                                {{-- If discounted, unit_vat is lower than normal VAT --}}
                                                <span>Basis incl. btw: &euro; {{ number_format($item->unit_base_price + $item->unit_vat, 2, ',', '.') }}</span>
                                            </div>
                                        @endif

                                        @if($hasDiscount)
                                            <div>
                                                @if($item->unit_discount_percentage > 0)
                                                    <span class="badge bg-success rounded-pill" style="font-size: 0.7em">{{ $item->unit_discount_percentage }}% Korting</span>
                                                @endif
                                            </div>
                                            <div class="text-muted text-decoration-line-through">
                                                Normaal: &euro; {{ number_format($normalPrice, 2, ',', '.') }}
                                            </div>
                                        @endif

                                        {{-- Type 1: Additions (VAT) --}}
                                        @if(!empty($meta['additions']))
                                            <div class="text-muted fst-italic">
                                                (incl.
                                                @foreach($meta['additions'] as $add)
                                                    {{-- Display the ACTUAL VAT amount included in final price --}}
                                                    {{ $add['name'] }} {{ $add['amount'] }}% - &euro; {{ number_format($add['calculated_amount'], 2, ',', '.') }}@if(!$loop->last), @endif
                                                @endforeach
                                                )
                                            </div>
                                        @elseif($item->unit_vat > 0)
                                            <div class="text-muted fst-italic">(incl. toeslagen &euro; {{ number_format($item->unit_vat, 2, ',', '.') }})</div>
                                        @endif

                                        {{-- Type 3: Extra Costs --}}
                                        @if(!empty($meta['extras']))
                                            <div class="text-muted fst-italic">
                                                (excl.
                                                @foreach($meta['extras'] as $ex)
                                                    {{ $ex['name'] }} &euro; {{ number_format($ex['amount'], 2, ',', '.') }}@if(!$loop->last), @endif
                                                @endforeach
                                                )
                                            </div>
                                        @elseif($item->unit_extra > 0)
                                            <div class="text-muted fst-italic">(excl. extra kosten &euro; {{ number_format($item->unit_extra, 2, ',', '.') }})</div>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-light fw-bold">
                                <span>Totaal</span>
                                <span>&euro;{{ number_format($order->total_amount, 2) }}</span>
                            </li>
                        </ul>

                        <h4 class="mb-3">Factuur</h4>
                        <div class="d-grid gap-2 mb-4">
                            <a href="{{ route('order.invoice', $order->order_number) }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center">
                                <span class="material-symbols-rounded me-2">receipt_long</span>
                                Download Factuur (PDF)
                            </a>
                        </div>

                        @if($order->tickets->count() > 0)
                            <h4 class="mb-3">Jouw Tickets</h4>
                            <div class="d-grid gap-2">
                                @foreach($order->tickets as $ticket)
                                    <a href="{{ route('ticket.download', $ticket->uuid) }}" class="btn btn-outline-primary d-flex align-items-center justify-content-center">
                                        <span class="material-symbols-rounded me-2">confirmation_number</span>
                                        Download Ticket: {{ $ticket->activity->title }} ({{ \Carbon\Carbon::parse($ticket->start_date)->format('d-m-Y') }})
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-5">
                    <a href="{{ route('home') }}" class="btn btn-primary">Terug naar Home</a>
                </div>
            </div>
        </div>
    </div>
@endsection
