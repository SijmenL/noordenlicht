@extends('layouts.dashboard')

@section('content')

    <div class="container col-md-11">
        <h1>Details Ticket #{{ substr($ticket->uuid, 0, 8) }}</h1>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item" aria-current="page"><a href="{{route('admin')}}">Dashboard</a></li>
                                <li class="breadcrumb-item" aria-current="page"><a href=" {{ route('admin.tickets.list') }}">Tickets</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ substr($ticket->uuid, 0, 8) }}...</li>
            </ol>
        </nav>

        @if(Session::has('error'))
            <div class="alert alert-danger" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <div class="d-flex flex-column gap-2">

            <div class="bg-white w-100 p-4 rounded mt-3">
                <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">confirmation_number</span>Ticket
                    Informatie</h2>
                <div class="-body mt-3">
                    <p class="mb-1"><strong>UUID:</strong> {{ $ticket->uuid }}</p>
                    <p class="mb-1"><strong>Activiteit:</strong> {{ $ticket->activity->title ?? 'Onbekend' }}</p>
                    <p class="mb-1"><strong>Evenement
                            Datum:</strong> {{ \Carbon\Carbon::parse($ticket->start_date)->format('d-m-Y H:i') }}</p>
                    <p class="mb-1"><strong>Gescand op:</strong>
                        @if($ticket->scanned_at)
                            {{ \Carbon\Carbon::parse($ticket->scanned_at)->format('d-m-Y H:i') }}
                        @else
                            <span class="text-muted fst-italic">Nog niet gescand</span>
                        @endif
                    </p>
                </div>
            </div>


            <div class="bg-white w-100 p-4 rounded mt-3">
                <div class="d-flex flex-row gap-2 align-items-center">
                    <h2 class="flex-row gap-3 align-items-center"><span
                            class="material-symbols-rounded me-2">paid</span>Status
                    </h2>
                    @php
                        $statusClass = match($ticket->status) {
                             'valid' => 'success',
                             'used' => 'secondary',
                             'pending' => 'warning',
                             'cancelled' => 'danger',
                             default => 'info'
                         };
                    @endphp
                    <span class="badge bg-{{ $statusClass }}">
                           @if($ticket->status == 'valid') Geldig
                        @elseif($ticket->status == 'used') Gebruikt
                        @elseif($ticket->status == 'pending') In afwachting
                        @elseif($ticket->status == 'warning') Waarschuwing
                        @elseif($ticket->status == 'cancelled') Niet geldig
                        @else
                            {{ ucfirst($ticket->status) }}
                        @endif
                                </span>
                </div>
                <div class="w-100">
                    <form action="{{ route('admin.tickets.details.update', $ticket->uuid) }}" method="POST"
                          class="row align-items-end w-100">
                        @csrf
                        <div class="d-flex flex-column">
                            <label for="status" class="col-md-4 col-form-label ">Wijzig Status</label>
                            <select id="status"
                                    class="w-100 form-select @error('status') is-invalid @enderror"
                                    name="status">
                                <option value="pending" {{ $ticket->status == 'pending' ? 'selected' : '' }}>In afwachting</option>
                                <option value="valid" {{ $ticket->status == 'valid' ? 'selected' : '' }}>Geldig</option>
                                <option value="cancelled" {{ $ticket->status == 'cancelled' ? 'selected' : '' }}>Niet geldig</option>
                                <option value="used" {{ $ticket->status == 'used' ? 'selected' : '' }}>Gebruikt</option>
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
                    <div class="mt-2 text-muted small">
                        <span class="material-symbols-rounded align-middle fs-6 me-1">info</span>
                        Tickets worden automatisch op 'Gebruikt' gezet na scannen.
                    </div>
                </div>
            </div>

            <div class="bg-white w-100 p-4 rounded mt-3">
                <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">person</span>Eigenaar</h2>
                @if($ticket->user)
                    <p class="text-muted">Account gekoppeld</p>

                    <div class="-body">
                        <p class="mb-1"><strong>Naam:</strong> {{ $ticket->user->name }}</p>
                        <p class="mb-1"><strong>Email:</strong> <a href="mailto:{{ $ticket->user->email }}">{{ $ticket->user->email }}</a>
                        </p>
                    </div>

                    <a href="{{ route('admin.account-management.details', $ticket->user->id) }}"
                       class="btn btn-outline-primary btn-sm mt-2">
                        <span class="material-symbols-rounded align-middle fs-6 me-1">open_in_new</span>
                        Bekijk gebruikers account
                    </a>
                @else
                    <p class="text-muted">Geen account gekoppeld (Gast)</p>

                    <div class="-body">
                        <p class="mb-1"><strong>Naam:</strong> {{ $ticket->order->first_name }} {{ $ticket->order->last_name }}</p>
                        <p class="mb-1"><strong>Email:</strong> <a href="mailto:{{ $ticket->order->email }}">{{ $ticket->order->email }}</a>
                        </p>
                        <p class="mb-1"><strong>Adres:</strong> {{ $ticket->order->address }}</p>
                        <p class="mb-1"><strong>Postcode / Stad:</strong> {{ $ticket->order->zipcode }} {{ $ticket->order->city }}</p>
                        <p class="mb-0"><strong>Land:</strong> {{ $ticket->order->country }}</p>
                    </div>
                @endif
            </div>

            <div class="bg-white w-100 p-4 rounded mt-3">
                <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">person</span>Ordergegevens</h2>
                @if($ticket->order)
                    <p class="mb-1"><strong>Order #:</strong> {{ $ticket->order->order_number }}</p>
                    <p class="mb-1">
                        <strong>Aankoopdatum:</strong> {{ $ticket->order->created_at->format('d-m-Y H:i') }}
                    </p>
                    <a href="{{ route('admin.orders.details', $ticket->order_id) }}"
                       class="btn btn-outline-primary btn-sm mt-2">
                        <span class="material-symbols-rounded align-middle fs-6 me-1">open_in_new</span>
                        Bekijk volledige bestelling
                    </a>
                @else
                    <span class="text-muted">Geen order gevonden.</span>
                @endif
            </div>

        <div class="bg-white w-100 p-4 rounded mt-3">
            <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">picture_as_pdf</span>Download</h2>

                <div class="col-lg-8 mb-4 mb-lg-0">
                    <div class="border rounded p-2 h-100">
                        <iframe src="{{ route('admin.tickets.stream', $ticket->id) }}"
                                width="100%"
                                height="500px"
                                style="border: none;"
                                title="Ticket PDF">
                        </iframe>
                    </div>
                </div>


                    <a href="{{ route('ticket.download', $ticket->uuid) }}" class="btn btn-primary mt-2">
                        <span class="material-symbols-rounded align-middle me-2">download</span> Download PDF
                    </a>

        </div>

        <div class="d-flex flex-row flex-wrap mt-5 gap-2 mb-5">
                            <a href="{{ route('admin.tickets.list') }}" class="btn btn-secondary text-white">Terug naar overzicht</a>
        </div>

    </div>
    </div>
@endsection
