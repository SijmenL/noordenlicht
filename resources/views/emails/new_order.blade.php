@extends('emails.layouts.mail')

@php
    use Illuminate\Support\Str;

    $order = \App\Models\Order::findOrFail($data['relevant_id']);
@endphp

@section('title')
    <h1 class="email-title">Bedankt voor je bestelling!</h1>
@endsection

@section('greeting')
    <p>Beste {{ $order->first_name }},</p>
    <br>
    <p>Hartelijk dank voor je bestelling bij NoordenLicht. Je bestelling is goed doorgekomen!</p>
    <p>We gaan direct voor je aan de slag. Hieronder vind je een overzicht van wat je hebt besteld.</p>
@endsection

@section('info')
    <div class="box">
        <h3 style="margin-bottom: 15px; border-bottom: 1px solid #dcdcdc; padding-bottom: 10px;">
            Bestelling #{{ $order->order_number }}
        </h3>

        <table width="100%" cellpadding="0" cellspacing="0" border="0">
            @foreach($order->items as $item)
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #e6eacb; vertical-align: top;">
                        <span style="font-weight: 600; color: #5a7123;">
                            {{ $item->product_name ?? $item->name ?? 'Product' }}
                        </span>
                        <br>
                        <span style="font-size: 13px; color: #666;">Aantal: {{ $item->quantity }}</span>
                    </td>


                    <td style="padding: 8px 0; border-bottom: 1px solid #e6eacb; text-align: right; vertical-align: top; white-space: nowrap;">
                        &euro; {{ number_format($item->unit_price, 2, ',', '.') }}
                    </td>
                </tr>
            @endforeach

            <tr>
                <td style="padding-top: 15px; font-weight: bold; text-align: right;">Totaal</td>
                <td style="padding-top: 15px; font-weight: bold; text-align: right; white-space: nowrap; color: #5a7123;">
                    &euro; {{ number_format($order->total_amount, 2, ',', '.') }}
                </td>
            </tr>
        </table>
    </div>

    <br>

    <div class="two-columns">
        <div class="column">
            <h4 style="color: #624b25;">Factuur</h4>
            <p style="font-size: 14px; line-height: 1.6;">
                {{ $order->first_name }} {{ $order->last_name }}<br>
                {{ $order->address }}<br>
                {{ $order->zipcode }} {{ $order->city }}<br>
                {{ $order->country }}
            </p>
        </div>
        <div class="column">
            <h4 style="color: #624b25;">Status</h4>
            <p style="font-size: 14px; line-height: 1.6;">
                <strong>Betaalstatus:</strong> {{ ucfirst($order->payment_status) }}<br>
                <strong>Orderstatus:</strong> {{ ucfirst($order->status) }}
            </p>
        </div>
    </div>
@endsection

@section('action')
    @if($order->account_id !== null)
    <p style="margin-bottom: 10px;">Wil je je bestelling bekijken in je account?</p>
    {{-- Pas de URL aan naar jouw route --}}
    <a href="{{ url('/account/orders/' . $order->id) }}" class="action-button">Bekijk mijn bestelling</a>
    @endif
@endsection

@section('main_footer')
    <td style="padding-top: 30px;">
        <p class="centered" style="font-size: 14px;">
            Heb je vragen over je bestelling? <br>
            Beantwoord deze mail gerust, we helpen je graag verder.
        </p>
    </td>
@endsection
