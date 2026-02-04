@extends('layouts.app')

@section('content')
    <div class="rounded-bottom-5 bg-white d-flex flex-column container-block"
         style="position: relative; margin-top: -25px; background-position: unset !important; background-image: url('{{ asset('img/logo/doodles/Blad Buizerd.webp') }}'); background-repeat: repeat;">

        <div class="container py-5">
            <div class="d-flex flex-column align-items-center justify-content-center mb-5 gap-3 text-center">
                <div style="backdrop-filter: blur(2px);">
                    <h1 class="fw-bold display-5">Prijslijst</h1>
                    <h2 class="">Een duidelijk overzicht van al onze tarieven</h2>
                </div>

                <a href="{{ route('prices.download') }}" class="btn btn-primary rounded-pill px-4 shadow-sm d-flex align-items-center gap-2">
                    <span class="material-symbols-rounded">download</span>
                    Download als PDF
                </a>
            </div>


            <div class="d-flex flex-column gap-4">
                {{-- 1. Accommodaties --}}
                <div class="col-12">
                    <div class="border-0 rounded-5 overflow-hidden">
                        <div class="bg-light p-3">
                            <h3 class="fw-bold m-0 d-flex align-items-center gap-2">
                                <span class="material-symbols-rounded">cottage</span>
                                Accommodaties
                            </h3>
                        </div>
                        <div class="-body bg-white p-0">
                            <div class="table-responsive">
                                <div class="-body p-4">
                                    @if($accommodaties->isNotEmpty())
                                        <div class="">
                                                <div class="">
                                                    <div class="bg-white rounded-4 shadow-sm h-100 overflow-hidden border border-light">
                                                        <div class="p-3 border-bottom bg-white">
                                                            <h5 class="fw-bold text-primary m-0">Accommodaties</h5>
                                                        </div>
                                                        <table class="table table-sm table-hover mb-0">
                                                            <tbody>
                                                            @foreach($accommodaties as $accommodatie)
                                                                @php
                                                                    // --- Price Calculation Logic (Sync with Details) ---
                                                                    $allPrices = $accommodatie->prices->map(fn($p) => $p->price);

                                                                    $basePrices = $allPrices->where('type', 0);
                                                                    $percentageAdditions = $allPrices->where('type', 1);
                                                                    $fixedDiscounts = $allPrices->where('type', 2);
                                                                    $extraCosts = $allPrices->where('type', 3);
                                                                    $percentageDiscounts = $allPrices->where('type', 4);

                                                                    $totalBasePrice = $basePrices->sum('amount');

                                                                    // --- Pre-Discount Price (Base + VAT on Base) ---
                                                                    // Calculate VAT over the original base price for the "Normal Price" display
                                                                    $preDiscountVatAmount = 0;
                                                                    foreach ($percentageAdditions as $percentage) {
                                                                        $preDiscountVatAmount += $totalBasePrice * ($percentage->amount / 100);
                                                                    }
                                                                    $preDiscountPrice = $totalBasePrice + $preDiscountVatAmount;

                                                                    // --- Actual Price Calculation ---
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
                                                                    foreach ($percentageAdditions as $percentage) {
                                                                        $totalVatAmount += $taxableAmount * ($percentage->amount / 100);
                                                                    }

                                                                    $priceInclVat = $taxableAmount + $totalVatAmount;
                                                                    $calculatedPrice = $priceInclVat;

                                                                    $hasDiscount = $fixedDiscounts->isNotEmpty() || $percentageDiscounts->isNotEmpty();
                                                                @endphp
                                                                <tr>
                                                                    <td class="ps-3 py-2 border-0 align-middle"><div>{{ $accommodatie->name }}</div><small> {{$accommodatie->type}}</small></td>
                                                                    <td class="pe-3 py-2 text-end border-0" style="min-width: 150px;">
                                                                        @if($hasDiscount)
                                                                            <div class="mb-1"><span class="badge bg-success rounded-pill" style="font-size: 0.7rem;">@if($totalPercentageDiscounts > 0)
                                                                                        {{ $totalPercentageDiscounts }}%
                                                                                    @endif

                                                                                    @if($totalPercentageDiscounts > 0 && $fixedDiscounts->sum('amount') > 0) én @endif

                                                                                    @if($fixedDiscounts->sum('amount') > 0)
                                                                                        -€{{ $fixedDiscounts->sum('amount') }}
                                                                                    @endif
                                korting!</span></div>
                                                                            <small class="text-decoration-line-through text-muted me-1">€ {{ number_format($preDiscountPrice, 2, ',', '.') }}</small>
                                                                            <span class="fw-bold text-success">€ {{ number_format($calculatedPrice, 2, ',', '.') }}</span>
                                                                        @else
                                                                            <span class="fw-bold text-dark">€ {{ number_format($calculatedPrice, 2, ',', '.') }}</span>
                                                                        @endif

                                                                        {{-- Incl/Excl Details Small --}}
                                                                        @if($percentageAdditions->isNotEmpty() || $extraCosts->isNotEmpty())
                                                                            <div class="lh-1 mt-1">
                                                                                @if($percentageAdditions->isNotEmpty())
                                                                                    <small class="d-block text-muted" style="font-size: 10px;">
                                                                                        (incl. @foreach($percentageAdditions as $c) {{ $c->name }} {{ $c->amount }}% @endforeach)
                                                                                    </small>
                                                                                @endif
                                                                                @if($extraCosts->isNotEmpty())
                                                                                    <small class="d-block text-muted" style="font-size: 10px;">
                                                                                        (excl. @foreach($extraCosts as $c) {{ $c->name }} € {{ number_format($c->amount, 2) }} @endforeach)
                                                                                    </small>
                                                                                @endif
                                                                            </div>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                        </div>
                                    @else
                                        <p class="text-muted text-center m-0">Geen accommodaties gevonden.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Producten & Diensten --}}
                <div class="col-12">
                    <div class="border-0 rounded-5 overflow-hidden">
                        <div class="bg-light p-3">
                            <h3 class="fw-bold m-0 d-flex align-items-center gap-2">
                                <span class="material-symbols-rounded">inventory_2</span>
                                Producten &amp; Diensten
                            </h3>
                        </div>
                        <div class="-body bg-white p-4">
                            @if($products->isNotEmpty())
                                <div class="row g-4">
                                    @foreach($products as $categoryName => $items)
                                        <div class="col-md-6">
                                            <div class="bg-white rounded-4 shadow-sm h-100 overflow-hidden border border-light">
                                                <div class="p-3 border-bottom bg-white">
                                                    <h5 class="fw-bold text-primary m-0">{{ $categoryName }}</h5>
                                                </div>
                                                <table class="table table-sm table-hover mb-0">
                                                    <tbody>
                                                    @foreach($items as $product)
                                                        @php
                                                            // --- Price Calculation Logic (Sync with Details) ---
                                                            $allPrices = $product->prices->map(fn($p) => $p->price);

                                                            $basePrices = $allPrices->where('type', 0);
                                                            $percentageAdditions = $allPrices->where('type', 1);
                                                            $fixedDiscounts = $allPrices->where('type', 2);
                                                            $extraCosts = $allPrices->where('type', 3);
                                                            $percentageDiscounts = $allPrices->where('type', 4);

                                                            $totalBasePrice = $basePrices->sum('amount');

                                                            // --- Pre-Discount Price (Base + VAT on Base) ---
                                                            // Calculate VAT over the original base price for the "Normal Price" display
                                                            $preDiscountVatAmount = 0;
                                                            foreach ($percentageAdditions as $percentage) {
                                                                $preDiscountVatAmount += $totalBasePrice * ($percentage->amount / 100);
                                                            }
                                                            $preDiscountPrice = $totalBasePrice + $preDiscountVatAmount;

                                                            // --- Actual Price Calculation ---
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
                                                            foreach ($percentageAdditions as $percentage) {
                                                                $totalVatAmount += $taxableAmount * ($percentage->amount / 100);
                                                            }

                                                            $priceInclVat = $taxableAmount + $totalVatAmount;
                                                            $calculatedPrice = $priceInclVat;

                                                            $hasDiscount = $fixedDiscounts->isNotEmpty() || $percentageDiscounts->isNotEmpty();
                                                        @endphp
                                                        <tr>
                                                            <td class="ps-3 py-2 border-0 align-middle">{{ $product->name }}</td>
                                                            <td class="pe-3 py-2 text-end border-0" style="min-width: 150px;">
                                                                @if($hasDiscount)
                                                                    <div class="mb-1"><span class="badge bg-success rounded-pill" style="font-size: 0.7rem;">@if($totalPercentageDiscounts > 0)
                                                                                {{ $totalPercentageDiscounts }}%
                                                                            @endif

                                                                            @if($totalPercentageDiscounts > 0 && $fixedDiscounts->sum('amount') > 0) én @endif

                                                                            @if($fixedDiscounts->sum('amount') > 0)
                                                                                -€{{ $fixedDiscounts->sum('amount') }}
                                                                            @endif
                                korting!</span></div>
                                                                    <small class="text-decoration-line-through text-muted me-1">€ {{ number_format($preDiscountPrice, 2, ',', '.') }}</small>
                                                                    <span class="fw-bold text-success">€ {{ number_format($calculatedPrice, 2, ',', '.') }}</span>
                                                                @else
                                                                    <span class="fw-bold text-dark">€ {{ number_format($calculatedPrice, 2, ',', '.') }}</span>
                                                                @endif

                                                                {{-- Incl/Excl Details Small --}}
                                                                @if($percentageAdditions->isNotEmpty() || $extraCosts->isNotEmpty())
                                                                    <div class="lh-1 mt-1">
                                                                        @if($percentageAdditions->isNotEmpty())
                                                                            <small class="d-block text-muted" style="font-size: 10px;">
                                                                                (incl. @foreach($percentageAdditions as $c) {{ $c->name }} {{ $c->amount }}% @endforeach)
                                                                            </small>
                                                                        @endif
                                                                        @if($extraCosts->isNotEmpty())
                                                                            <small class="d-block text-muted" style="font-size: 10px;">
                                                                                (excl. @foreach($extraCosts as $c) {{ $c->name }} € {{ number_format($c->amount, 2) }} @endforeach)
                                                                            </small>
                                                                        @endif
                                                                    </div>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted text-center m-0">Geen producten gevonden.</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- 3. Events / Activiteiten --}}
                <div class="col-12">
                    <div class="border-0 rounded-5 overflow-hidden">
                        <div class="bg-light p-3">
                            <h3 class="fw-bold m-0 d-flex align-items-center gap-2">
                                <span class="material-symbols-rounded">event</span>
                                Events
                            </h3>
                        </div>
                        <div class="-body p-0">
                            @if($activities->isNotEmpty())
                                <div class="row g-4">
                                    <div class="">
                                        <div class="bg-white rounded-4 shadow-sm h-100 overflow-hidden border border-light">
                                            <div class="p-3 border-bottom bg-white">
                                                <h5 class="fw-bold text-primary m-0">Accommodaties</h5>
                                            </div>
                                            <table class="table table-sm table-hover mb-0">
                                                <tbody>
                                                @foreach($activities as $activity)
                                                    @php
                                                        // --- Price Calculation Logic (Sync with Details) ---
                                                        $allPrices = $activity->prices->map(fn($p) => $p->price);

                                                        $basePrices = $allPrices->where('type', 0);
                                                        $percentageAdditions = $allPrices->where('type', 1);
                                                        $fixedDiscounts = $allPrices->where('type', 2);
                                                        $extraCosts = $allPrices->where('type', 3);
                                                        $percentageDiscounts = $allPrices->where('type', 4);

                                                        $totalBasePrice = $basePrices->sum('amount');

                                                        // --- Pre-Discount Price (Base + VAT on Base) ---
                                                        // Calculate VAT over the original base price for the "Normal Price" display
                                                        $preDiscountVatAmount = 0;
                                                        foreach ($percentageAdditions as $percentage) {
                                                            $preDiscountVatAmount += $totalBasePrice * ($percentage->amount / 100);
                                                        }
                                                        $preDiscountPrice = $totalBasePrice + $preDiscountVatAmount;

                                                        // --- Actual Price Calculation ---
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
                                                        foreach ($percentageAdditions as $percentage) {
                                                            $totalVatAmount += $taxableAmount * ($percentage->amount / 100);
                                                        }

                                                        $priceInclVat = $taxableAmount + $totalVatAmount;
                                                        $calculatedPrice = $priceInclVat;

                                                        $hasDiscount = $fixedDiscounts->isNotEmpty() || $percentageDiscounts->isNotEmpty();
                                                    @endphp
                                                    <tr>
                                                        <td class="ps-3 py-2 border-0 align-middle"><div>{{ $activity->title }}</div></td>
                                                        <td class="pe-3 py-2 text-end border-0" style="min-width: 150px;">
                                                            @if($hasDiscount)
                                                                <div class="mb-1"><span class="badge bg-success rounded-pill" style="font-size: 0.7rem;">@if($totalPercentageDiscounts > 0)
                                                                            {{ $totalPercentageDiscounts }}%
                                                                        @endif

                                                                        @if($totalPercentageDiscounts > 0 && $fixedDiscounts->sum('amount') > 0) én @endif

                                                                        @if($fixedDiscounts->sum('amount') > 0)
                                                                            -€{{ $fixedDiscounts->sum('amount') }}
                                                                        @endif
                                korting!</span></div>
                                                                <small class="text-decoration-line-through text-muted me-1">€ {{ number_format($preDiscountPrice, 2, ',', '.') }}</small>
                                                                <span class="fw-bold text-success">€ {{ number_format($calculatedPrice, 2, ',', '.') }}</span>
                                                            @else
                                                                <span class="fw-bold text-dark">€ {{ number_format($calculatedPrice, 2, ',', '.') }}</span>
                                                            @endif

                                                            {{-- Incl/Excl Details Small --}}
                                                            @if($percentageAdditions->isNotEmpty() || $extraCosts->isNotEmpty())
                                                                <div class="lh-1 mt-1">
                                                                    @if($percentageAdditions->isNotEmpty())
                                                                        <small class="d-block text-muted" style="font-size: 10px;">
                                                                            (incl. @foreach($percentageAdditions as $c) {{ $c->name }} {{ $c->amount }}% @endforeach)
                                                                        </small>
                                                                    @endif
                                                                    @if($extraCosts->isNotEmpty())
                                                                        <small class="d-block text-muted" style="font-size: 10px;">
                                                                            (excl. @foreach($extraCosts as $c) {{ $c->name }} € {{ number_format($c->amount, 2) }} @endforeach)
                                                                        </small>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <p class="text-muted text-center m-0">Geen events gevonden.</p>
                            @endif
                        </div>
                        </div>
                    </div>

                {{-- 4. Maatwerk Section --}}
                <div class="col-12">
                    <div class="border-0 rounded-5 overflow-hidden">
                        <div class="bg-light p-3">
                            <h3 class="fw-bold m-0 d-flex align-items-center gap-2">
                                <span class="material-symbols-rounded">handshake</span>
                                Maatwerk
                            </h3>
                        </div>
                        <div class="-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <tbody>
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark">Speciale wensen of combinaties?</td>
                                        <td class="text-muted">Wij denken graag met u mee voor een passend aanbod.</td>
                                        <td class="pe-4 text-end">
                                            <span class="fst-italic fw-bold text-primary">In overleg</span>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
