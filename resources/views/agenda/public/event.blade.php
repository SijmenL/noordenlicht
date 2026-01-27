@extends('layouts.app')
@include('partials.editor')

@php
    use Carbon\Carbon;
    Carbon::setLocale('nl');
@endphp

@section('content')

    @if(Session::has('success') && session('success') == "CardAdded")
        <div id="cartConfirm" class="popup" style="margin-top: -92px;">
            <div class="popup-body">
                <div class="page">
                    <h2>Toegevoegd aan winkelwagen!</h2>
                    <p>Je ticket is aan de winkelwagen toegevoegd. Winkel verder of reken af via één van de onderstaande
                        knoppen.</p>
                    <div class="d-grid gap-2">
                        <button id="close" class="btn btn-success">Verder winkelen</button>
                        <a class="btn btn-secondary" href="{{ route("checkout") }}">Bekijk winkelwagen</a>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modalSave = document.getElementById('cartConfirm');
                const btnSaveAll = document.getElementById('close');

                btnSaveAll.addEventListener('click', function () {
                    modalSave.classList.add('d-none');
                });
            });
        </script>
    @endif

    <div class="container col-md-11 mt-5 mb-5">
        @if($activity !== null)

            @php
                $eventStart = Carbon::parse($activity->date_start);
                $eventEnd = Carbon::parse($activity->date_end);

                // --- Price Calculation Logic ---
                $allPrices = $activity->prices->map(fn($p) => $p->price);

                $basePrices = $allPrices->where('type', 0);
                $percentageAdditions = $allPrices->where('type', 1);
                $fixedDiscounts = $allPrices->where('type', 2);
                $extraCosts = $allPrices->where('type', 3);
                $percentageDiscounts = $allPrices->where('type', 4);

                $totalBasePrice = $basePrices->sum('amount');
                $preDiscountPrice = $totalBasePrice;

                // 1. Apply percentage additions
                foreach ($percentageAdditions as $percentage) {
                    $preDiscountPrice += $totalBasePrice * ($percentage->amount / 100);
                }

                $calculatedPrice = $preDiscountPrice;

                // 2. Apply percentage discounts
                $totalPercentageDiscounts = 0;
                foreach ($percentageDiscounts as $percentage) {
                    $calculatedPrice -= $preDiscountPrice * ($percentage->amount / 100);
                    $totalPercentageDiscounts += $percentage->amount;
                }

                // 3. Apply fixed amount discounts
                $calculatedPrice -= $fixedDiscounts->sum('amount');

                // 4. Add Extra Costs (Type 3)
                $calculatedPrice += $extraCosts->sum('amount');

                $hasDiscount = $fixedDiscounts->isNotEmpty() || $percentageDiscounts->isNotEmpty();

                $finalPrice = max(0, $calculatedPrice);
            @endphp

            @if($view === 'month')
                <a href="{{ route('agenda.public.month') }}"
                   class="btn m-4 d-flex flex-row gap-4 align-items-center justify-content-center"
                   style="margin-left: 25%; margin-right: 25%"
                ><span class="material-symbols-rounded me-2">arrow_back</span> <span>Terug naar het overzicht</span></a>
            @else
                <a href="{{ route('agenda.public.schedule') }}"
                   class="btn m-4 d-flex flex-row gap-4 align-items-center justify-content-center"
                   style="margin-left: 25%; margin-right: 25%"
                ><span class="material-symbols-rounded me-2">arrow_back</span> <span>Terug naar het overzicht</span></a>
            @endif

            <div>
                @if(Session::has('error'))
                    <div class="alert alert-danger" role="alert">
                        {{ session('error') }}
                    </div>
                @endif


                <div class="rounded-2 w-100 d-flex flex-column align-items-center">
                    <div class="w-100">

                        <div class="p-3 w-100 d-flex align-items-center flex-column">

                            <h1 class="text-center mt-5">{{ $activity->title }}</h1>
                            @if($eventStart->isSameDay($eventEnd))
                                <h2 class="text-center">{{ $eventStart->format('j') }} {{ $eventStart->translatedFormat('F') }}
                                    @ {{ $eventStart->format('H:i') }} - {{ $eventEnd->format('H:i') }}</h2>
                            @else
                                <h2 class="text-center">{{ $eventStart->format('d-m-Y') }}
                                    tot {{ $eventEnd->format('d-m-Y') }}</h2>
                            @endif

                            <div class="d-flex flex-row-responsive gap-5 w-100">
                                <div class="d-flex flex-column align-items-center justify-content-center w-100">
                                    @if(isset($activity->image))
                                        <img class="rounded shadow-sm zoomable-image"
                                             style="width: 100%; max-width: 400px; object-fit: cover; object-position: center;"
                                             alt="Activiteit Afbeelding"
                                             src="{{ asset('files/agenda/agenda_images/'.$activity->image) }}">
                                    @endif
                                    <div class="w-100 agenda-content mt-4"
                                         style="align-self: start">{!! $activity->content !!}
                                    </div>
                                </div>

                                @if($calculatedPrice > 0 || $allPrices->isNotEmpty())
                                    <div class="flex-shrink-0" style="min-width: 350px;">
                                        <div class="card shadow-lg border-0 rounded-4 overflow-hidden position-relative"
                                             style="border: 1px solid #e0e0e0;">
                                            <!-- Decorative Header Background -->
                                            <div
                                                style="height: 100px; background-image: url('{{ asset('img/logo/doodles/Treewoman2a.webp') }}'); background-size: cover; background-position: center; filter: brightness(0.9);"></div>

                                            <div class="card-body p-4 position-relative bg-white">
                                                <h3 class="fw-bold text-center mb-1">Tickets</h3>
                                                <p class="text-muted text-center small mb-4">Koop hier je
                                                    toegangsbewijs</p>

                                                @if($hasDiscount)
                                                    <div class="text-center mb-2">
                                                        <span
                                                            class="badge bg-danger rounded-pill px-3 py-1">Korting!</span>
                                                    </div>
                                                    <div class="text-center text-muted text-decoration-line-through">
                                                        &#8364;{{ number_format($preDiscountPrice + $extraCosts->sum('amount'), 2, ',', '.') }}
                                                    </div>
                                                @endif

                                                <div class="text-center mb-4">
                                                    <h1 class="display-4 fw-bold text-primary mb-0">
                                                        &#8364;{{ number_format($finalPrice, 2, ',', '.') }}
                                                    </h1>
                                                    <small class="text-muted">per stuk</small>
                                                </div>

                                                {{-- Detailed Breakdown --}}
                                                <div class="bg-light rounded-3 p-3 mb-4 small">
                                                    @if($percentageAdditions->isNotEmpty())
                                                        @foreach($percentageAdditions as $cost)
                                                            <div class="d-flex justify-content-between text-muted">
                                                                <span>incl. {{ $cost->name }} ({{ $cost->amount }}%)</span>
                                                                <span>+ &#8364;{{ number_format($totalBasePrice * ($cost->amount / 100), 2) }}</span>
                                                            </div>
                                                        @endforeach
                                                    @endif

                                                    @if($extraCosts->isNotEmpty())
                                                        @foreach($extraCosts as $cost)
                                                            <div class="d-flex justify-content-between text-muted">
                                                                <span>excl. {{ $cost->name }} ({{ $cost->amount }})</span>
                                                                <span>+ &#8364;{{ number_format($cost->amount, 2) }}</span>
                                                            </div>
                                                        @endforeach
                                                    @endif

                                                    @if($hasDiscount)
                                                        <hr class="my-2">
                                                        @foreach($percentageDiscounts as $disc)
                                                            <div class="d-flex justify-content-between text-success">
                                                                <span>{{ $disc->name }} (-{{ $disc->amount }}%)</span>
                                                                <span>- &#8364;{{ number_format($preDiscountPrice * ($disc->amount / 100), 2) }}</span>
                                                            </div>
                                                        @endforeach
                                                        @foreach($fixedDiscounts as $disc)
                                                            <div class="d-flex justify-content-between text-success">
                                                                <span>{{ $disc->name }}</span>
                                                                <span>- &#8364;{{ number_format($disc->amount, 2) }}</span>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                                @if(\Carbon\Carbon::parse($activity->date_end)->isPast())
                                                    <div class="alert alert-warning text-center mb-0 border-0 bg-light">
                                                        <h3 class="fw-bold text-dark mb-2">
                                                            <span class="material-symbols-rounded align-middle me-1">history</span>
                                                            Verlopen
                                                        </h3>
                                                        <p class="mb-0 text-muted">Dit evenement is al voorbij. Je kunt geen tickets meer kopen.</p>
                                                    </div>

                                                @elseif($activity->hasTicketsAvailable())
                                                    @if($activity->ticketsLeft() !== null)
                                                        @if($activity->ticketsLeft() == 1)
                                                            <small>Nog {{ $activity->ticketsLeft() }} ticket
                                                                beschikbaar.</small>
                                                        @else
                                                            <small>Nog {{ $activity->ticketsLeft() }} tickets
                                                                beschikbaar.</small>

                                                        @endif
                                                    @endif
                                                    <form action="{{ route('cart.add', ['id' => $activity->id]) }}"
                                                          method="POST">
                                                        @csrf
                                                        <input type="hidden" name="type" value="activity">
                                                        {{-- Pass the occurrence date. AgendaController sets $activity->date_start to the occurrence date --}}
                                                        <input type="hidden" name="start_date"
                                                               value="{{ $activity->date_start }}">

                                                        <div class="form-floating mb-3">
                                                            <input type="number" class="form-control" id="quantity"
                                                                   name="quantity" value="1" min="1"
                                                                   max="{{ $activity->ticketsLeft() }}">
                                                            <label for="quantity">Aantal tickets</label>
                                                        </div>

                                                        <button type="submit"
                                                                class="btn btn-primary btn-lg rounded-pill w-100 shadow fw-bold">
                                                            <span class="material-symbols-rounded align-middle me-2">shopping_cart</span>
                                                            In winkelmandje
                                                        </button>
                                                    </form>
                                                @else
                                                    <div
                                                        class="alert alert-secondary text-center mb-0 border-0 bg-light">
                                                        <h3 class="fw-bold text-danger mb-2">
                                                            <span class="material-symbols-rounded align-middle me-1">sentiment_dissatisfied</span>
                                                            Uitverkocht
                                                        </h3>
                                                        <p class="mb-0 text-muted">Helaas, er zijn geen tickets meer
                                                            beschikbaar
                                                            voor dit evenement.</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Description Section --}}
                        <div class="bg-light w-100 p-4 rounded mt-3">
                            <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">description</span>Beschrijving
                            </h2>
                            <div class="d-flex flex-row flex-wrap gap-5 justify-content-between">
                                <div>
                                    <h4 class="mb-2">Gegevens</h4>
                                    <div class="d-flex flex-column gap-1">
                                        @if($eventStart->isSameDay($eventEnd))
                                            <p class="m-0"><strong>Datum</strong></p>
                                            <p class="m-0">{{ $eventStart->format('j') }} {{ $eventStart->translatedFormat('F') }} </p>
                                            <p class="m-0"><strong>Tijd</strong></p>
                                            <p class="m-0">{{ $eventStart->format('H:i') }} </p>
                                        @else
                                            <p class="m-0"><strong>Begin</strong></p>
                                            <p class="m-0">{{ $eventStart->format('j') }} {{ $eventStart->translatedFormat('F') }}
                                                om {{ $eventStart->format('H:i') }}</p>
                                            <p class="m-0"><strong>Einde</strong></p>
                                            <p class="m-0">{{ $eventEnd->format('j') }} {{ $eventEnd->translatedFormat('F') }}
                                                om {{ $eventEnd->format('H:i') }}</p>
                                        @endif
                                    </div>
                                </div>
                                @if(isset($activity->location))
                                    <div>
                                        <h4 class="mb-2">Locatie</h4>
                                        <p class="m-0">{{ $activity->location }}</p>
                                    </div>
                                @endif
                                @if(isset($activity->organisator))
                                    <div>
                                        <h4 class="mb-2">Organisator</h4>
                                        <p class="m-0">{{ $activity->organisator }}</p>
                                    </div>
                                @endif
                                @if(isset($activity->price))
                                    <div>
                                        <h4 class="mb-2">Prijs indicatie</h4>
                                        @if($activity->price > 0)
                                            <p class="m-0">€{{ $activity->price }}</p>
                                        @else
                                            <p class="m-0">Deze activiteit is gratis!</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Form Elements Section --}}
                        @if($activity->formElements->count() > 0)
                            <div class="bg-light w-100 p-4 rounded mt-3">
                                <h2 class="flex-row gap-3"><span
                                        class="material-symbols-rounded me-2">app_registration</span>Inschrijfformulier
                                </h2>
                                <form action="{{ route('agenda.activity.submit', $activity->id) }}" method="POST">
                                    @csrf
                                    @foreach ($activity->formElements as $formElement)
                                        @php
                                            $options = $formElement->option_value ? explode(',', $formElement->option_value) : [];
                                            $oldValue = old('form_elements.' . $formElement->id);
                                        @endphp

                                        <div class="form-group mb-3">
                                            <label class="fw-bold mb-1"
                                                   for="formElement{{ $formElement->id }}">{{ $formElement->label }} @if($formElement->is_required)
                                                    <span class="text-danger">*</span>
                                                @endif</label>

                                            @switch($formElement->type)
                                                @case('text')
                                                @case('email')
                                                @case('number')
                                                @case('date')
                                                    <input type="{{ $formElement->type }}"
                                                           id="formElement{{ $formElement->id }}"
                                                           name="form_elements[{{ $formElement->id }}]"
                                                           class="form-control"
                                                           value="{{ $oldValue ?? '' }}"
                                                        {{ $formElement->is_required ? 'required' : '' }}>
                                                    @break

                                                @case('select')
                                                    <select id="formElement{{ $formElement->id }}"
                                                            name="form_elements[{{ $formElement->id }}]"
                                                            class="form-select w-100"
                                                        {{ $formElement->is_required ? 'required' : '' }}>
                                                        <option value="">Selecteer een optie</option>
                                                        @foreach ($options as $option)
                                                            <option value="{{ $option }}"
                                                                {{ $oldValue == $option ? 'selected' : '' }}>
                                                                {{ $option }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @break

                                                @case('radio')
                                                    @foreach ($options as $option)
                                                        <div class="form-check">
                                                            <input type="radio"
                                                                   id="formElement{{ $formElement->id }}_{{ $loop->index }}"
                                                                   name="form_elements[{{ $formElement->id }}]"
                                                                   value="{{ $option }}"
                                                                   class="form-check-input"
                                                                {{ $oldValue == $option ? 'checked' : '' }}
                                                                {{ $formElement->is_required ? 'required' : '' }}>
                                                            <label
                                                                for="formElement{{ $formElement->id }}_{{ $loop->index }}"
                                                                class="form-check-label">
                                                                {{ $option }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                    @break

                                                @case('checkbox')
                                                    @php
                                                        $oldValues = is_array($oldValue) ? $oldValue : [];
                                                    @endphp
                                                    @foreach ($options as $option)
                                                        <div class="form-check">
                                                            <input type="checkbox"
                                                                   id="formElement{{ $formElement->id }}_{{ $loop->index }}"
                                                                   name="form_elements[{{ $formElement->id }}][]"
                                                                   value="{{ $option }}"
                                                                   class="form-check-input">
                                                            <label
                                                                for="formElement{{ $formElement->id }}_{{ $loop->index }}"
                                                                class="form-check-label">{{ $option }}</label>
                                                        </div>
                                                    @endforeach
                                                    @break
                                            @endswitch
                                        </div>
                                    @endforeach

                                    <button type="submit"
                                            class="btn btn-success mt-3 d-flex align-items-center justify-content-center">
                                        Opslaan
                                    </button>
                                </form>
                            </div>
                        @endif

                    </div>
                </div>
                @else
                    {{-- Not Found View --}}
                    <div class="container col-md-11 text-center mt-5">
                        <h1>Activiteit niet gevonden</h1>
                        <p>Het item is mogelijk verwijderd of verplaatst.</p>
                        <a href="{{ route('agenda.public.month') }}" class="btn btn-primary text-white">Ga terug naar
                            het
                            overzicht</a>
                    </div>
                @endif
            </div>
    </div>
@endsection
