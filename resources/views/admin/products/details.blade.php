@extends('layouts.dashboard')

@section('content')

    <div class="container col-md-11">
        <h1>Details @if($product !== null)
                {{$product->name}}
            @endif</h1>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item" aria-current="page"><a href="{{route('admin')}}">Dashboard</a></li>
                <li class="breadcrumb-item" aria-current="page"><a href="{{route('admin.products')}}">Producten</a></li>
                <li class="breadcrumb-item active" aria-current="page">Details {{$product->name}} </li>
            </ol>
        </nav>

        @if(Session::has('error'))
            <div class="alert alert-danger" role="alert">
                {{ session('error') }}
            </div>
        @endif

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
            <div class="d-flex flex-row-responsive justify-content-center align-items-start gap-5">
                {{-- Carousel Section --}}
                <div class="w-100" style="max-width: 600px;">
                    @if(count($carrousel_images) > 0)
                        <x-carousel :images="$carrousel_images"/>
                    @else
                        <div class="alert alert-secondary">Geen afbeeldingen beschikbaar</div>
                        @if($product->image)
                            {{-- Fallback to main image if no carousel images exist --}}
                            <img src="{{ asset('files/products/images/' . $product->image) }}" class="img-fluid rounded"
                                 alt="{{ $product->name }}">
                        @endif
                    @endif
                </div>

                {{-- Details Section --}}
                <div class="w-100">
                    <h1>{{ $product->name }}</h1>
                    <h2 class="text-muted h5" style="font-style: italic">{{ $productTypeLabel }}</h2>

                    <div class="p-3 d-flex flex-column align-items-start border rounded bg-light mt-3">
                        @if($hasDiscount)
                            <p class="badge bg-success mb-2">
                                @if($totalPercentageDiscounts > 0)
                                    {{ $totalPercentageDiscounts }}%
                                @endif
                                @if($fixedDiscounts->sum('amount') > 0)
                                    -€{{ $fixedDiscounts->sum('amount') }}
                                @endif
                                korting!
                            </p>

                            <div class="d-flex flex-row gap-2 align-items-baseline">
                                <h3 class="d-inline-block text-muted"
                                    style="text-decoration: line-through; opacity: 0.6; font-size: 1.2rem;">
                                    &#8364;{{ number_format($preDiscountPrice, 2, ',', '.') }}
                                </h3>
                                @else
                                    <div class="">
                                        @endif

                                        <h3 class="d-inline-block fw-bold text-primary">
                                            &#8364;{{ number_format($calculatedPrice, 2, ',', '.') }}
                                        </h3>
                                    </div>

                                    {{-- Price Breakdown --}}
                                    @if($totalPercentageAdditions !== 0)
                                        <p class="text-muted small mb-0 mt-1">
                                            (incl.
                                            @foreach($percentageAdditions as $index => $cost)
                                                {{ $cost->name }} {{ $cost->amount }}%
                                                (@if($cost->amount > 0)
                                                    +
                                                @endif
                                                &#8364;{{ number_format($totalBasePrice * ($cost->amount / 100), 2) }}
                                                )@if(!$loop->last)
                                                    ,
                                                @endif
                                            @endforeach
                                            )
                                        </p>
                                    @endif

                                    @if($extraCosts->isNotEmpty())
                                        <p class="text-muted small mb-0 mt-1">
                                            (excl.
                                            @foreach($extraCosts as $index => $cost)
                                                {{ $cost->name }}
                                                &#8364;{{ number_format($cost->amount, 2, ',', '.') }}@if(!$loop->last)
                                                    ,
                                                @endif
                                            @endforeach
                                            )
                                        </p>
                                    @endif
                            </div>

                            <div class="mt-4">
                                <h4 class="h5">Beschrijving</h4>
                                <div>
                                    {!! $product->description !!}
                                </div>
                            </div>
                    </div>
                </div>

                @else
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <span class="material-symbols-rounded me-2">inventory_2</span>Geen product gevonden...
                    </div>
                @endif

                <div class="d-flex flex-row flex-wrap mt-5 gap-2">
                    <a href="{{ route('admin.products') }}" class="btn btn-info text-white">Terug</a>
                    @if($product !== null)
                        <a href="{{ route('admin.products.edit', ['id' => $product->id]) }}"
                           class="btn btn-dark">Bewerk</a>
                    @endif
                </div>
            </div>
@endsection
