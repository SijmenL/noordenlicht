@extends("layouts.dashboard")

@php
    use Carbon\Carbon;
    Carbon::setLocale('nl');
@endphp

@section("content")
    <div class="container col-md-11">

        {{-- Header Section --}}
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h1 class="fw-bold m-0">{{$greeting}}, {{ $name }}</h1>
                <p class="text-muted m-0 small">{{ $formattedDate }}</p>
            </div>
        </div>

        {{-- Notifications Section --}}
        @if($totalNotifications > 0)
            <div class="alert alert-warning w-100  mb-5" role="alert">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="material-symbols-rounded">notifications_active</span>
                    <h4 class="alert-heading fw-bold m-0 fs-5">
                        Je hebt nog {{$totalNotifications}} @if($totalNotifications !== 1)
                            openstaande taken
                        @else
                            openstaande taak
                        @endif
                    </h4>
                </div>
                <ul class="mb-0 ps-3">
                    @if($contact > 0)
                        <li>{{$contact}} @if($contact !== 1)
                                ongelezen contactformulieren
                            @else
                                ongelezen contactformulier
                            @endif</li>
                    @endif
                    @if($orders > 0)
                        <li>{{$orders}} @if($orders !== 1)
                                betaalde bestellingen te verwerken
                            @else
                                betaalde bestelling te verwerken
                            @endif</li>
                    @endif
                    @if($signup > 0)
                        <li>{{$signup}} @if($signup !== 1)
                                nieuwe aanmeldingen
                            @else
                                nieuwe aanmelding
                            @endif</li>
                    @endif
                </ul>
            </div>
        @endif

        {{-- Dashboard Grid --}}
        <div class="d-flex flex-column gap-5">

            <div class=" h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold m-0 d-flex align-items-center gap-2">
                        <span class="material-symbols-rounded text-primary">event</span>
                        Eerstvolgende Event
                    </h5>
                </div>
                <div class="-body p-4 d-flex flex-column">
                    @if($upcomingEvent)
                        <div class="mb-3 position-relative rounded-3 overflow-hidden" style="height: 150px;">
                            @if($upcomingEvent->image)
                                <img src="{{ asset('files/agenda/agenda_images/'.$upcomingEvent->image) }}"
                                     class="w-100 h-100 object-fit-cover" alt="Event">
                            @else
                                <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center">
                                    <span class="material-symbols-rounded text-muted fs-1">image</span>
                                </div>
                            @endif
                            <div class="position-absolute top-0 end-0 m-2">
                                <span
                                    class="badge bg-white text-dark shadow-sm">{{ $upcomingEvent->date_start->format('d M') }}</span>
                            </div>
                        </div>
                        <h5 class="fw-bold mb-1">{{ $upcomingEvent->title }}</h5>
                        <p class="text-muted small mb-3">
                            <span class="material-symbols-rounded align-middle fs-6 me-1">location_on</span>
                            {{ $upcomingEvent->location }}
                        </p>
                        <a href="{{ route('agenda.activity', ['id' => $upcomingEvent->id, 'startDate' => $upcomingEvent->date_start->format('Y-m-d')]) }}" class="btn btn-outline-primary w-100 rounded-pill mt-auto">Beheren</a>
                    @else
                        <div class="text-center py-5 text-muted">
                            <span class="material-symbols-rounded fs-1 d-block mb-2 opacity-50">event_busy</span>
                            Geen aankomende evenementen
                        </div>
                        <a href="{{ route('agenda.new') }}" class="btn btn-primary w-100 rounded-pill mt-auto">Nieuw
                            Event</a>
                    @endif
                </div>
            </div>


            <div class="h-100 border-0 shadow-sm rounded-4">
                <div
                    class="-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold m-0 d-flex align-items-center gap-2">
                        <span class="material-symbols-rounded text-success">shopping_cart</span>
                        Laatste Bestellingen
                    </h5>
                </div>
                <div class="-body p-0">
                    @if($latestOrders->isNotEmpty())
                        <div class="list-group list-group-flush py-2">
                            @foreach($latestOrders as $order)
                                <a href="{{ route('admin.orders.details', $order->id) }}"
                                   class="list-group-item list-group-item-action border-0 px-4 py-3">
                                    <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                        <span class="fw-bold text-dark">#{{ $order->order_number }}</span>
                                        <small class="text-muted">{{ $order->created_at->diffForHumans() }}</small>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="small text-muted text-truncate" style="max-width: 150px;">
                                            {{ $order->first_name }} {{ $order->last_name }}
                                        </div>
                                        @php
                                            $statusClass = match($order->status) {
                                                'paid' => 'success',
                                                'open', 'pending' => 'warning',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span
                                            class="badge bg-{{ $statusClass }} bg-opacity-10 text-{{ $statusClass }} rounded-pill">
                                                € {{ number_format($order->total_amount, 2, ',', '.') }}
                                            </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <p class="m-0">Nog geen bestellingen</p>
                        </div>
                    @endif
                </div>
                <div class="-footer bg-white border-0 p-4 pt-0">
                    <a href="{{ route('admin.orders') }}" class="btn btn-light w-100 rounded-pill text-muted">Alle
                        bestellingen</a>
                </div>
            </div>


            <div class=" h-100 border-0 shadow-sm rounded-4">
                <div
                    class="-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold m-0 d-flex align-items-center gap-2">
                        <span class="material-symbols-rounded text-info">mail</span>
                        Recente Berichten
                    </h5>
                </div>
                <div class="-body p-4 d-flex flex-column">
                    @if($latestContact)
                        <div class="d-flex align-items-center mb-3">
                            <div
                                class="bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3"
                                style="width: 45px; height: 45px; min-width: 45px;">
                                <span class="material-symbols-rounded text-info">person</span>
                            </div>
                            <div class="overflow-hidden">
                                <h6 class="fw-bold mb-0 text-truncate">{{ $latestContact->name }}</h6>
                                <small class="text-muted">{{ $latestContact->email }}</small>
                            </div>
                        </div>
                        <div class="bg-light rounded-3 p-3 mb-3 flex-grow-1">
                            <p class="small text-muted mb-0 fst-italic">
                                "{{ Str::limit($latestContact->message, 100) }}"
                            </p>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <small class="text-muted">{{ $latestContact->created_at->diffForHumans() }}</small>
                            <a href="{{ route('admin.contact.details', $latestContact->id) }}"
                               class="btn btn-sm btn-info rounded-pill px-3 text-white">Lezen</a>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                                <span
                                    class="material-symbols-rounded fs-1 d-block mb-2 opacity-50">mark_email_read</span>
                            Geen nieuwe berichten
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
