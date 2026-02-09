@extends('layouts.app')

@section('content')
    <div class="rounded-bottom-5 bg-light container-block pb-5"
         style="position: relative; margin-top: 0 !important; background-image: url('{{ asset('img/logo/doodles/Blad StretchA_white.webp') }}'); background-repeat: repeat; background-size: cover;">
        <div class="container">
            @if($accommodatie !== null)
                @php
                    $allPrices = $accommodatie->prices->map(fn($p) => $p->price);

                    $basePrices = $allPrices->where('type', 0);
                    $percentageAdditions = $allPrices->where('type', 1); // VAT
                    $fixedDiscounts = $allPrices->where('type', 2);
                    $extraCosts = $allPrices->where('type', 3);
                    $percentageDiscounts = $allPrices->where('type', 4);

                    $totalBasePrice = $basePrices->sum('amount');


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


                    $preDiscountVatAmount = 0;
                    foreach ($percentageAdditions as $percentage) {
                        $preDiscountVatAmount += $totalBasePrice * ($percentage->amount / 100);
                    }
                    $preDiscountPrice = $totalBasePrice + $preDiscountVatAmount;


                    $carrousel_images = [];
                    foreach($accommodatie->images as $image) {
                        $carrousel_images[] = asset('/files/accommodaties/images/'.$accommodatie->image);
                        $carrousel_images[] = '/files/accommodaties/carousel/'.$image->image;
                    }
                @endphp

                    <!-- Header Block -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
                    <div>
                        <h1 class="fw-bold">{{ $accommodatie->name }}</h1>
                        <h2 class="h4" style="font-style: italic; color: #5a7123;">{{ $accommodatie->type }}</h2>
                    </div>
                    <a href=" {{ route('accommodaties') }}" class="btn btn-outline-primary mt-3 mt-md-0">
                        <i class="bi bi-arrow-left me-1"></i> Bekijk alle accommodaties
                    </a>
                </div>


                <div class="d-flex flex-row-responsive gap-5">
                    <!-- Left column (carousel) -->
                    <div class="w-100" style="max-width: 500px">
                        <div class="sticky-top" style="top: 120px;"> <!-- adjust for navbar height -->
                            <x-carousel :images="$carrousel_images"/>
                        </div>
                    </div>


                    <!-- Description, Price, and Booking Column (Right on desktop) -->
                    <div class="w-100">
                        <!-- Price and Booking Card -->
                        <div class="card shadow-lg border-0 rounded-5 mb-4 p-4"
                             style="background-image: url('{{ asset('img/logo/doodles/Treewoman2a.webp') }}'); background-repeat: no-repeat; background-size: cover; background-position: center; border: 3px solid #5a7123; width: 100%; max-width: 100%">

                            <div class="card-body p-0">
                                @if($hasDiscount)
                                    <span class="badge bg-success fw-bold mb-3 px-3 py-2 rounded-pill">
                                @if($totalPercentageDiscounts > 0)
                                            {{ $totalPercentageDiscounts }}%
                                        @endif

                                        @if($totalPercentageDiscounts > 0 && $fixedDiscounts->sum('amount') > 0)
                                            én
                                        @endif

                                        @if($fixedDiscounts->sum('amount') > 0)
                                            -€{{ $fixedDiscounts->sum('amount') }}
                                        @endif
                                korting!
                            </span>
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
                                    <p class="text-muted small fw-normal">per uur (incl. btw)</p>
                                </div>

                                <!-- Cost Details -->
                                @if($percentageAdditions->isNotEmpty())
                                    <p class="text-dark small mb-0 mt-1">
                                        (incl.
                                        @foreach($percentageAdditions as $index => $cost)
                                            {{ $cost->name }} {{ $cost->amount }}% -
                                            &#8364;{{ number_format($taxableAmount * ($cost->amount / 100), 2, ',', '.') }}@if(!$loop->last)
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

                                <!-- Booking Actions -->
                                <div class="d-grid gap-3 mt-4">
                                    @guest()
                                        <div class="alert alert-info">Meldt je bij de eerste keer aan middels het
                                            aanvraag formulier. Je ontvangt een reactie of je praktijk
                                            resoneert bij NoordenLicht. Na goedkeuring kun je via deze pagina de
                                            gewenste ruimte en datum reserveren en met Ideal de reservering definitief
                                            maken.
                                        </div>
                                        <a href="{{ route('accommodaties.form') }}"
                                           class="btn btn-primary btn-lg rounded-pill shadow">Vul aanvraagformulier
                                            in</a>
                                    @else
                                        @if($user->allow_booking == 1)
                                            <a href="{{ route('accommodatie.book', $accommodatie->id) }}"
                                               class="btn btn-primary btn-lg rounded-pill shadow">Nu Boeken</a>
                                        @else
                                            <div class="alert alert-info">Je boekingsaanvraag is nog niet geaccepteerd.
                                                We komen
                                                zo snel mogelijk bij je terug.
                                            </div>
                                            <button disabled
                                                    class="btn btn-primary btn-lg rounded-pill shadow">Je kunt nog geen
                                                boekingen maken
                                            </button>
                                        @endif
                                    @endguest
                                    <a href="{{ route('home.rules') }}" class="btn btn-outline-primary rounded-pill">Bekijk
                                        Huisregels</a>
                                </div>
                            </div>
                        </div>

                        <!-- Description Block -->
                        <div class="p-3">
                            <h3 class="h5 fw-bold mb-3 text-secondary">Beschrijving</h3>
                            {!! $accommodatie->description !!}
                        </div>
                    </div>
                </div>

                <!-- Facilities/Amenities Block -->
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="bg-white shadow-lg rounded-5 p-5 border">
                            <h2 class="fw-bold mb-4 text-center text-primary">Voorzieningen</h2>
                            <div class="d-flex flex-wrap justify-content-center gap-2 mb-2">
                                @foreach($accommodatie->icons as $icon)
                                    <div
                                        class="d-flex align-items-center bg-light border border-white rounded-pill px-3 py-2 shadow-sm"
                                        title="{{ $icon->text }}">
                                        <div class="icon-pill-svg me-2" style="height: 22px; width: 22px;">
                                            {!! file_get_contents(public_path('files/accommodaties/icons/'.$icon->icon)) !!}
                                        </div>
                                        <span class="small fw-semibold text-muted"
                                              style="font-size: 0.85rem;">{{ $icon->text }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <style>
                                .icon-pill-svg svg {
                                    width: 100%;
                                    height: 100%;
                                    object-fit: contain;
                                    fill: #5a7123;
                                }

                                .text-primary {
                                    color: #5a7123 !important;
                                }

                                .btn-primary {
                                    background-color: #5a7123;
                                    border-color: #5a7123;
                                }

                                .btn-outline-primary {
                                    color: #5a7123;
                                    border-color: #5a7123;
                                }

                                .btn-outline-primary:hover {
                                    background-color: #5a7123;
                                    border-color: #5a7123;
                                }
                            </style>
                        </div>
                    </div>
                </div>

            @else
                <div class="alert alert-warning d-flex align-items-center rounded-4 p-4" role="alert">
                    <span class="material-symbols-rounded me-2">home</span>
                    Geen accommodatie gevonden. Controleer de link of ga terug naar het overzicht.
                </div>
            @endif
        </div>
    </div>
@endsection
