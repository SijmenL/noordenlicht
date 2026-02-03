<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Prijslijst NoordenLicht</title>
    <style>
        body {
            font-family: 'Georgia', serif;
            color: #5a7123;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        .logo-img {
            max-width: 150px;
            height: auto;
            margin-bottom: 10px;
        }
        .title {
            font-size: 28px;
            font-weight: bold;
            color: #5a7123;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 16px;
            color: #777;
            margin-bottom: 5px;
        }
        .link {
            color: #5a7123;
            text-decoration: none;
            font-size: 12px;
        }
        .section-header {
            background-color: #f2f5e7;
            color: #333;
            padding: 10px 15px;
            font-size: 16px;
            font-weight: bold;
            border-bottom: 2px solid #e9ecef;
            margin-top: 25px;
            margin-bottom: 0;
            border-radius: 8px 8px 0 0;
        }
        .category-subheader {
            color: #5a7123;
            padding: 5px 15px;
            margin-top: 15px;
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 13px;
            border-bottom: 1px solid #eee;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        th {
            text-align: left;
            background-color: #fff;
            color: #6c757d;
            padding: 10px 15px;
            font-size: 11px;
            text-transform: uppercase;
            border-bottom: 1px solid #dee2e6;
        }
        td {
            padding: 10px 15px;
            border-bottom: 1px solid #f2f2f2;
            vertical-align: top;
        }
        .row-striped:nth-child(even) {
            background-color: #fcfcfc;
        }
        .price-col {
            text-align: right;
            white-space: nowrap;
        }
        .price-base {
            text-decoration: line-through;
            color: #999;
            font-size: 11px;
            margin-right: 5px;
        }
        .price-final {
            font-weight: bold;
            color: #5a7123;
            font-size: 14px;
        }
        .price-discount {
            color: #5a7123;
        }
        .price-detail {
            display: block;
            font-size: 9px;
            color: #666;
            margin-top: 2px;
        }
        .discount-badge {
            background-color: #5a7123;
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            display: inline-block;
            margin-bottom: 2px;
        }
        .text-muted {
            color: #777;
            font-size: 11px;
        }
        .page-break {
            page-break-after: always;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #aaa;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>
<div class="header">
    <img src="{{ public_path('img/logo/logo pdf.png') }}" class="logo-img" alt="Logo">
    <div class="title">Prijslijst</div>
    <div class="subtitle">Een duidelijk overzicht van al onze tarieven {{ date('Y') }}</div>
    <a href="https://www.noordenlicht.nu/prijslijst" class="link">www.NoordenLicht.nu/prijslijst</a>
</div>

<div class="section-header">Accommodaties</div>
<table>
    <thead>
    <tr>
        <th>Naam</th>
        <th>Type</th>
        <th class="price-col">Prijs (per uur)</th>
    </tr>
    </thead>
    <tbody>
    @foreach($accommodaties as $acco)
        @php
            // --- Price Calculation Logic (Sync with Details) ---
            $allPrices = $acco->prices->map(fn($p) => $p->price);

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
        <tr class="row-striped">
            <td><strong>{{ $acco->name }}</strong></td>
            <td class="text-muted">{{ $acco->type }}</td>
            <td class="price-col">
                @if($hasDiscount)
                    <div><span class="discount-badge">{{ $totalPercentageDiscounts > 0 ? $totalPercentageDiscounts.'%' : 'Korting' }}</span></div>
                    <span class="price-base">€ {{ number_format($preDiscountPrice, 2, ',', '.') }}</span>
                    <span class="price-final price-discount">€ {{ number_format($calculatedPrice, 2, ',', '.') }}</span>
                @else
                    <span class="price-final">€ {{ number_format($calculatedPrice, 2, ',', '.') }}</span>
                @endif

                @if($percentageAdditions->isNotEmpty())
                    <span class="price-detail">
                        (incl. @foreach($percentageAdditions as $c) {{ $c->name }} {{ $c->amount }}% - € {{ number_format($totalBasePrice * ($c->amount / 100), 2, ',', '.') }} @if(!$loop->last), @endif @endforeach)
                    </span>
                @endif
                @if($extraCosts->isNotEmpty())
                    <span class="price-detail">
                        (excl. @foreach($extraCosts as $c) {{ $c->name }} € {{ number_format($c->amount, 2, ',', '.') }} @if(!$loop->last), @endif @endforeach)
                    </span>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

@if($products->isNotEmpty())
    <div class="section-header">Producten & Diensten</div>

    @foreach($products as $categoryName => $items)
        <div class="category-subheader">{{ $categoryName }}</div>
        <table>
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
                <tr class="row-striped">
                    <td width="60%">{{ $product->name }}</td>
                    <td class="price-col" width="40%">
                        @if($hasDiscount)
                            <span class="discount-badge">{{ $totalPercentageDiscounts > 0 ? $totalPercentageDiscounts.'%' : 'Sale' }}</span><br>
                            <span class="price-base">€ {{ number_format($preDiscountPrice, 2, ',', '.') }}</span>
                            <span class="price-final price-discount">€ {{ number_format($calculatedPrice, 2, ',', '.') }}</span>
                        @else
                            <span class="price-final">€ {{ number_format($calculatedPrice, 2, ',', '.') }}</span>
                        @endif

                        @if($percentageAdditions->isNotEmpty())
                            <span class="price-detail">
                                (incl. @foreach($percentageAdditions as $c) {{ $c->name }} {{ $c->amount }}% @if(!$loop->last), @endif @endforeach)
                            </span>
                        @endif
                        @if($extraCosts->isNotEmpty())
                            <span class="price-detail">
                                (excl. @foreach($extraCosts as $c) {{ $c->name }} € {{ number_format($c->amount, 2, ',', '.') }} @if(!$loop->last), @endif @endforeach)
                            </span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    @endforeach
@endif

@if($activities->isNotEmpty())
    @if(count($accommodaties) + count($products, COUNT_RECURSIVE) > 12)
        <div class="page-break"></div>
    @endif

    <div class="section-header">Events</div>
    <table>
        <thead>
        <tr>
            <th>Activiteit</th>
            <th>Locatie</th>
            <th class="price-col">Prijs</th>
        </tr>
        </thead>
        <tbody>
        @foreach($activities as $activity)
            @php
                $allPrices = $activity->prices->map(fn($p) => $p->price);
                if($allPrices->isEmpty()) {
                   $calculatedPrice = 0;
                   $preDiscountPrice = 0;
                   $hasDiscount = false;
                   $percentageAdditions = collect();
                   $extraCosts = collect();
                   $totalBasePrice = 0;
                   $totalPercentageDiscounts = 0;
                } else {
                    // --- Price Calculation Logic (Sync with Details) ---
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
                }
            @endphp
            <tr class="row-striped">
                <td><strong>{{ $activity->title }}</strong></td>
                <td class="text-muted">{{ $activity->location ?? '-' }}</td>
                <td class="price-col">
                    @if($calculatedPrice > 0)
                        @if($hasDiscount)
                            <div><span class="discount-badge">{{ $totalPercentageDiscounts > 0 ? $totalPercentageDiscounts.'%' : 'Korting' }}</span></div>
                            <span class="price-base">€ {{ number_format($preDiscountPrice, 2, ',', '.') }}</span>
                            <span class="price-final price-discount">€ {{ number_format($calculatedPrice, 2, ',', '.') }}</span>
                        @else
                            <span class="price-final">€ {{ number_format($calculatedPrice, 2, ',', '.') }}</span>
                        @endif

                        @if($percentageAdditions->isNotEmpty())
                            <span class="price-detail">
                                (incl. @foreach($percentageAdditions as $c) {{ $c->name }} {{ $c->amount }}% - € {{ number_format($totalBasePrice * ($c->amount / 100), 2, ',', '.') }} @if(!$loop->last), @endif @endforeach)
                            </span>
                        @endif
                        @if($extraCosts->isNotEmpty())
                            <span class="price-detail">
                                (excl. @foreach($extraCosts as $c) {{ $c->name }} € {{ number_format($c->amount, 2, ',', '.') }} @if(!$loop->last), @endif @endforeach)
                            </span>
                        @endif
                    @else
                        <span style="color: #198754; font-weight: bold;">Gratis</span>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

<div class="section-header">Maatwerk</div>
<table>
    <tbody>
    <tr>
        <td width="60%"><strong>Speciale wensen?</strong><br><span class="text-muted">Wij denken graag met u mee voor een passend aanbod.</span></td>
        <td class="price-col" width="40%">
            <span class="price-final" style="font-style: italic; color: #333;">In overleg</span>
        </td>
    </tr>
    </tbody>
</table>

<div class="footer">
    Prijzen zijn onder voorbehoud van wijzigingen en typefouten. Gegenereerd op {{ date('d-m-Y') }}.
</div>
</body>
</html>
