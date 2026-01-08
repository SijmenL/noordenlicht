@extends('layouts.app')

@section('content')
    <div class="container py-5 col-md-11">
        <h1>Bestellingen</h1>


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

{{--        <form id="auto-submit" method="GET">--}}
{{--            <div class="d-flex">--}}
{{--                <div class="d-flex flex-row-responsive gap-2 align-items-center mb-3 w-100"--}}
{{--                     style="justify-items: stretch">--}}
{{--                    <div class="input-group">--}}
{{--                        <label for="status" class="input-group-text" id="basic-addon1">--}}
{{--                            <span class="material-symbols-rounded">paid</span></label>--}}
{{--                        <select id="status" name="status" class="form-select"--}}
{{--                                aria-label="Status" aria-describedby="basic-addon1" onchange="this.form.submit();">--}}
{{--                            <option value="all" {{ $status == 'all' || $status == '' ? 'selected' : '' }}>Alle--}}
{{--                                bestellingen--}}
{{--                            </option>--}}
{{--                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Betaald</option>--}}

{{--                            <option value="pending" {{ request('status') == "pending" ? 'selected' : '' }}>In--}}
{{--                                afwachting--}}
{{--                            </option>--}}
{{--                            <option value="open" {{ request('status') == "open" ? 'selected' : '' }}>Niet betaald--}}
{{--                            </option>--}}
{{--                            <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Verzonden--}}
{{--                            </option>--}}
{{--                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>--}}
{{--                                Afgerond--}}
{{--                            </option>--}}
{{--                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>--}}
{{--                                Geannuleerd--}}
{{--                            </option>--}}
{{--                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Misgegaan--}}
{{--                            </option>--}}
{{--                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Verlopen--}}
{{--                            </option>--}}
{{--                        </select>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </form>--}}

        @if($orders->count() > 0)
            <div class="d-none d-md-flex row justify-content-between px-4 py-2 text-muted fw-bold text-uppercase small mb-2">
                <div class="col-md-3">Order & Datum</div>
                <div class="col-md-2">Status</div>
                <div class="col-md-2 text-end">Bedrag</div>
                <div class="col-md-2"></div>
            </div>

            <!-- Orders Loop -->
            <div class="d-flex flex-column gap-3">
                @foreach ($orders as $order)
                    @php
                        // Determine status color/logic
                        $statusColor = match($order->status) {
                            'completed', 'shipped' => 'success',
                            'paid' => 'info',
                            'open', 'pending' => 'warning',
                            'cancelled', 'failed', 'expired' => 'danger',
                            default => 'secondary'
                        };

                        // Status translation
                        $statusLabel = match($order->status) {
                            'pending' => 'In afwachting',
                            'open' => 'Niet betaald',
                            'paid' => 'Betaald',
                            'shipped' => 'Verzonden',
                            'completed' => 'Afgerond',
                            'cancelled' => 'Geannuleerd',
                            'failed' => 'Misgegaan',
                            'expired' => 'Verlopen',
                            default => $order->status
                        };
                    @endphp

                        <!-- Custom Order Item -->
                    <div class="bg-white border rounded position-relative overflow-hidden">
                        <!-- Status Strip (Left Border Color) -->
                        <div class="position-absolute top-0 bottom-0 start-0 bg-{{ $statusColor }}"
                             style="width: 6px;"></div>

                        <div class="p-3 p-md-4">
                            <div class="d-flex row justify-content-between align-items-center gy-3">

                                <!-- 1. Order # & Date -->
                                <div class="col-12 col-md-3 ps-4">
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center mb-1">
                                            <span
                                                class="material-symbols-rounded fs-5 me-2 text-primary">receipt_long</span>
                                            <span class="fw-bold fs-5 text-dark">#{{ $order->order_number }}</span>
                                        </div>
                                        <div class="d-flex align-items-center text-muted small">
                                            <span class="material-symbols-rounded fs-6 me-1">calendar_month</span>
                                            @if ($order->created_at !== $order->updated_at)
                                            <span>{{ $order->updated_at->format('d-m-Y') }}</span>
                                            <span class="mx-1">&bull;</span>
                                            <span>{{ $order->updated_at->format('H:i') }}</span>
                                            @else
                                                <span>{{ $order->created_at->format('d-m-Y') }}</span>
                                                <span class="mx-1">&bull;</span>
                                                <span>{{ $order->created_at->format('H:i') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. Status Badge -->
                                <div class="col-6 col-md-2 ps-4 ps-md-3">
                                    <span
                                        class="badge bg-{{ $statusColor }} bg-opacity-10 text-{{ $statusColor }} border border-{{ $statusColor }} border-opacity-25 rounded-pill px-3 py-2">
                                        {{ $statusLabel }}
                                    </span>
                                </div>

                                <!-- 4. Total Amount -->
                                <div class="col-6 col-md-2 text-end">
                                    <div class="d-md-none text-muted small mb-1">Totaal</div>
                                    <span
                                        class="fw-bold fs-5 text-dark">&#8364;{{ number_format($order->total_amount, 2, ',', '.') }}</span>
                                </div>

                                <!-- 5. Action Button -->
                                <div class="col-12 col-md-2 ">
                                    <a href="{{ route('user.orders.details', ['id' => $order->id]) }}"
                                       class="btn btn-outline-primary w-100 w-md-auto d-flex align-items-center justify-content-center gap-2">
                                        <span>Details</span>
                                        <span class="material-symbols-rounded fs-6">arrow_forward</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-4 d-flex justify-content-center">
                {{ $orders->links() }}
            </div>

        @else
            <!-- Empty State -->
            <div class="text-center py-5">
                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                     style="width: 80px; height: 80px;">
                    <span class="material-symbols-rounded text-muted fs-1">inbox</span>
                </div>
                <h3 class="h5 text-muted">Geen bestellingen gevonden</h3>
                <p class="text-muted small">Probeer een andere filter of wacht op nieuwe bestellingen.</p>
            </div>
        @endif
    </div>
@endsection
