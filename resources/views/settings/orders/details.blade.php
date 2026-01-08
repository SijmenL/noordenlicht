@php use App\Models\Accommodatie;use App\Models\Activity;use App\Models\Booking;use App\Models\Product;use App\Models\Ticket; @endphp
@extends('layouts.app')

@section('content')
    <div class="container mt-5 mb-5 py-5 col-md-11">
        <h1>Order {{ $order->order_number }}</h1>


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

        <div class="d-flex flex-column gap-3">


            <div>
                <div class="bg-white border w-100 p-4 rounded mt-3">
                    <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">shopping_bag</span>Producten,
                        tickets en/of boekingen</h2>

                    <div class="p-0">
                        <div
                            class="d-none d-md-flex text-muted fw-bold small text-uppercase px-4 py-2 border-bottom">
                            <div class="col-6">Product</div>
                            <div class="col-2 text-center">Aantal</div>
                            <div class="col-2 text-end">Prijs</div>
                            <div class="col-2 text-end">Totaal</div>
                        </div>

                        @foreach($order->items as $item)
                            @php
                                // Initialize variables to null to prevent "Undefined variable" errors
                                $product = $item->product_id ? Product::find($item->product_id) : null;
                                $booking = null;
                                $accomodation = null;
                                $ticket = null;
                                $event = null;

                                // If it's not a standard product, look for bookings or events attached to the order
                                if (!$product) {
                                    $booking = Booking::with(['accommodatie', 'user', 'order.items'])->where('order_id', $order->id)->first();

                                    $ticket = Ticket::where('order_id', $order->id)->first();

                                    if ($booking) {
                                        $accomodation = Accommodatie::find($booking->accommodatie_id);
                                    }

                                    if ($ticket) {
                                        // You assigned this to $event, so we must use $event in the HTML below
                                        $event = Activity::find($ticket->activity_id);
                                    }
                                }
                            @endphp
                            <div class="border-bottom p-4">
                                <div class="row gy-3 align-items-center">
                                    <div class="col-12 col-md-6">
                                        <div class="d-flex flex-column">
                                            <div class="d-flex align-items-center mb-1">
                                                {{-- Image Logic --}}
                                                @if($product && $product->image)
                                                    <img alt="productafbeelding" class="rounded me-3 zoomable-image"
                                                         style="width: 100px; aspect-ratio: 1/1; object-fit: cover"
                                                         src="{{ asset('/files/products/images/'.$product->image) }}">

                                                @elseif($accomodation && $accomodation->image)
                                                    <img alt="productafbeelding" class="rounded me-3 zoomable-image"
                                                         style="width: 100px; aspect-ratio: 1/1; object-fit: cover"
                                                         src="{{ asset('/files/accommodaties/images/'.$accomodation->image) }}">

                                                @elseif($event && $event->image)
                                                    {{-- Fixed: Changed $activity to $event to match the PHP variable above --}}
                                                    <img alt="productafbeelding" class="rounded me-3 zoomable-image"
                                                         style="width: 100px; aspect-ratio: 1/1; object-fit: cover"
                                                         src="{{ asset('files/agenda/agenda_images/'.$event->image) }}">
                                                @endif

                                                <span class="fw-bold text-dark">{{ $item->product_name }}</span>

                                                @if($item->product_id && $product)
                                                    <a href="{{ route('shop.details', $item->product_id) }}"
                                                       target="_blank" class="text-muted ms-2" title="Bekijk product">
                                                        <span class="material-symbols-rounded fs-6">open_in_new</span>
                                                    </a>
                                                @elseif($item->product_id)
                                                    <span
                                                        class="badge bg-danger bg-opacity-10 text-danger border border-danger ms-2">Verwijderd</span>
                                                @endif
                                            </div>

                                            @php
                                                $itemResponses = collect();

                                                if ($item->product_id) {
                                                    // 1. Get ALL responses for this product type
                                                    $allProductResponses = $formResponses->where('product_id', $item->product_id);

                                                    // 2. Group them by submission ID so we have distinct "sets" of answers
                                                    $groupedResponses = $allProductResponses->groupBy('submitted_id')->values();

                                                    // 3. Get all items in this specific order that match this product ID
                                                    // We use values() to ensure keys are 0, 1, 2...
                                                    $sameItemsInOrder = $order->items->where('product_id', $item->product_id)->values();

                                                    // 4. Find the index of the CURRENT item ($item) within that list
                                                    $currentIndex = $sameItemsInOrder->search(function($val) use ($item) {
                                                        return $val->id === $item->id;
                                                    });

                                                    // 5. Pick the response group that matches the index of the item
                                                    // E.g. Item #1 gets Response Group #1. Item #2 gets Response Group #2.
                                                    if ($groupedResponses->has($currentIndex)) {
                                                        $itemResponses = $groupedResponses->get($currentIndex);
                                                    }

                                                } else {
                                                    $activityResponses = $formResponses->where('location', 'activity');
                                                    foreach($activityResponses->groupBy('activity_id') as $actId => $responses) {
                                                         $activity = $responses->first()->activity ?? null;
                                                         if($activity && str_contains($item->product_name, $activity->title)) {
                                                             $itemResponses = $responses;
                                                             break;
                                                         }
                                                    }
                                                }
                                            @endphp

                                            @if($itemResponses->isNotEmpty())
                                                <div class="mt-2 p-3 bg-light rounded border small">
                                                    @foreach($itemResponses->groupBy('submitted_id') as $index => $group)
                                                        @if($itemResponses->groupBy('submitted_id')->count() > 1)
                                                            <div class="fw-bold text-secondary mb-1 border-bottom pb-1">
                                                                Inschrijving {{ $loop->iteration }}</div>
                                                        @endif

                                                        <div class="d-flex flex-column gap-1 mb-2 last-mb-0">
                                                            @foreach($group as $response)
                                                                <div class="d-flex justify-content-between">
                                                                    <span class="text-muted">{{ $response->formElement->label ?? 'Veld' }}:</span>
                                                                    <span
                                                                        class="text-dark text-end fw-medium">{{ $response->response }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-4 col-md-2 text-md-center text-muted">
                                        <span class="d-md-none small text-uppercase">Aantal: </span>
                                        {{ $item->quantity }}x
                                    </div>

                                    <div class="col-4 col-md-2 text-end text-muted">
                                        <span class="d-md-none small text-uppercase">Prijs: </span>
                                        &#8364;{{ number_format($item->unit_price, 2, ',', '.') }}
                                    </div>

                                    <div class="col-4 col-md-2 text-end fw-bold text-dark fs-5">
                                        &#8364;{{ number_format($item->total_price, 2, ',', '.') }}
                                    </div>
                                </div>
                            </div>

                        @endforeach

                        <div class="p-4 bg-light bg-opacity-50">
                            <div class="d-flex justify-content-between justify-content-md-end align-items-center gap-4">
                                <span class="text-muted text-uppercase fw-bold small">Totaal Orderbedrag</span>
                                <span
                                    class="h4 mb-0 fw-bold text-primary">&#8364;{{ number_format($order->total_amount, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($order->tickets->count() > 0)
                    <div class="bg-white border w-100 p-4 rounded mt-3">
                        <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">local_activity</span>Tickets
                        </h2>
                        <div class="d-grid gap-2">
                            @foreach($order->tickets as $ticket)
                                <a href="{{ route('ticket.download', $ticket->uuid) }}"
                                   class="btn btn-outline-primary d-flex align-items-center justify-content-center">
                                    <span class="material-symbols-rounded me-2">confirmation_number</span>
                                    Download Ticket: {{ $ticket->activity->title }}
                                    ({{ \Carbon\Carbon::parse($ticket->start_date)->format('d-m-Y') }})
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="bg-white border w-100 p-4 rounded mt-3">
                    <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">person</span>Klantgegevens
                    </h2>

                    <div class="d-flex align-items-center mb-3 mt-4">
                        <div class="bg-light rounded-5-circle p-2 me-3 text-secondary">
                            <span class="material-symbols-rounded fs-3">account_circle</span>
                        </div>
                        <div>
                            <div class="fw-bold text-dark">{{ $order->first_name }} {{ $order->last_name }}</div>
                            <a href="mailto:{{ $order->email }}"
                               class="small text-decoration-none">{{ $order->email }}</a>
                        </div>
                    </div>

                    <hr class="text-muted opacity-25">

                    <div class="small text-muted">
                        <div class="fw-bold text-uppercase mb-1" style="font-size: 0.7rem;">Adres</div>
                        <div>{{ $order->address }}</div>
                        <div>{{ $order->zipcode }} {{ $order->city }}</div>
                        <div>{{ $order->country }}</div>
                    </div>
                </div>

                <div class="bg-white border w-100 p-4 rounded mt-3">
                    <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">analytics</span>Info</h2>
                    <ul class="list-unstyled mb-0 small">
                        <li class="d-flex justify-content-between py-2 border-bottom border-light">
                            <span class="text-muted">Datum</span>
                            <span class="fw-medium text-dark">{{ $order->created_at->format('d-m-Y') }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom border-light">
                            <span class="text-muted">Tijd</span>
                            <span class="fw-medium text-dark">{{ $order->created_at->format('H:i') }}</span>
                        </li>
                        @if($order->created_at !== $order->updated_at)
                            <li class="d-flex justify-content-between py-2 border-bottom border-light">
                                <span class="text-muted">Datum bewerkt</span>
                                <span class="fw-medium text-dark">{{ $order->updated_at->format('d-m-Y') }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-2 border-bottom border-light">
                                <span class="text-muted">Tijd bewewrkt</span>
                                <span class="fw-medium text-dark">{{ $order->updated_at->format('H:i') }}</span>
                            </li>
                        @endif
                        <li class="d-flex justify-content-between py-2">
                            <span class="text-muted">Aantal Items</span>
                            <span class="fw-medium text-dark">{{ $order->items->sum('quantity') }}</span>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </div>

    <style>
        /* Simple helper to remove margin from last child in loops */
        .last-mb-0:last-child {
            margin-bottom: 0 !important;
        }
    </style>
@endsection
