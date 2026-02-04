@extends('layouts.dashboard')

@section('content')

    <div class="container col-md-11">
        <h1>Boeking #{{ $booking->id }}</h1>

        @if($location != 'agenda')
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item" aria-current="page"><a href="{{route('admin')}}">Dashboard</a></li>
                    <li class="breadcrumb-item" aria-current="page"><a href="{{route('admin.bookings')}}">Boekingen</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">#{{ $booking->id }}</li>
                </ol>
            </nav>
        @else
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item" aria-current="page"><a href="{{route('admin')}}">Dashboard</a></li>
                    <li class="breadcrumb-item" aria-current="page"><a href="{{route('agenda.month')}}">Agenda</a></li>
                    <li class="breadcrumb-item active" aria-current="page">#{{ $booking->id }}</li>
                </ol>
            </nav>
        @endif

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

            {{-- Booking Details Section --}}
            <div class="bg-white w-100 p-4 rounded mt-3">
                <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">hotel</span>Verblijf Details</h2>

                <div class="mt-4">
                    <h4 class="fw-bold">{{ $booking->accommodatie->name ?? 'Onbekende Accommodatie' }}</h4>
                    <p class="text-muted">{{ $booking->accommodatie->type ?? '' }}</p>

                    <div class="mt-4">
                        <div class="d-flex flex-column border-bottom py-2">
                            <span class="fw-bold">Aankomst:</span>
                            <span>{{ $booking->start->format('d-m-Y H:i') }}</span>
                        </div>
                        <div class="d-flex flex-column border-bottom py-2">
                            <span class="fw-bold">Vertrek:</span>
                            <span>{{ $booking->end->format('d-m-Y H:i') }}</span>
                        </div>
                        <div class="d-flex flex-column border-bottom py-2">
                            <span class="fw-bold">Duur:</span>
                            <span>{{ $booking->start->diffInHours($booking->end) }} uur</span>
                        </div>

                        <div class="d-flex flex-column border-bottom py-2">
                            <span class="fw-bold">Opmerkingen:</span>
                            <span>{{ $booking->comment }}</span>
                        </div>

                    </div>
                        @if($booking->public == "1")
                            <div class="p-2 bg-light rounded-2 m-1">
                                <div class="d-flex  flex-column border-bottom py-2">
                                    <span class="fw-bold">Openbare beschrijving:</span>
                                    <div style="overflow-wrap: break-word; word-break: break-word;">{!! $booking->activity_description !!}</div>
                                </div>
                                <div class="d-flex flex-column border-bottom py-2">
                                    <span class="fw-bold">Openbare link:</span>
                                    <a style="overflow-wrap: break-word; word-break: break-word;" href="{{ Str::startsWith($booking->external_link, ['http://', 'https://']) ? $booking->external_link : 'https://' . $booking->external_link }}" target="_blank">
                                        {{ $booking->external_link }}
                                    </a>
                                </div>
                            </div>
                        @endif

                    @if($booking->order->items->count() > 1)
                        <div class="mt-3">
                            <h6 class="fw-bold">Geboekte Extra's:</h6>
                            <ul class="list-group list-group-flush">
                                @php
                                    $processedSubmittedIds = []; // Track displayed responses to avoid duplicates
                                @endphp
                                @foreach($booking->order->items as $item)
                                    {{-- Skip the main accommodation item --}}
                                    @if(str_contains($item->product_name, $booking->accommodatie->name))
                                        @continue
                                    @endif

                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div class="w-100">
                                            <span>{{ $item->quantity }}x {{ $item->product_name }}</span>

                                            {{-- Form Responses for this item --}}
                                            @php
                                                $itemResponses = collect();
                                                $candidates = collect();

                                                if ($item->product_id) {
                                                    // Match responses by Product ID
                                                    $candidates = $formResponses->where('product_id', $item->product_id);
                                                } else {
                                                    // Match responses by Activity
                                                    $activityResponses = $formResponses->where('location', 'activity');
                                                    foreach($activityResponses->groupBy('activity_id') as $actId => $responses) {
                                                         $activity = $responses->first()->activity ?? null;
                                                         if($activity && str_contains($item->product_name, $activity->title)) {
                                                             $candidates = $responses;
                                                             break;
                                                         }
                                                    }
                                                }

                                                // Distribute responses to this item line based on quantity and availability
                                                if($candidates->isNotEmpty()) {
                                                    $groupedCandidates = $candidates->groupBy('submitted_id');
                                                    $setsNeeded = $item->quantity;

                                                    foreach($groupedCandidates as $subId => $group) {
                                                        if($setsNeeded <= 0) break;

                                                        if(!in_array($subId, $processedSubmittedIds)) {
                                                            $itemResponses = $itemResponses->merge($group);
                                                            $processedSubmittedIds[] = $subId;
                                                            $setsNeeded--;
                                                        }
                                                    }
                                                }
                                            @endphp

                                            {{-- 3. Display Form Data (if found) --}}
                                            @if($itemResponses->isNotEmpty())
                                                <table
                                                    class="table table-sm table-bordered bg-white small mb-0 w-100">
                                                    @foreach($itemResponses->groupBy('submitted_id') as $index => $group)
                                                        {{-- Header for multiple sets (e.g. 2 tickets = 2 entries) --}}
                                                        @if($itemResponses->groupBy('submitted_id')->count() > 1)
                                                            <thead class="table-light">
                                                            <tr>
                                                                <th colspan="2" class="text-muted">
                                                                    Inschrijving {{ $loop->iteration }}</th>
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
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Guest Details --}}
            <div class="bg-white w-100 p-4 rounded mt-3">
                <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">person</span>Gastgegevens</h2>
                <div class="-body">
                    <p class="mb-1">
                        <strong>Naam:</strong> {{ $booking->user->name ?? '-' }} {{ $booking->user->last_name ?? '' }}
                    </p>
                    <p class="mb-1"><strong>Email:</strong> <a
                            href="mailto:{{ $booking->user->email ?? '' }}">{{ $booking->user->email ?? '-' }}</a></p>
                    <p class="mb-1">
                        <strong>Adres:</strong> {{ $booking->user->address ?? ($booking->order->address ?? '-') }}</p>
                    <p class="mb-1"><strong>Postcode /
                            Stad:</strong> {{ $booking->user->zipcode ?? ($booking->order->zipcode ?? '-') }} {{ $booking->user->city ?? ($booking->order->city ?? '') }}
                    </p>
                    <a class="btn btn-outline-primary" href="{{ route('admin.orders.details', $booking->order_id) }}">Bekijk
                        bestelling</a>
                </div>
            </div>

            {{-- Status Management Section --}}
            <div class="bg-white w-100 p-4 rounded mt-3">
                <div class="d-flex flex-row gap-2 align-items-center">
                    <h2 class="flex-row gap-3 align-items-center"><span
                            class="material-symbols-rounded me-2">sync_alt</span>Status</h2>
                    @php
                        $statusClass = match($booking->status) {
                            'confirmed', 'completed' => 'success',
                            'pending' => 'warning',
                            'cancelled' => 'danger',
                            default => 'secondary'
                        };
                    @endphp
                    <span class="badge bg-{{ $statusClass }}">
                        @if($booking->status == 'pending')
                            In afwachting
                        @endif
                        @if($booking->status == 'confirmed')
                            Bevestigd
                        @endif
                        @if($booking->status == 'completed')
                            Afgerond
                        @endif
                        @if($booking->status == 'cancelled')
                            Geannuleerd
                        @endif
                    </span>
                </div>
                <div class="w-100">
                    <form action="{{ route('admin.bookings.details.update', ['id' => $booking->id]) }}" method="POST"
                          class="row align-items-end w-100">
                        @csrf
                        <div class="d-flex flex-column">
                            <label for="status" class="col-md-4 col-form-label">Wijzig Status</label>
                            <select id="status" class="w-100 form-select @error('status') is-invalid @enderror"
                                    name="status">
                                <option value="pending" {{ $booking->status == 'pending' ? 'selected' : '' }}>In
                                    afwachting
                                </option>
                                <option value="confirmed" {{ $booking->status == 'confirmed' ? 'selected' : '' }}>
                                    Bevestigd
                                </option>
                                <option value="completed" {{ $booking->status == 'completed' ? 'selected' : '' }}>
                                    Afgerond
                                </option>
                                <option value="cancelled" {{ $booking->status == 'cancelled' ? 'selected' : '' }}>
                                    Geannuleerd
                                </option>
                            </select>
                            @error('status')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="d-flex flex-row gap-2 align-items-center mt-3">
                            <button onclick="this.disabled=true;this.closest('form').submit();" class="btn btn-success">
                                Status aanpassen
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="d-flex flex-row flex-wrap mt-5 gap-2">
                <a href="{{ route('admin.bookings') }}" class="btn btn-info text-white">Terug</a>
            </div>
        </div>
    </div>
@endsection
