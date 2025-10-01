@extends('layouts.contact')
@include('partials.editor')

@php
    use Carbon\Carbon;
    Carbon::setLocale('nl');
@endphp



@section('content')

    @if($activity !== null)

        @php
            $eventStart = Carbon::parse($activity->date_start);
            $eventEnd = Carbon::parse($activity->date_end);

            $eventMonth = $eventStart->translatedFormat('F');
        @endphp

        <a @if($view === 'month') onclick="{breakOut('month')}"
           @else onclick="{breakOut('agenda')}"
           @endif class="btn m-4 d-flex flex-row gap-4 align-items-center justify-content-center"
           style="margin-left: 25%; margin-right: 25%"
        >
            <span class="material-symbols-rounded me-2">arrow_back</span> <span>Terug naar het overzicht</span></a>

        <div class="mt-2" style="margin-left: 10%; margin-right: 10%">
            @if(Session::has('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            <div class="bg-white rounded-2 w-100 d-flex flex-column align-items-center">
                <div class="bg-light w-100 p-4 rounded">

                    <div class="p-3 w-100 d-flex align-items-center flex-column" style="transform: translateY(-50px)">

                        <h1 class="text-center mt-5">{{ $activity->title }}</h1>
                        @if($eventStart->isSameDay($eventEnd))
                            <h2 class="text-center">{{ $eventStart->format('j') }} {{ $eventStart->translatedFormat('F') }}
                                @ {{ $eventStart->format('H:i') }} - {{ $eventEnd->format('H:i') }}</h2>
                        @else
                            <h2 class="text-center">{{ $eventStart->format('d-m-Y') }}
                                tot {{ $eventEnd->format('d-m-Y') }}</h2>
                        @endif
                        @if(isset($activity->price))
                            @if($activity->price > 0)
                                <h3 class="text-center">€{{ $activity->price }}</h3>
                            @else
                                <h3 class="text-center">Deze activiteit is gratis!</h3>
                            @endif
                        @endif
                        @if(isset($activity->image))
                            <img class="mt-3"
                                 style="width: 100%; max-width: 800px; object-fit: cover; object-position: center;"
                                 alt="Activiteit Afbeelding"
                                 src="{{ asset('files/agenda/agenda_images/'.$activity->image) }}">
                        @endif

                        <div class="mt-4 w-100 agenda-content" style="align-self: start">{!! $activity->content !!}</div>
                    </div>

                </div>
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
                                <h4 class="mb-2">Prijs</h4>
                                @if($activity->price > 0)
                                    <p class="m-0">€{{ $activity->price }}</p>
                                @else
                                    <p class="m-0">Deze activiteit is gratis!</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                @if($activity->formElements->count() > 0)
                    <div class="bg-light w-100 p-4 rounded mt-3">
                        <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">app_registration</span>Inschrijfformulier
                        </h2>
                        <form action="{{ route('agenda.activity.submit', $activity->id) }}" method="POST">
                            @csrf
                            @foreach ($activity->formElements as $formElement)
                                @php
                                    $options = $formElement->option_value ? explode(',', $formElement->option_value) : [];
                                    $oldValue = old('form_elements.' . $formElement->id);
                                @endphp

                                <div class="form-group">
                                    <label
                                        for="formElement{{ $formElement->id }}">{{ $formElement->label }} @if($formElement->is_required)
                                            <span class="required-form">*</span>
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
                                                    <label for="formElement{{ $formElement->id }}_{{ $loop->index }}"
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
                                                    <label for="formElement{{ $formElement->id }}_{{ $loop->index }}"
                                                           class="form-check-label">{{ $option }}</label>
                                                </div>
                                            @endforeach
                                            @break
                                    @endswitch

                                    @if ($errors->has('form_elements.' . $formElement->id))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('form_elements.' . $formElement->id) }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach

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
                                class="btn btn-success mt-3 flex flex-row align-items-center justify-content-center">
                                <span class="button-text">Opslaan</span>
                                <span style="display: none" class="loading-spinner spinner-border spinner-border-sm" aria-hidden="true"></span>
                                <span style="display: none" class="loading-text" role="status">Laden...</span>
                            </button>
                        </form>
                    </div>
                @endif

            </div>

        </div>
    @else
        <div class="header" style="background-image: linear-gradient(rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.5)), url({{ asset('/files/agenda/banner.jpg') }})">
            <div>
                <p class="header-title">Geen activiteit gevonden</p>
            </div>
        </div>
        <div class="container col-md-11">
            <h1>We hebben geen activiteit gevonden</h1>
            <p>Het item is mogelijk verwijderd of verplaatst.</p>

            <button onclick="breakOut2()" class="btn btn-primary text-white">Ga terug naar het overzicht</button>

            <script>
                function breakOut2() {
                    window.parent.location.href = 'https://waterscoutingmhg.nl/over-onze-club/activiteiten';
                }
            </script>
        </div>

    @endif
@endsection

