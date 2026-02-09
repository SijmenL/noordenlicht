@extends('layouts.dashboard')

@section('content')
    <div class="container col-md-11">
        <h1>Boekingen</h1>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item" aria-current="page"><a href="{{route('admin')}}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Boekingen</li>
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
                <div class="d-flex flex-row-responsive gap-2 align-items-center mb-3 w-100"
                     style="justify-items: stretch">
                    <div class="input-group">
                        <label for="search" class="input-group-text" id="basic-addon1">
                            <span class="material-symbols-rounded">search</span></label>
                        <input id="search" name="search" type="text" class="form-control"
                               placeholder="Zoeken op naam, accommodatie of ID"
                               aria-label="Zoeken" aria-describedby="basic-addon1" value="{{ request('search') }}"
                               onchange="this.form.submit();">
                    </div>
                    <div class="input-group">
                        <label for="status" class="input-group-text" id="basic-addon1">
                            <span class="material-symbols-rounded">filter_list</span></label>
                        <select id="status" name="status" class="form-select"
                                aria-label="Status" aria-describedby="basic-addon1" onchange="this.form.submit();">
                            <option value="all" {{ $status == 'all' || $status == '' ? 'selected' : '' }}>Alle boekingen</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Bevestigd</option>
                            <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>Gereserveerd</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>In afwachting</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Afgerond</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Geannuleerd</option>
                        </select>
                    </div>
                </div>
            </div>
        </form>

        @if($bookings->count() > 0)
            <div class="overflow-x-scroll" style="max-width: 100vw">
                <table class="table table-striped">
                    <thead class="thead-dark table-bordered table-hover">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Accommodatie</th>
                        <th scope="col">Gast</th>
                        <th scope="col">Periode</th>
                        <th scope="col">Status</th>
                        <th scope="col">Opties</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach ($bookings as $booking)
                        <tr id="{{ $booking->id }}" @if($booking->status == "cancelled") class="table-danger" @endif >
                            <th>#{{ $booking->id }}</th>
                            <td>
                                {{ $booking->accommodatie->name ?? 'Onbekend' }}
                            </td>
                            <td>
                                {{ $booking->user->name ?? 'Onbekend' }} {{ $booking->user->last_name ?? '' }}<br>
                                <small class="text-muted">{{ $booking->user->email ?? '' }}</small>
                            </td>
                            <td>
                                <div>{{ $booking->start->format('d-m-Y H:i') }}</div>
                                <div>{{ $booking->end->format('d-m-Y H:i') }}</div>
                            </td>
                            <td>
                                @php
                                    $statusClass = match($booking->status) {
                                        'confirmed', 'completed' => 'success',
                                        'pending', 'reserved' => 'warning',
                                        'cancelled' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusClass }}">
                                   @if($booking->status == 'pending') In afwachting @endif
                                    @if($booking->status == 'confirmed') Bevestigd @endif
                                    @if($booking->status == 'reserved') Gereserveerd @endif
                                    @if($booking->status == 'completed') Afgerond @endif
                                    @if($booking->status == 'cancelled') Geannuleerd @endif
                                </span>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Opties
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="{{ route('admin.bookings.details', ['id' => $booking->id]) }}" class="dropdown-item">
                                                Bekijk details
                                            </a>
                                        </li>
                                        @if($booking->order_id)
                                            <li>
                                                <a href="{{ route('admin.orders.details', ['id' => $booking->order_id]) }}" class="dropdown-item">
                                                    Bekijk gekoppelde order
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{ $bookings->links() }}
        @else
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <span class="material-symbols-rounded me-2">info</span>Geen boekingen gevonden...
            </div>
        @endif
    </div>
@endsection
