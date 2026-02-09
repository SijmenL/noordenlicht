@extends('layouts.dashboard')

@section('content')
    <div class="container col-md-11">
        <h1>Bestellingen</h1>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item" aria-current="page"><a href="{{route('admin')}}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Bestellingen</li>
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
                               placeholder="Zoeken op ordernummer, naam of e-mail"
                               aria-label="Zoeken" aria-describedby="basic-addon1" value="{{ request('search') }}"
                               onchange="this.form.submit();">
                    </div>
                    <div class="input-group">
                        <label for="status" class="input-group-text" id="basic-addon1">
                            <span class="material-symbols-rounded">paid</span></label>
                        <select id="status" name="status" class="form-select"
                                aria-label="Status" aria-describedby="basic-addon1" onchange="this.form.submit();">
                            <option value="all" {{ $status == 'all' || $status == '' ? 'selected' : '' }}>Alle bestellingen</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Betaald</option>

                            <option value="pending" {{ request('status') == "pending" ? 'selected' : '' }}>In afwachting</option>
                            <option value="open" {{ request('status') == "open" ? 'selected' : '' }}>Niet betaald</option>
                            <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Verzonden</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Afgerond</option>
                            <option value="lunch_later" {{ request('status') == 'lunch_later' ? 'selected' : '' }}>Ruimte betaald, toevoegingen niet</option>
                            <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Geannuleerd</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Misgegaan</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Verlopen</option>
                        </select>
                    </div>
                </div>
            </div>
        </form>

        @if($orders->count() > 0)
            <div class="overflow-x-scroll" style="max-width: 100vw">
                <table class="table table-striped">
                    <thead class="thead-dark table-bordered table-hover">
                    <tr>
                        <th scope="col">Order #</th>
                        <th scope="col">Klant</th>
                        <th scope="col">Status</th>
                        <th scope="col">Totaal</th>
                        <th scope="col">Datum</th>
                        <th scope="col">Opties</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach ($orders as $order)
                        <tr id="{{ $order->id }}" @if($order->status == "paid") class="table-info" @endif @if($order->status == "failed" || $order->status == "expired") class="table-danger" @endif >
                            <th>{{ $order->order_number }}</th>
                            <td>
                                {{ $order->first_name }} {{ $order->last_name }}<br>
                                <small class="text-muted">{{ $order->email }}</small>
                            </td>
                            <td>
                                @php
                                    $statusClass = match($order->status) {
                                        'completed', 'shipped', 'reservation' => 'success',
                                        'paid', 'lunch_later' => 'info',
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
                                   @if($order->status == 'lunch_later') Ruimte betaald, toevoegingen niet @endif
                                   @if($order->status == 'cancelled') Geannuleerd @endif
                                   @if($order->status == 'failed') Misgegaan @endif
                                   @if($order->status == 'expired') Verlopen @endif
                                   @if($order->status == 'reservation') Admin accommodatie reservering @endif
                                </span>
                            </td>
                            <td>&#8364;{{ number_format($order->total_amount, 2, ',', '.') }}</td>

                            <td>{{ $order->created_at->format('d-m-Y H:i') }} @if($order->created_at !== $order->updated_at) ({{ $order->updated_at->format('d-m-Y H:i') }}) @endif</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Opties
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="{{ route('admin.orders.details', ['id' => $order->id]) }}" class="dropdown-item">
                                                Bekijk details
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
            {{ $orders->links() }}
        @else
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <span class="material-symbols-rounded me-2">info</span>Geen bestellingen gevonden...
            </div>
        @endif
    </div>
@endsection
