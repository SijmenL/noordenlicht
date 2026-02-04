@extends('layouts.dashboard')

@section('content')
    <div class="container col-md-11">
        <h1>Tickets</h1>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item" aria-current="page"><a href="{{route('admin')}}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tickets</li>
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

        <form id="auto-submit" method="GET">
            <div class="d-flex">
                <div class="d-flex flex-row-responsive justify-content-between gap-2 mb-3 w-100">
                    <div class="input-group">
                        <label for="search" class="input-group-text" id="basic-addon1">
                            <span class="material-symbols-rounded">search</span>
                        </label>
                        <input id="search" name="search" type="text" class="form-control"
                               placeholder="Zoeken op UUID, Activiteit of Eigenaar"
                               aria-label="Zoeken" aria-describedby="basic-addon1"
                               value="{{ $search }}" onchange="this.form.submit();">
                    </div>
                </div>
            </div>
        </form>

        @if($tickets->count() > 0)
            <div class=overflow-x-scroll style="max-width: 100vw">
                <table class="table table-striped">
                    <thead class="thead-dark table-bordered table-hover">
                    <tr>
                        <th scope="col">Ticket ID</th>
                        <th scope="col">Activiteit</th>
                        <th scope="col">Eigenaar</th>
                        <th scope="col">Datum</th>
                        <th scope="col">Status</th>
                        <th scope="col">Opties</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach ($tickets as $ticket)
                        <tr>
                            <th>
                                <span title="{{ $ticket->uuid }}">
                                    {{ substr($ticket->uuid, 0, 8) }}...
                                </span>
                            </th>
                            <td>
                                {{ $ticket->activity->title }}<br>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($ticket->start_date)->format('d-m-Y H:i') }}</small>
                            </td>

                            <td>
                                @if($ticket->user)
                                    {{ $ticket->user->name }}
                                @else
                                    {{ $ticket->order->first_name }} {{ $ticket->order->last_name }}

                                @endif
                            </td>
                            <td>{{ $ticket->created_at->format('d-m-Y') }}</td>
                            <td>
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
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Opties
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="{{ route('admin.tickets.details', $ticket->uuid) }}" class="dropdown-item">
                                                Bekijk details
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('ticket.download', $ticket->uuid) }}" class="dropdown-item">
                                                Download PDF
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{ $tickets->links() }}
        @else
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <span class="material-symbols-rounded me-2">info</span>Geen tickets gevonden...
            </div>
        @endif
    </div>
@endsection
