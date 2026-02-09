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

$preDiscountVatAmount = 0;
                    foreach ($percentageAdditions as $percentage) {
                        $preDiscountVatAmount += $totalBasePrice * ($percentage->amount / 100);
                    }
                    $preDiscountPrice = $totalBasePrice + $preDiscountVatAmount;

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



                    $carrousel_images[] = 'files/products/images/'.$product->image;
                foreach($product->images as $image) {
                    $carrousel_images[] = 'files/products/carousel/'.$image->image;
                }


            @endphp
            <div class="d-flex flex-row-responsive justify-content-center align-items-start gap-5">
                {{-- Carousel Section --}}
                <div class="w-100" style="max-width: 400px;">
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

                                @if($totalPercentageDiscounts > 0 && $fixedDiscounts->sum('amount') > 0) én @endif

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
                                <h4 class="h5">Formulier</h4>
                            @if($product->formElements->count() > 0)
                                @csrf

                                @foreach ($product->formElements as $formElement)
                                    @php
                                        $options = $formElement->option_value ? explode(',', $formElement->option_value) : [];
                                        $oldValue = old('form_elements.' . $formElement->id);
                                    @endphp

                                    <div class="form-group">
                                        <label
                                            for="formElement{{ $formElement->id }}">{{ $formElement->label }} @if($formElement->is_required)
                                                <span class="required-form">*</span>
                                            @endif</label>

                                        @switch($formElement->type)
                                            @case('text')
                                            @case('email')
                                            @case('number')
                                            @case('date')
                                                <input type="{{ $formElement->type }}"
                                                       id="formElement{{ $formElement->id }}"
                                                       name="form_elements[{{ $formElement->id }}]"
                                                       class="form-control"
                                                       value="{{ $oldValue ?? '' }}"
                                                    {{ $formElement->is_required ? 'required' : '' }}>
                                                @break

                                            @case('select')
                                                <select id="formElement{{ $formElement->id }}"
                                                        name="form_elements[{{ $formElement->id }}]"
                                                        class="form-select w-100"
                                                    {{ $formElement->is_required ? 'required' : '' }}>
                                                    <option value="">Selecteer een optie</option>
                                                    @foreach ($options as $option)
                                                        <option value="{{ $option }}"
                                                            {{ $oldValue == $option ? 'selected' : '' }}>
                                                            {{ $option }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @break

                                            @case('radio')
                                                @foreach ($options as $option)
                                                    <div class="form-check">
                                                        <input type="radio"
                                                               id="formElement{{ $formElement->id }}_{{ $loop->index }}"
                                                               name="form_elements[{{ $formElement->id }}]"
                                                               value="{{ $option }}"
                                                               class="form-check-input"
                                                            {{ $oldValue == $option ? 'checked' : '' }}
                                                            {{ $formElement->is_required ? 'required' : '' }}>
                                                        <label for="formElement{{ $formElement->id }}_{{ $loop->index }}"
                                                               class="form-check-label">
                                                            {{ $option }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                                @break

                                            @case('checkbox')
                                                @php
                                                    $oldValues = is_array($oldValue) ? $oldValue : [];
                                                @endphp
                                                @foreach ($options as $option)
                                                    <div class="form-check">
                                                        <input type="checkbox"
                                                               id="formElement{{ $formElement->id }}_{{ $loop->index }}"
                                                               name="form_elements[{{ $formElement->id }}][]"
                                                               value="{{ $option }}"
                                                               class="form-check-input">
                                                        <label for="formElement{{ $formElement->id }}_{{ $loop->index }}"
                                                               class="form-check-label">{{ $option }}</label>
                                                    </div>
                                                @endforeach
                                                @break
                                        @endswitch

                                        @if ($errors->has('form_elements.' . $formElement->id))
                                            <div class="invalid-feedback">
                                                {{ $errors->first('form_elements.' . $formElement->id) }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
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
