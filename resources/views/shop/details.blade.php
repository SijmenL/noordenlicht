@extends('layouts.app')

@section('content')
    <div class="rounded-bottom-5 bg-light container-block pb-5"
         style="position: relative; margin-top: 0 !important; z-index: 10; background-image: url('{{ asset('img/logo/doodles/Blad StretchA_white.webp') }}'); background-repeat: repeat; background-size: cover;">
        <div class="container">
            @if($product !== null)
                @php
                    // --- Price Calculation Logic (UNCHANGED) ---
                    $allPrices = $product->prices->map(fn($p) => $p->price);

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


                                        $carrousel_images = [];
                    foreach($product->images as $image) {
                        $carrousel_images[] = 'files/products/carousel/'.$image->image;
                    }
                    if(empty($carrousel_images)) {
                        $carrousel_images[] = 'files/products/images/'.$product->image;
                    }
                @endphp

                    <!-- Header Block -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
                    <div>
                        <h1 class="fw-bold">{{ $product->name }}</h1>
                        <h2 class="h4" style="font-style: italic; color: #5a7123;">{{ $product->type }}</h2>
                    </div>
                    <a href="{{ route('shop') }}" class="btn btn-outline-primary mt-3 mt-md-0">
                        <i class="bi bi-arrow-left me-1"></i> Terug naar overzicht
                    </a>
                </div>

                <div class="d-flex flex-column flex-md-row gap-5">
                    <!-- Carousel -->
                    <div class="w-100">
                        <div class="sticky-top" style="top: 90px;">
                            <x-carousel :images="$carrousel_images"/>
                        </div>
                    </div>

                    <!-- Details & Cart -->
                    <div class="w-100">
                        <div class="card shadow-lg border-0 rounded-5 mb-4 p-4"
                             style="background-image: url('{{ asset('img/logo/doodles/Treewoman2a.webp') }}'); background-repeat: no-repeat; background-size: cover; background-position: center; border: 3px solid #5a7123; width: 100%;">

                            <div class="card-body p-0">
                                <div class="mb-3">
                                    @if($hasDiscount)
                                        <span class="badge bg-success fw-bold mb-3 px-3 py-2 rounded-pill">{{ $totalPercentageDiscounts }}% korting!</span>
                                    @endif

                                    <div class="mb-3">
                                        @if($hasDiscount)
                                            <p class="text-muted mb-0"
                                               style="text-decoration: line-through; opacity: 0.7; font-size: 1.1rem;">
                                                Normale prijs: &#8364;{{ number_format($preDiscountPrice, 2, ',', '.') }}
                                            </p>
                                        @endif

                                        <h2 class="fw-bold text-primary mb-0">
                                            &#8364;{{ number_format($calculatedPrice, 2, ',', '.') }}
                                        </h2>
                                        <p class="text-muted small fw-normal">per stuk</p>
                                    </div>

                                    <!-- Cost Details -->
                                    @if($totalPercentageAdditions !== 0)
                                        <p class="text-dark small mb-0 mt-1">
                                            (incl.
                                            @foreach($percentageAdditions as $index => $cost)
                                                {{ $cost->name }} {{ $cost->amount }}% -
                                                &#8364;{{ number_format($totalBasePrice * ($cost->amount / 100), 2) }}@if(!$loop->last)
                                                    ,
                                                @endif
                                            @endforeach)
                                        </p>
                                    @endif

                                    @if($extraCosts->isNotEmpty())
                                        <p class="text-dark small mb-0 mt-1">
                                            (excl.
                                            @foreach($extraCosts as $index => $cost)
                                                {{ $cost->name }}
                                                &#8364;{{ number_format($cost->amount, 2, ',', '.') }}@if(!$loop->last)
                                                    ,
                                                @endif
                                            @endforeach)
                                        </p>
                                    @endif

                                </div>

                                <div class="d-grid gap-3 mt-4">
                                    <form action="{{ route('cart.add', $product->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow w-100">
                                            In winkelmandje
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="p-3">
                            <h3 class="h5 fw-bold mb-3 text-secondary">Beschrijving</h3>
                            {!! $product->description !!}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
