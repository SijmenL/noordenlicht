@extends('layouts.dashboard')

@section('content')
    <div class="container col-md-11">
        <h1>Details @if($accommodatie !== null)
                {{$accommodatie->name}}
            @endif</h1>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">

                <li class="breadcrumb-item" aria-current="page"><a href="{{route('admin')}}">Dashboard</a></li>
                <li class="breadcrumb-item" aria-current="page"><a
                        href="{{route('admin.accommodaties')}}">Accommodaties</a></li>
                <li class="breadcrumb-item active" aria-current="page">Details {{$accommodatie->name}} </li>
            </ol>
        </nav>

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

        @if($accommodatie !== null)
            @php
                // --- Price Calculation Logic ---
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


                $carrousel_images = [];
                foreach($accommodatie->images as $image) {
                    $carrousel_images[] = '/files/accommodaties/carousel/'.$image->image;
                }
            @endphp
            <div class="d-flex flex-row-responsive justify-content-center align-items-center gap-5">
                <div class="w-100">
                    <x-carousel :images="$carrousel_images"/>
                </div>
                <div class="w-100">
                    <h1>{{ $accommodatie->name }}</h1>
                    <h2 style="font-style: italic">{{ $accommodatie->type }}</h2>

                    <div class="p-3 d-flex flex-column align-items-start">
                        @if($hasDiscount)
                            <p class="badge bg-success mb-2">{{ $totalPercentageDiscounts }}% korting!</p>

                            <div class="d-flex flex-row gap-2">
                                <h3 class="d-inline-block"
                                    style="font-style: italic; text-decoration: line-through; opacity: 0.6;">
                                    &#8364;{{ number_format($preDiscountPrice, 2, ',', '.') }}
                                </h3>
                                @else
                                    <div class="">

                                        @endif
                                        <h3 class="d-inline-block fw-bold" style="font-style: italic">
                                            &#8364;{{ number_format($calculatedPrice, 2, ',', '.') }}
                                            <small class="text-muted fw-normal">per uur</small>
                                        </h3>
                                    </div>
                                    @if($totalPercentageAdditions !== 0)
                                        <p class="text-muted small mb-0 mt-1">
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
                                        <p class="text-muted small mb-0 mt-1">
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

                            <div>
                                {!! $accommodatie->description !!}
                            </div>
                    </div>
                </div>

                <div class="d-flex flex-row flex-wrap gap-5 mt-5">
                    <div class="row row-cols-3 g-3 text-center">
                        @foreach($accommodatie->icons as $icon)
                            <div class="col d-flex flex-column align-items-center">
                                <div class="icon-color">
                                    {!! file_get_contents(public_path('/files/accommodaties/icons/'.$icon->icon)) !!}
                                </div>

                                <style>
                                    .icon-color svg {
                                        width: 50px;
                                        height: 50px;
                                        aspect-ratio: 1/1;
                                        object-fit: cover;
                                        fill: #5a7123;
                                    }
                                </style>
                                <span class="mt-2">{{ $icon->text }}</span>
                            </div>
                        @endforeach
                    </div>


                </div>

                @else
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <span class="material-symbols-rounded me-2">home</span>Geen accommodatie gevonden...
                    </div>
                @endif

                <div class="d-flex flex-row flex-wrap mt-5 gap-2">
                    <a href="{{ route('admin.accommodaties') }}" class="btn btn-info">Terug</a>
                    @if($accommodatie !== null)
                        <a href="{{ route('admin.accommodaties.edit', ['id' => $accommodatie->id]) }}"
                           class="btn btn-dark">Bewerk</a>
                    @endif

                </div>
            </div>
@endsection

