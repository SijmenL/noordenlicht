@extends('layouts.app')

@section('content')
    <div class="container my-5">
        <div class="shadow-sm w-100 border-0 rounded-4 overflow-hidden">
            <div class="text-center p-5 bg-white">
                <span class="material-symbols-rounded text-info" style="font-size: 64px;">pending</span>
                <h1 class="mt-3 text-dark">Betaling in behandeling</h1>
                <p class="lead text-muted">
                    We wachten nog op de bevestiging van de betaling voor bestelling #{{ $order->order_number }}.
                    <br>
                    Dit kan enkele minuten duren. Je ontvangt vanzelf een e-mail zodra de betaling is verwerkt.
                </p>

                <hr class="my-4 w-50 mx-auto">

                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-4">Terug naar Home</a>
                    <a href="{{ route('order.success', ['order_number' => $order->order_number]) }}" class="btn btn-outline-primary rounded-pill px-4">Check status opnieuw</a>
                </div>
            </div>
        </div>
    </div>
@endsection
