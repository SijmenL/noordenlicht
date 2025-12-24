@extends('layouts.dashboard')

@section('content')
    <div class="container-fluid p-4">
        <h1>Ticket Scanner</h1>

        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('admin.tickets.check') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="barcode" class="form-label">Scan Barcode / Voer UUID in</label>
                        <input type="text" name="barcode" id="barcode" class="form-control form-control-lg" placeholder="Scan..." autofocus required autocomplete="off">
                        <small class="text-muted">Druk op enter om te zoeken</small>
                    </div>
                </form>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(isset($ticket))
            <div class="card {{ $alertType == 'success' ? 'border-success' : ($alertType == 'warning' ? 'border-warning' : 'border-danger') }}">
                <div class="card-header {{ $alertType == 'success' ? 'bg-success text-white' : ($alertType == 'warning' ? 'bg-warning' : 'bg-danger text-white') }}">
                    <h3 class="mb-0">{{ $message }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Activiteit</h5>
                            <p class="lead">{{ $ticket->activity->title }}</p>
                            <p><strong>Datum:</strong> {{ \Carbon\Carbon::parse($ticket->start_date)->format('d-m-Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <h5>Bezoeker</h5>
                            <p>{{ $ticket->user ? $ticket->user->name : 'Gast' }}</p>
                            <p class="text-muted small">Bestelling: {{ $ticket->order ? $ticket->order->order_number : 'N/A' }}</p>
                        </div>
                    </div>

                    @if($status === 'valid')
                        <hr>
                        <form action="{{ route('admin.tickets.checkin', $ticket->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg w-100">Inchecken</button>
                        </form>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <script>
        // Keep focus on input for continuous scanning
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('barcode');
            if(input) input.focus();
        });
    </script>
@endsection
