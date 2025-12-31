@extends('layouts.dashboard')

@section('content')
    <div class="container col-md-11">
        <h1>Scan tickets</h1>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item" aria-current="page"><a href="{{route('admin')}}">Dashboard</a></li>
                <li class="breadcrumb-item" aria-current="page"><a href=" {{ route('admin.tickets.list') }}">Tickets</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Scan tickets</li>
            </ol>
        </nav>

        <div class="bg-white w-100 p-4 rounded mt-3">
            <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">confirmation_number</span>Scan
                tickets</h2>

            <form id="auto-submit" action="{{ route('admin.tickets.check') }}" method="POST">
                @csrf

                {{-- Activity Selector --}}
                <div class="mb-3">
                    <div class="d-flex flex-column mb-3">
                        <label for="activity_id" class="col-md-4 col-form-label ">Selecteer evenement</label>
                    <select name="activity_id" id="activity_id" class="form-select" required>
                        @if($activities->isEmpty())
                            <option value="" disabled selected>Geen aankomende evenementen gevonden</option>
                        @else
                            @foreach($activities as $activity)
                                <option value="{{ $activity->id }}"
                                    {{-- LOGIC: Check Session/Variable first, then Old Input. Never check Ticket ID. --}}
                                    {{ (isset($selectedActivityId) && $selectedActivityId == $activity->id) || old('activity_id') == $activity->id ? 'selected' : '' }}>

                                    {{ $activity->title }} ({{ \Carbon\Carbon::parse($activity->date_start)->format('d-m-Y H:i') }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                    </div>
                </div>

                {{-- Barcode Input --}}
                <div class="input-group">
                    <label for="barcode" class="input-group-text" id="basic-addon1">
                        <span class="material-symbols-rounded">qr_code_scanner</span>
                    </label>
                    <input id="barcode" name="barcode" type="text" class="form-control"
                           placeholder="Klik hier en scan barcode..."
                           aria-label="Scan..." aria-describedby="basic-addon1" autofocus required autocomplete="off"
                           onchange="this.form.submit();">
                </div>
                <small class="text-muted">De scan wordt automatisch verstuurd na invoer.</small>
            </form>
        </div>

        {{-- Result Display --}}
        @if(isset($message))
            <div class="bg-white w-100 p-4 rounded mt-3 border {{ $alertType == 'success' ? 'border-success' : ($alertType == 'warning' ? 'border-warning' : 'border-danger') }}">
                <div class="d-flex align-items-center gap-2 {{ $alertType == 'success' ? 'text-success' : ($alertType == 'warning' ? 'text-warning' : ' text-danger') }}">
                    @if($alertType == 'success')
                        <span class="material-symbols-rounded fs-2">check_circle</span>
                    @elseif($alertType == 'warning')
                        <span class="material-symbols-rounded fs-2">warning</span>
                    @else
                        <span class="material-symbols-rounded fs-2">error</span>
                    @endif
                    <h2 class="mb-0 {{ $alertType == 'success' ? 'text-success' : ($alertType == 'warning' ? 'text-warning' : ' text-danger') }}">{{ $message }}</h2>
                </div>

                @if(isset($ticket))
                    <div class="-body mt-3">
                        <p class="mb-1"><strong>UUID:</strong> {{ $ticket->uuid }}</p>
                        <p class="mb-1"><strong>Activiteit op ticket:</strong> {{ $ticket->activity->title ?? 'Onbekend' }}</p>
                        <p class="mb-1"><strong>Gescand op:</strong>
                            @if($ticket->scanned_at)
                                {{ \Carbon\Carbon::parse($ticket->scanned_at)->format('d-m-Y H:i') }}
                            @else
                                <span class="text-muted fst-italic">Nog niet gescand</span>
                            @endif
                        </p>
                    </div>

                    <div class="mt-3 p-3 bg-light rounded">
                        @if($ticket->user)
                            <h5 class="fw-bold">Gekoppeld Account</h5>
                            <p class="mb-1"><strong>Naam:</strong> {{ $ticket->user->name }}</p>
                            <p class="mb-1"><strong>Email:</strong> {{ $ticket->user->email }}</p>
                            <a href="{{ route('admin.account-management.details', $ticket->user->id) }}"
                               class="btn btn-outline-primary btn-sm mt-2" target="_blank">
                                Bekijk account
                            </a>
                        @else
                            <h5 class="fw-bold">Gast (Geen account)</h5>
                            @if($ticket->order)
                                <p class="mb-1"><strong>Naam:</strong> {{ $ticket->order->first_name }} {{ $ticket->order->last_name }}</p>
                                <p class="mb-1"><strong>Email:</strong> {{ $ticket->order->email }}</p>
                                <p class="mb-0"><strong>Stad:</strong> {{ $ticket->order->city }}</p>
                            @endif
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>

    <script>
        // Keep focus on input for continuous scanning
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('barcode');
            if (input) {
                input.focus();
                // Ensure focus returns to input after a click anywhere else (optional, good for handheld scanners)
                document.addEventListener('click', function(e) {
                    if (e.target.tagName !== 'SELECT' && e.target.tagName !== 'A' && e.target.tagName !== 'BUTTON') {
                        input.focus();
                    }
                });
            }
        });
    </script>
@endsection
