@extends('layouts.app')

@section('content')
    @if(Session::has('success') && session('success') == "CardAdded")
        <div id="cartConfirm" class="popup" style="margin-top: -92px;">
            <div class="popup-body">
                <div class="page">
                    <h2>Toegevoegd aan winkelwagen!</h2>
                    <p>Je ticket is aan de winkelwagen toegevoegd. Winkel verder of reken af via één van de onderstaande
                        knoppen.</p>
                    <div class="d-grid gap-2">
                        <button id="close" class="btn btn-success">Verder winkelen</button>
                        <a class="btn btn-secondary" href="{{ route("checkout") }}">Bekijk winkelwagen</a>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modalSave = document.getElementById('cartConfirm');
                const btnSaveAll = document.getElementById('close');

                btnSaveAll.addEventListener('click', function () {
                    modalSave.classList.add('d-none');
                });
            });
        </script>
    @endif

    @php
        $categoryNames = [
            '0' => 'Supplementen bij accommodatie',
            '1' => 'Evenement ticket',
            '2' => 'Overnachting',
        ];
    @endphp


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



                        $carrousel_images[] = 'files/products/images/'.$product->image;
                    foreach($product->images as $image) {
                        $carrousel_images[] = 'files/products/carousel/'.$image->image;
                    }


                @endphp

                    <!-- Header Block -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
                    <div>
                        <h1 class="fw-bold">{{ $product->name }}</h1>
                        <h2 class="h4"
                            style="font-style: italic; color: #5a7123;">   {{ $categoryNames[$product->type] ?? 'Overige' }}</h2>
                    </div>

                    <a href="{{ route('shop') }}" class="btn btn-outline-primary mt-3 mt-md-0">Terug naar overzicht</a>


                </div>

                <div class="d-flex flex-column flex-md-row gap-5">
                    <!-- Carousel -->
                    @if($product->image !== null)

                    <div class="w-100">
                        <div class="sticky-top" style="top: 90px;">
                            <x-carousel :images="$carrousel_images"/>
                        </div>
                    </div>
                    @endif

                    <!-- Details & Cart -->
                    <div class="w-100">
                        <div class="shadow-lg bg-white border-0 rounded-5 mb-4 p-4"
                             style="background-image: url('{{ asset('img/logo/doodles/Treewoman2a.webp') }}'); background-repeat: no-repeat; background-size: cover; background-position: center; border: 3px solid #5a7123; width: 100% !important;">

                            <div class="p-0">
                                <div class="mb-3">
                                    @if($hasDiscount)
                                        <span class="badge bg-success fw-bold mb-3 px-3 py-2 rounded-pill">{{ $totalPercentageDiscounts }}% korting!</span>
                                    @endif

                                    <div class="mb-3">
                                        @if($hasDiscount)
                                            <p class="text-muted mb-0"
                                               style="text-decoration: line-through; opacity: 0.7; font-size: 1.1rem;">
                                                Normale prijs:
                                                &#8364;{{ number_format($preDiscountPrice, 2, ',', '.') }}
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

                {{-- Supplements Section (Only for Overnachtingen) --}}
                @if($product->type == '2' && isset($supplements) && $supplements->count() > 0)
                    <div class="mt-5 pt-5 border-top border-secondary-subtle">
                        <div class="d-flex align-items-center mb-4 gap-3">
                            <span class="material-symbols-rounded">hotel_class</span>
                            <h3 class="fw-bold m-0 h2">Maak je verblijf compleet</h3>
                        </div>

                        <div class="d-flex flex-row-responsive gap-4 justify-content-center">
                            @foreach ($supplements as $supplement)
                                <a href="{{ route('shop.details', $supplement->id) }}" class="text-decoration-none" style="min-width: 200px;">
                                    <div
                                        class="shop-tile h-100 d-flex flex-column bg-white overflow-hidden position-relative">
                                        @if($supplement->image !== null)
                                            {{-- Image Section --}}
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
                                            </div>
                                        </div>
                                    </div>
                                </a>
                        @endforeach
                        </div>
                    </div>
        </div>
        @endif
        @endif
    </div>

    {{-- Styles for the Supplements Grid (Copied from List View) --}}
    <style>
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
