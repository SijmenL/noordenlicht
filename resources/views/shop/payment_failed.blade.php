@extends('layouts.app')

@section('content')
    <div class="container my-5">
        <div class="shadow-sm w-100 border-0 rounded-4 overflow-hidden">
            <div class="text-center p-5 bg-white">

                @if($status == 'canceled')
                    <span class="material-symbols-rounded text-warning" style="font-size: 64px;">cancel</span>
                    <h1 class="mt-3 text-dark">Betaling Geannuleerd</h1>
                    <p class="lead text-muted">Je hebt de betaling voor bestelling #{{ $order->order_number }} geannuleerd.</p>
                @elseif($status == 'expired')
                    <span class="material-symbols-rounded text-secondary" style="font-size: 64px;">timer_off</span>
                    <h1 class="mt-3 text-dark">Betaling Verlopen</h1>
                    <p class="lead text-muted">De tijd om de betaling voor #{{ $order->order_number }} af te ronden is verstreken.</p>
                @else
                    <span class="material-symbols-rounded text-danger" style="font-size: 64px;">error</span>
                    <h1 class="mt-3 text-dark">Betaling Mislukt</h1>
                    <p class="lead text-muted">Er is helaas iets misgegaan tijdens het betalen van bestelling #{{ $order->order_number }}.</p>
                @endif

                <hr class="my-4 w-50 mx-auto">

                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('shop') }}" class="btn btn-outline-primary rounded-pill px-4">Terug naar winkel</a>

                    <a href="{{ route('order.retry', $order->id) }}" class="btn btn-primary rounded-pill px-4">Opnieuw proberen</a>
                </div>
            </div>
        </div>
    </div>
@endsection
