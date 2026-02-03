@extends('layouts.dashboard')

@section('content')

    <div class="container col-md-11">
        <h1>Details {{ $order->order_number }}</h1>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item" aria-current="page"><a href="{{route('admin')}}">Dashboard</a></li>
                <li class="breadcrumb-item" aria-current="page"><a href="{{route('admin.orders')}}">Bestellingen</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ $order->order_number }}</li>
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

        <div class="d-flex flex-column gap-2">

            {{-- Tickets / Activities Table --}}

            <div class="bg-white w-100 p-4 rounded mt-3">
                <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">local_activity</span>Producten, Boekingen & Tickets
                </h2>

                <div class="w-100">
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <thead>
                            <tr>
                                <th scope="col" style="width: 50%;">Product / Beschrijving</th>
                                <th scope="col">Aantal</th>
                                <th scope="col">Stukprijs</th>
                                <th scope="col">Totaal</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                // Track how many response sets have been consumed per product/activity context
                                // This ensures sequential distribution: Item 1 gets Set 1, Item 2 gets Set 2, etc.
                                $consumedCounts = [];
                            @endphp
                            @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        {{-- 1. Product / Item Name --}}
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-bold">{{ $item->product_name }}</span>

                                            {{-- Link to shop if product exists, otherwise warning --}}
                                            @if($item->product_id)
                                                @if($item->product)
                                                    <a href="{{ route('admin.products.details', $item->product_id) }}" target="_blank"
                                                       class="text-muted small" title="Bekijk in shop">
                                                        <span class="material-symbols-rounded fs-6 align-middle">open_in_new</span>
                                                    </a>
                                                @else
                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger p-1 ms-2">Verwijderd</span>
                                                @endif
                                            @endif
                                        </div>

                                        {{-- 2. Form Data Logic --}}
                                        @php
                                            $itemResponses = collect();
                                            $candidates = collect();
                                            $contextKey = '';

                                            if ($item->product_id) {
                                                // Match responses by Product ID
                                                $candidates = $formResponses->where('product_id', $item->product_id);
                                                $contextKey = 'prod_' . $item->product_id;
                                            } else {
                                                // Match responses by Activity: Check if the Item Name contains the Activity Title
                                                $activityResponses = $formResponses->where('location', 'activity');
                                                foreach($activityResponses->groupBy('activity_id') as $actId => $responses) {
                                                     $activity = $responses->first()->activity ?? null;
                                                     if($activity && str_contains($item->product_name, $activity->title)) {
                                                         $candidates = $responses;
                                                         $contextKey = 'act_' . $actId;
                                                         break;
                                                     }
                                                }
                                            }

                                            if($candidates->isNotEmpty() && $contextKey) {
                                                // Group by submission ID to keep sets together
                                                // Sort by key (submitted_id) to ensure chronological order
                                                $groupedSets = $candidates->groupBy('submitted_id')->sortKeys();

                                                $currentOffset = $consumedCounts[$contextKey] ?? 0;
                                                $setsNeeded = $item->quantity;

                                                // Slice the next batch of response sets for this specific item instance
                                                $selectedSets = $groupedSets->slice($currentOffset, $setsNeeded);

                                                // Collapse back to a single collection so the view logic below works as expected
                                                $itemResponses = $selectedSets->collapse();

                                                // Update the counter for the next item of this type
                                                $consumedCounts[$contextKey] = $currentOffset + $setsNeeded;
                                            }
                                        @endphp

                                        {{-- 3. Display Form Data (if found) --}}
                                        @if($itemResponses->isNotEmpty())
                                            <div class="mt-3">
                                                <table class="table table-sm table-bordered bg-white small mb-0" style="max-width: 90%;">
                                                    @foreach($itemResponses->groupBy('submitted_id') as $index => $group)
                                                        {{-- Header for multiple sets (e.g. 2 tickets = 2 entries) --}}
                                                        @if($itemResponses->groupBy('submitted_id')->count() > 1)
                                                            <thead class="table-light">
                                                            <tr>
                                                                <th colspan="2" class="text-muted">Inschrijving {{ $loop->iteration }}</th>
                                                            </tr>
                                                            </thead>
                                                        @endif

                                                        <tbody>
                                                        @foreach($group as $response)
                                                            <tr>
                                                                <td class="text-muted bg-light" style="width: 35%;">
                                                                    {{ $response->formElement->label ?? 'Veld' }}
                                                                </td>
                                                                <td>
                                                                    {{ $response->response }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                        </tbody>
                                                    @endforeach
                                                </table>
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Standard Order Fields --}}
                                    <td class="align-top">{{ $item->quantity }}</td>
                                    <td class="align-top">&#8364;{{ number_format($item->unit_price, 2, ',', '.') }}</td>
                                    <td class="align-top fw-bold">&#8364;{{ number_format($item->total_price, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Status Management Section --}}
            <div class="bg-white w-100 p-4 rounded mt-3">
                <div class="d-flex flex-row gap-2 align-items-center">
                    <h2 class="flex-row gap-3 align-items-center"><span
                            class="material-symbols-rounded me-2">paid</span>Status
                    </h2>
                    @php
                        $statusClass = match($order->status) {
                            'completed', 'shipped' => 'success',
                            'paid' => 'info',
                            'open', 'pending' => 'dark',
                            'cancelled', 'failed', 'expired' => 'danger',
                            default => 'secondary'
                        };
                    @endphp
                    <span class="badge bg-{{ $statusClass }}">
                           @if($order->status == 'pending') In afwachting @endif
                        @if($order->status == 'open') Niet betaald @endif
                        @if($order->status == 'paid') Betaald @endif
                        @if($order->status == 'shipped') Verzonden @endif
                        @if($order->status == 'completed') Afgerond @endif
                        @if($order->status == 'cancelled') Geannuleerd @endif
                        @if($order->status == 'failed') Misgegaan @endif
                        @if($order->status == 'expired') Verlopen @endif
                                </span>
                </div>
                <div class="w-100">
                    <form action="{{ route('admin.orders.details.update', ['id' => $order->id]) }}" method="POST"
                          class="row align-items-end w-100">
                        @csrf
                        <div class="d-flex flex-column">
                            <label for="status" class="col-md-4 col-form-label ">Wijzig Status</label>
                            <select id="status"
                                    class="w-100 form-select @error('status') is-invalid @enderror"
                                    name="status">
                                <option value="open" {{ $order->status == 'open' ? 'selected' : '' }}>Niet betaald
                                </option>
                                <option value="paid" {{ $order->status == 'paid' ? 'selected' : '' }}>Betaald
                                </option>
                                <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Verzonden
                                </option>
                                <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Afgerond
                                </option>
                                <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>
                                    Geannuleerd
                                </option>
                            </select>
                            @error('category')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="d-flex flex-row gap-2 align-items-center mt-3">

                            <button
                                onclick="function handleButtonClick(button) {
                                 button.disabled = true;
                                button.classList.add('loading');

                                // Show the spinner and hide the text
                                button.querySelector('.button-text').style.display = 'none';
                                button.querySelector('.loading-spinner').style.display = 'inline-block';
                                button.querySelector('.loading-text').style.display = 'inline-block';

                                button.closest('form').submit();
                            }
                            handleButtonClick(this)"
                                class="btn btn-success flex flex-row align-items-center justify-content-center">
                                <span class="button-text">Status aanpassen</span>
                                <span style="display: none" class="loading-spinner spinner-border spinner-border-sm"
                                      aria-hidden="true"></span>
                                <span style="display: none" class="loading-text" role="status">Laden...</span>
                            </button>
                        </div>
                    </form>
                    <div class="mt-3 text-muted small">
                        <span class="fw-bold">Betaling ID (Mollie):</span> {{ $order->mollie_payment_id ?? 'N/A' }} <br>
                        <span class="fw-bold">Betaling Status:</span> {{ $order->payment_status }}
                    </div>
                </div>
            </div>

            <div class="bg-white w-100 p-4 rounded mt-3">
                <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">person</span>Klantgegevens
                </h2>
                <div class="-body">
                    <p class="mb-1"><strong>Naam:</strong> {{ $order->first_name }} {{ $order->last_name }}</p>
                    <p class="mb-1"><strong>Email:</strong> <a href="mailto:{{ $order->email }}">{{ $order->email }}</a>
                    </p>
                    <p class="mb-1"><strong>Adres:</strong> {{ $order->address }}</p>
                    <p class="mb-1"><strong>Postcode / Stad:</strong> {{ $order->zipcode }} {{ $order->city }}</p>
                    <p class="mb-0"><strong>Land:</strong> {{ $order->country }}</p>
                </div>
            </div>

            <div class="bg-white w-100 p-4 rounded mt-3">
                <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">article_shortcut</span>Samenvatting
                </h2>
                <div class="-body">
                    <p class="mb-1"><strong>Order Datum:</strong> {{ $order->created_at->format('d-m-Y H:i') }}</p>
                    @if($order->created_at !== $order->updated_at)
                    <p class="mb-1"><strong>Order Bewerkt:</strong> {{ $order->updated_at->format('d-m-Y H:i') }}</p>
                    @endif
                        <p class="mb-1"><strong>Totaal Artikelen:</strong> {{ $order->items->sum('quantity') }}</p>
                    <p class="mb-1"><strong>Totaal
                            Betaald: </strong>&#8364;{{ number_format($order->total_amount, 2, ',', '.') }}</p>
                </div>
            </div>

            <div class="bg-white w-100 p-4 rounded mt-3">
                <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">picture_as_pdf</span>Download Factuur</h2>

                <div class="col-lg-8 mb-4 mb-lg-0">
                    <div class="border rounded p-2 h-100">
                        <iframe src="{{ route('admin.order.invoice.stream', $order->order_number) }}"
                                width="100%"
                                height="500px"
                                style="border: none;"
                                title="Factuur PDF">
                        </iframe>
                    </div>
                </div>


                <a href="{{ route('order.invoice', $order->order_number) }}" class="btn btn-primary mt-2">
                    <span class="material-symbols-rounded align-middle me-2">download</span> Download PDF
                </a>

            </div>


            <div class="d-flex flex-row flex-wrap mt-5 gap-2">
                <a href="{{ route('admin.orders') }}" class="btn btn-info text-white">Terug</a>
            </div>
        </div>
    </div>
@endsection
