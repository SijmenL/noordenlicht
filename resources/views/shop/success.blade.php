@extends('layouts.app')


@section('content')
    <div class="container my-5">
        <div class="shadow-sm w-100 border-0">
            <div class=" text-center p-5">
                <span class="material-symbols-rounded text-success" style="font-size: 64px;">check_circle</span>
                <h1 class="mt-3">Bedankt voor je bestelling!</h1>
                <p class="lead text-muted">Je bestelling #{{ $order->order_number }} is succesvol afgerond.</p>

                <hr class="my-4">

                <div class="row text-start justify-content-center">
                    <div class="col-md-8">
                        <h4>Overzicht</h4>
                        <ul class="list-group mb-4">

                            @foreach($order->items as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold">{{ $item->product_name }}</span>
                                        <div class="small text-muted">Aantal: {{ $item->quantity }}</div>
                                    </div>
                                    <span>&euro;{{ number_format($item->total_price, 2) }}</span>
                                </li>
                            @endforeach
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-light fw-bold">
                                <span>Totaal</span>
                                <span>&euro;{{ number_format($order->total_amount, 2) }}</span>
                            </li>
                        </ul>

                        @if($order->tickets->count() > 0)
                            <h4 class="mb-3">Jouw Tickets</h4>
                            <div class="d-grid gap-2">
                                @foreach($order->tickets as $ticket)
                                    <a href="{{ route('ticket.download', $ticket->uuid) }}" class="btn btn-outline-primary d-flex align-items-center justify-content-center">
                                        <span class="material-symbols-rounded me-2">confirmation_number</span>
                                        Download Ticket: {{ $ticket->activity->title }} ({{ \Carbon\Carbon::parse($ticket->start_date)->format('d-m-Y') }})
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-5">
                    <a href="{{ route('home') }}" class="btn btn-primary">Terug naar Home</a>
                </div>
            </div>
        </div>
    </div>
@endsection
