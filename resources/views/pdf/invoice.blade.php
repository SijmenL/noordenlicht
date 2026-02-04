@php
    $total_vat = 0;
@endphp

    <!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Factuur {{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Georgia', serif;
            color: #333;
            font-size: 12px;
            line-height: 1.4;
        }
        .header-container {
            width: 100%;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        .logo-img {
            max-width: 150px;
            height: auto;
        }
        .company-info {
            float: right;
            text-align: right;
            color: #5a7123;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .invoice-details-box {
            margin-bottom: 30px;
            width: 100%;
        }
        .client-address {
            float: left;
            width: 50%;
        }
        .invoice-meta {
            float: right;
            width: 40%;
            text-align: right;
        }
        .section-header {
            background-color: #f2f5e7;
            color: #333;
            padding: 8px 15px;
            font-size: 14px;
            font-weight: bold;
            border-bottom: 2px solid #5a7123;
            margin-top: 20px;
            border-radius: 4px 4px 0 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            text-align: left;
            background-color: #fff;
            color: #5a7123;
            padding: 10px;
            font-size: 11px;
            text-transform: uppercase;
            border-bottom: 1px solid #5a7123;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #f2f2f2;
            vertical-align: top;
        }
        .row-striped:nth-child(even) {
            background-color: #fcfcfc;
        }
        .text-right {
            text-align: right;
        }
        .total-row td {
            border-top: 2px solid #5a7123;
            font-weight: bold;
            font-size: 14px;
            padding-top: 15px;
            color: #333;
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
        .status-paid {
            color: #76e44a;
            font-weight: bold;
            border: 1px solid #76e44a;
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 10px;
        }

        .status-unpaid {
            color: #ff7670;
            font-weight: bold;
            border: 1px solid #ff7670;
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 10px;
        }
        /* New helper classes */
        .price-base {
            text-decoration: line-through;
            color: #999;
            font-size: 9px;
            margin-right: 5px;
        }
        .discount-badge {
            background-color: #5a7123;
            color: white;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 8px;
        }
        .price-detail {
            display: block;
            font-size: 9px;
            color: #666;
        }
        .base-price-info {
            font-size: 9px;
            color: #555;
            margin-bottom: 2px;
            font-style: italic;
        }
    </style>
</head>
<body>

<div class="header-container">
    <div class="company-info">
        <div class="company-name">NoordenLicht</div>
        <div>Adresregel 1</div>
        <div>1234 AB Plaats</div>
        <div>KvK: 12345678</div>
        <div>BTW: NL123456789B01</div>
    </div>
    <img src="{{ public_path('img/logo/logo pdf.png') }}" class="logo-img" alt="Logo">
</div>

<div class="invoice-details-box">
    <div class="client-address">
        <strong>Factuuradres:</strong><br>
        {{ $order->first_name }} {{ $order->last_name }}<br>
        {{ $order->address }}<br>
        {{ $order->zipcode }} {{ $order->city }}<br>
        {{ $order->country ?? 'Nederland' }}
    </div>

    <div class="invoice-meta">
        <h1 style="font-size: 24px; color: #333; margin: 0 0 10px 0;">FACTUUR</h1>
        <table style="width: 100%; border: none;">
            <tr>
                <td style="padding: 2px; border: none; text-align: right;"><strong>Factuurnummer:</strong></td>
                <td style="padding: 2px; border: none; text-align: right;">{{ $order->order_number }}</td>
            </tr>
            <tr>
                <td style="padding: 2px; border: none; text-align: right;"><strong>Datum:</strong></td>
                <td style="padding: 2px; border: none; text-align: right;">{{ $order->created_at->format('d-m-Y') }}</td>
            </tr>
            @if($order->mollie_payment_id)
                <tr>
                    <td style="padding: 2px; border: none; text-align: right;"><strong>Referentie:</strong></td>
                    <td style="padding: 2px; border: none; text-align: right;">{{ substr($order->mollie_payment_id, 0, 12) }}...</td>
                </tr>
            @endif
        </table>

        @if($order->status == 'paid' || $order->payment_status == 'paid')
            <div class="status-paid">BETAALD</div>
        @else
            <div class="status-unpaid">NOG NIET BETAALD</div>

        @endif
    </div>
    <div style="clear: both;"></div>
</div>

<div class="section-header">Specificatie</div>
<table>
    <thead>
    <tr>
        <th width="40%">Omschrijving</th>
        <th width="10%" class="text-right">Aantal</th>
        <th width="15%" class="text-right">Prijs excl.</th>
        <th width="15%" class="text-right">Prijs incl.</th>
        <th width="20%" class="text-right">Totaal (incl.)</th>
    </tr>
    </thead>
    <tbody>
    @foreach($order->items as $item)
        @php
            $meta = $item->price_metadata ?? [];
            $hasDiscount = $item->unit_discount_amount > 0 || $item->unit_discount_percentage > 0;

            // Normal Price Calculation (Base + VAT on Base + Extras)
            // This represents the full price if no discount was applied
            $basePrice = $item->unit_base_price;
            $normalVat = 0;
            if (!empty($meta['additions'])) {
                foreach($meta['additions'] as $add) {
                    // VAT on full base price
                    $normalVat += $basePrice * ($add['amount'] / 100);
                }
            } else {
                // Fallback (might be inaccurate if unit_vat is discounted)
                $normalVat = $item->unit_vat;
            }

            // Extras
            $extraCostsNormal = 0;
            if(!empty($meta['extras'])) {
                foreach($meta['extras'] as $ex) {
                    $extraCostsNormal += $ex['amount'];
                }
            } elseif ($item->unit_extra > 0) {
                $extraCostsNormal = $item->unit_extra;
            }

            // Normal Price = Base + Full VAT + Extras
            $normalPrice = $basePrice + $normalVat + $extraCostsNormal;

            // Add item's ACTUAL VAT to the total (unit_vat * quantity)
            $total_vat += ($item->unit_vat * $item->quantity);

            // Exclusive Price Calculation for column:
            // Unit Price (which is the final inclusive price) - VAT = Exclusive Price
            $priceExclusive = $item->unit_price - $item->unit_vat;
        @endphp
        <tr class="row-striped">
            <td>
                <strong>{{ $item->product_name }}</strong>
                @if($item->product_id)
                    <br><span style="font-size: 10px; color: #777;">ID: {{ $item->product_id }}</span>
                @endif

                <div style="margin-top: 4px;">
                    @if($hasDiscount)
                        <span class="price-base">
                                € {{ number_format($normalPrice, 2, ',', '.') }}
                            </span>
                        @if($item->unit_discount_percentage > 0)
                            <span class="discount-badge">
                                    {{ $item->unit_discount_percentage }}% Korting
                                </span>
                        @endif
                        <br>
                    @endif

                    @if(!empty($meta['additions']))
                        <span class="price-detail">
                                (incl. @foreach($meta['additions'] as $c) {{ $c['name'] }} {{ $c['amount'] }}% - € {{ number_format($c['calculated_amount'], 2, ',', '.') }} @if(!$loop->last), @endif @endforeach)
                            </span>
                    @endif

                    @if(!empty($meta['extras']))
                        <span class="price-detail">
                                (excl. @foreach($meta['extras'] as $c) {{ $c['name'] }} € {{ number_format($c['amount'], 2, ',', '.') }} @if(!$loop->last), @endif @endforeach)
                            </span>
                    @endif
                </div>
            </td>
            <td class="text-right">{{ $item->quantity }}</td>
            <td class="text-right">€ {{ number_format($priceExclusive, 2, ',', '.') }}</td>
            <td class="text-right">€ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
            <td class="text-right">€ {{ number_format($item->total_price, 2, ',', '.') }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <td colspan="3" style="border:none;"></td>
        <td class="text-right" style="padding-top: 15px;"><strong>Subtotaal:</strong></td>
        <td class="text-right" style="padding-top: 15px;">€ {{ number_format($order->total_amount, 2, ',', '.') }}</td>
    </tr>

    <tr>
        <td colspan="3" style="border:none;"></td>
        <td class="text-right" style="color: #777; font-size: 10px;"><i>Waarvan BTW:</i></td>
        <td class="text-right" style="color: #777; font-size: 10px;">
            <i>€ {{ number_format($total_vat, 2, ',', '.') }}</i>
        </td>
    </tr>

    <tr class="total-row">
        <td colspan="3" style="border:none;"></td>
        <td class="text-right">Totaal</td>
        <td class="text-right">€ {{ number_format($order->total_amount, 2, ',', '.') }}</td>
    </tr>
    </tfoot>
</table>

<div class="footer">
    NoordenLicht - Factuur gegenereerd op {{ date('d-m-Y H:i') }} - Pagina 1 van 1
</div>

</body>
</html>
