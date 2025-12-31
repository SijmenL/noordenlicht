@extends('layouts.dashboard')
@include('partials.editor')

@vite(['resources/js/texteditor.js', 'resources/js/search-user.js', 'resources/css/texteditor.css'])

@php
    use Carbon\Carbon;
    Carbon::setLocale('nl');
@endphp



@section('content')
    <div id="popUp" class="popup" style="display: none; z-index: 99999; top: 0; left: 0; position: fixed">
        <div class="popup-body">
            <div class="page">
                <h2>Inschrijfformulier</h2>
                <p>Je kan de volgende elementen toevoegen aan een inschrijfformulier:</p>
                <div class="d-flex flex-column gap-2 w-100">
                    <div class="d-flex flex-row gap-4 justify-content-between">
                        <label for="info1" class="col-form-label ">Tekst</label>
                        <input id="info1" class="form-control" type="text" value="Lorum ipsum">
                    </div>
                    <div class="d-flex flex-row gap-4 justify-content-between">
                        <label for="info2" class="col-form-label ">Email</label>
                        <input id="info2" class="form-control" type="email" value="administratie@waterscoutingmhg.nl">
                    </div>
                    <div class="d-flex flex-row gap-4 justify-content-between">
                        <label for="info3" class="col-form-label ">Nummer</label>
                        <input id="info3" class="form-control" type="number" value="42">
                    </div>
                    <div class="d-flex flex-row gap-4 justify-content-between">
                        <label for="info4" class="col-form-label ">Datum</label>
                        <input id="info4" class="form-control" type="date" value="2003-11-12">

                    </div>
                    <div class="d-flex flex-row gap-4 justify-content-between">
                        <label for="info5" class="col-form-label ">Dropdown</label>
                        <select id="info5" class="form-select">
                            <option>Selecteer een optie</option>
                            <option>Optie 1</option>
                            <option>Optie 2</option>
                        </select>
                    </div>
                    <div class="d-flex flex-row gap-4 justify-content-between">
                        <label for="info6" class="col-form-label ">Radio</label>
                        <input name="info6" id="info6" class="form-check-input" type="radio" checked="checked">
                        <label for="info7" class="col-form-label ">Radio</label>
                        <input name="info6" id="info7" class="form-check-input" type="radio">
                    </div>
                    <div class="d-flex flex-row gap-4 justify-content-between">
                        <label for="info8" class="col-form-label ">Checkbox</label>
                        <input name="info8" id="info8" class="form-check-input" type="checkbox" checked="checked">
                        <label for="info9" class="col-form-label ">Checkbox</label>
                        <input name="info8" id="info9" class="form-check-input" type="checkbox" checked="checked">
                    </div>
                </div>
            </div>
            <div class="button-container">
                <a id="close-popup" class="btn btn-outline-danger"><span
                        class="material-symbols-rounded">close</span></a>
            </div>
        </div>
    </div>

    <div class="container col-md-11">
        @if(!isset($lesson))
            <h1>Evenement toevoegen</h1>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    @if($view === "month")
                        <li class="breadcrumb-item"><a
                                href="{{ route('agenda.month', ['month' => $monthOffset, 'all' => $wantViewAll]) }}">Agenda</a>
                        </li>
                    @else
                        <li class="breadcrumb-item"><a
                                href="{{ route('agenda.schedule', ['month' => $monthOffset, 'all' => $wantViewAll]) }}">Agenda</a>
                        </li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">Evenement toevoegen</li>
                </ol>
            </nav>
        @else
            <h1 class="">Nieuw agendapunt</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('lessons') }}">Lessen</a></li>
                <li class="breadcrumb-item"><a
                        href="{{ route('lessons.environment.lesson', $lesson->id) }}">{{ $lesson->title }}</a>
                </li>
                @if($view === "month")
                    <li class="breadcrumb-item"><a
                            href="{{ route('agenda.month', ['lessonId' => $lesson->id, 'month' => $monthOffset, 'all' => $wantViewAll]) }}">Planning</a>
                    </li>
                @else
                    <li class="breadcrumb-item"><a
                            href="{{ route('agenda.schedule', ['lessonId' => $lesson->id, 'month' => $monthOffset, 'all' => $wantViewAll]) }}">Planning</a>
                    </li>
                @endif
                <li class="breadcrumb-item active" aria-current="page">Nieuw agendapunt</li>
            </ol>
        @endif

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

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-light rounded-2 p-3">
            <div class="container">
                <form method="POST" action="{{ route('agenda.new.create') }}" enctype="multipart/form-data">
                    @csrf

                    <input type="hidden" name="prices_to_add" id="prices_to_add" value="">


                    <div class="mt-4">
                        <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">event</span>Algemene
                            Informatie</h2>
                        <div class="d-flex flex-column">
                            <label for="title" class="col-md-4 col-form-label ">Titel <span
                                    class="required-form">*</span></label>
                            <input name="title" type="text" class="form-control" id="title" value="{{ old('title') }}"
                            >
                            @error('title')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="">
                            <label for="image" class="col-md-4 col-form-label ">Coverafbeelding</label>
                            <div class="d-flex flex-row-responsive gap-4 align-items-center justify-content-center">
                                <input class="form-control mt-2 col" id="image" type="file" name="image"
                                       accept="image/*">
                                @error('image')
                            </div>
                            <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="text-input">Beschrijving</label>
                        <div class="editor-parent">
                            @yield('editor')
                            <div id="text-input" contenteditable="true" name="text-input"
                                 class="text-input">{!! old('content') !!}</div>
                            <small id="characters"></small>
                        </div>

                        <input id="content" name="content" type="hidden" value="{{ old('content') }}">

                        @error('content')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">date_range</span>Datum &
                            Tijd</h2>
                        <div class="d-flex flex-row-responsive gap-2 justify-content-between align-items-center">
                            <div class="w-100">
                                <label for="date_start" class="col-md-4 col-form-label ">Start datum en tijd <span
                                        class="required-form">*</span></label>
                                <input id="date_start" value="{{ old('date_start') }}" type="datetime-local"
                                       class="form-control @error('date_start') is-invalid @enderror" name="date_start">
                                @error('date_start')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="w-100">
                                <label for="date_end" class="col-md-4 col-form-label ">Eind datum en tijd <span
                                        class="required-form">*</span></label>
                                <input id="date_end" value="{{ old('date_end') }}" type="datetime-local"
                                       class="form-control @error('date_end') is-invalid @enderror" name="date_end">
                                @error('date_end')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="w-100">
                            <label for="reoccurrence" class="col-form-label">
                                Herhaal dit evenement
                                <span class="required-form">*</span>
                            </label>
                            <select id="reoccurrence" class="form-select @error('reoccurrence') is-invalid @enderror"
                                    name="reoccurrence">
                                <option value="never" selected>Nooit</option>
                                <option value="daily">Dagelijks</option>
                                <option value="weekly">Wekelijks</option>
                                <option value="monthly">Maandelijks</option>
                            </select>
                            @error('reoccurrence')
                            <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
                            @enderror
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const reoccurrenceSelect = document.getElementById('reoccurrence');

                            // Function to check value and do something
                            function handleReoccurrenceChange() {
                                const value = reoccurrenceSelect.value;

                                if (value !== 'never') {
                                    document.getElementById('custom-form').classList.add('d-none');
                                } else {
                                    document.getElementById('custom-form').classList.remove('d-none');
                                }
                            }

                            // Run on initial load
                            handleReoccurrenceChange();

                            // Run when the value changes
                            reoccurrenceSelect.addEventListener('change', handleReoccurrenceChange);
                        });
                    </script>

                    @if(!isset($lesson))
                        <div class="mt-4" id="custom-form">
                            <div class="d-flex flex-row-responsive justify-content-between align-items-center">
                                <h2 class="flex-row gap-3"><span
                                        class="material-symbols-rounded me-2">app_registration</span>Inschrijfformulier
                                </h2>
                                <a id="help-button"
                                   class="btn btn-outline-dark d-flex align-items-center justify-content-center"
                                   style="border: none">
                                    <span class="material-symbols-rounded" style="font-size: xx-large">help</span>
                                </a>
                            </div>
                            <p>Bij sommige evenementen is het nodig om een inschrijfformulier toe te voegen,
                                zoals bijvoorbeeld bij de aanmeldingen voor een bosklusdag. Klik op <span
                                    id="help-button2" class="material-symbols-rounded"
                                    style="transform: translateY(7px); cursor: pointer">help</span> voor meer
                                informatie.
                            </p>

                            <script>
                                let helpButton = document.getElementById('help-button');
                                let helpButton2 = document.getElementById('help-button2');
                                let body = document.getElementById('app');
                                let html = document.querySelector('html');
                                let popUp = document.getElementById('popUp');


                                helpButton.addEventListener('click', function () {
                                    openPopup();
                                });

                                helpButton2.addEventListener('click', function () {
                                    openPopup();
                                });

                                closeButton = document.getElementById('close-popup');
                                closeButton.addEventListener('click', closePopup);

                                function openPopup() {
                                    let scrollPosition = window.scrollY;
                                    html.classList.add('no-scroll');
                                    window.scrollTo(0, scrollPosition);
                                    popUp.style.display = 'flex';
                                }

                                function closePopup() {
                                    popUp.style.display = 'none';
                                    html.classList.remove('no-scroll');
                                }

                            </script>
                            <p>Druk op de knop "Voeg veld toe" om een invoerveld toe te voegen.</p>

                            <div id="form-elements"
                                 class="d-flex flex-column bg-info p-2 gap-2 m-2 rounded @if(!old('form_labels'))d-none @endif">
                                @if(old('form_labels'))
                                    @foreach(old('form_labels') as $index => $label)
                                        <div class="d-flex flex-column gap-2 bg-white rounded p-4 align-items-start"
                                             id="formElement{{ $index }}">
                                            <button type="button" class="btn btn-outline-danger align-self-end"
                                                    onclick="removeFormElement(${fields})">Verwijder veld
                                            </button>
                                            <label for="fieldLabel{{ $index }}">Veldlabel (bijvoorbeeld: Naam,
                                                Achternaam,
                                                Adres)</label>
                                            <input id="fieldLabel{{ $index }}" class="form-control" type="text"
                                                   name="form_labels[]" value="{{ $label }}">

                                            <label for="fieldType{{ $index }}">Type veld (wat je veld accepteerd,
                                                bijvoorbeeld "Tekst" als je een naam wilt of "Dropdown" bij een lijst
                                                tijdsloten)</label>
                                            <select id="fieldType{{ $index }}" class="form-control" name="form_types[]"
                                                    onchange="handleFieldTypeChange(this, {{ $index }})">
                                                <option
                                                    value="text" {{ old('form_types')[$index] == 'text' ? 'selected' : '' }}>
                                                    Tekst
                                                </option>
                                                <option
                                                    value="email" {{ old('form_types')[$index] == 'email' ? 'selected' : '' }}>
                                                    E-mail (geldig e-mail adres)
                                                </option>
                                                <option
                                                    value="number" {{ old('form_types')[$index] == 'number' ? 'selected' : '' }}>
                                                    Getal
                                                </option>
                                                <option
                                                    value="date" {{ old('form_types')[$index] == 'date' ? 'selected' : '' }}>
                                                    Datum
                                                </option>
                                                <option
                                                    value="select" {{ old('form_types')[$index] == 'select' ? 'selected' : '' }}>
                                                    Dropdown (Kies 1 waarde uit een menu)
                                                </option>
                                                <option
                                                    value="radio" {{ old('form_types')[$index] == 'radio' ? 'selected' : '' }}>
                                                    Radio (Kies 1 waarde uit verschillende opties)
                                                </option>
                                                <option
                                                    value="checkbox" {{ old('form_types')[$index] == 'checkbox' ? 'selected' : '' }}>
                                                    Checkbox (Kies meerdere waardes uit verschillende opties)
                                                </option>
                                            </select>

                                            @if(in_array(old('form_types')[$index], ['select', 'radio', 'checkbox']))
                                                <div id="optionsContainer{{ $index }}" class="mt-2 w-100">
                                                    <label>Waardes die in je dropdown, radio of checkbox komen te
                                                        staan</label>
                                                    <div id="options{{ $index }}" class="w-100">
                                                        @foreach(old('form_options.'.$index, []) as $option)
                                                            <div
                                                                class="d-flex flex-row-responsive align-items-center gap-2 w-100 mt-2">
                                                                <input type="text" class="form-control w-full"
                                                                       name="form_options[{{ $index }}][]"
                                                                       value="{{ $option }}">
                                                                <button type="button"
                                                                        class="btn btn-outline-danger d-flex align-items-center justify-content-center"
                                                                        style="min-width: 10%"
                                                                        onclick="removeOption(this)"><span
                                                                        class="material-symbols-rounded">close</span>
                                                                </button>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <button type="button" class="btn btn-info mt-2"
                                                            onclick="addOption({{ $index }})">Voeg optie toe
                                                    </button>
                                                </div>
                                            @endif

                                            <label for="fieldRequired{{ $index }}">Verplicht veld</label>
                                            <input id="fieldRequired{{ $index }}" class="form-check-input"
                                                   type="checkbox"
                                                   name="is_required[]" {{ old('is_required.'.$index) ? 'checked' : '' }}>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button class="btn btn-primary text-white" type="button" onclick="addFormElement()">Voeg
                                veld
                                toe
                            </button>
                            <script>
                                let fields = 0;

                                function addFormElement() {
                                    let elementContainer = document.getElementById('form-elements');
                                    elementContainer.classList.remove('d-none');

                                    let html = `
        <div class="d-flex flex-column gap-2 bg-white rounded p-4 align-items-start" id="formElement${fields}">
            <button type="button" class="btn btn-outline-danger align-self-end" onclick="removeFormElement(${fields})">Verwijder veld</button>
            <label for="fieldLabel${fields}">Veldlabel (bijvoorbeeld: Naam, Achternaam, Adres)</label>
            <input id="fieldLabel${fields}" class="form-control" type="text" name="form_labels[]">

            <label for="fieldType${fields}">Type veld (wat je veld accepteerd, bijvoorbeeld "Tekst" als je een naam wilt of "Dropdown" bij een lijst tijdsloten)</label>
            <select id="fieldType${fields}" class="form-select" name="form_types[]" onchange="handleFieldTypeChange(this, ${fields})">
                <option value="text">Tekst</option>
                <option value="email">E-mail (geldig e-mail adres)</option>
                <option value="number">Getal</option>
                <option value="date">Datum</option>
                <option value="select">Dropdown (Kies 1 waarde uit een menu)</option>
                <option value="radio">Radio (Kies 1 waarde uit verschillende opties)</option>
                <option value="checkbox">Checkbox (Kies meerdere waardes uit verschillende opties)</option>
            </select>

            <div id="optionsContainer${fields}" class="d-none mt-2 w-100">
                <label>Waardes die in je dropdown, radio of checkbox komen te staan</label>
                <div id="options${fields}" class="w-100">
                    <div class="d-flex flex-row align-items-center gap-2 w-100 mt-2">
                        <input type="text" class="form-control w-full" name="form_options[${fields}][]">
                        <button type="button" class="btn btn-outline-danger d-flex align-items-center justify-content-center" style="min-width: 10%" onclick="removeOption(this)"><span
                                                                class="material-symbols-rounded">close</span></button>
                    </div>
                </div>
                <button type="button" class="btn btn-info mt-2" onclick="addOption(${fields})">Voeg optie toe</button>
            </div>

            <label for="fieldRequired${fields}">Verplicht veld</label>
            <input id="fieldRequired${fields}" class="form-check-input" type="checkbox" name="is_required[]">
        </div>`;

                                    elementContainer.insertAdjacentHTML('beforeend', html);
                                    fields++;
                                }

                                function removeFormElement(index) {
                                    let element = document.getElementById(`formElement${index}`);
                                    element.remove();
                                }

                                function addOption(fieldIndex) {
                                    let optionsContainer = document.getElementById(`options${fieldIndex}`);
                                    let newOptionHtml = `
        <div class="d-flex align-items-center gap-2 w-100 mt-2">
            <input type="text" class="form-control w-full" name="form_options[${fieldIndex}][]">
            <button type="button" class="btn btn-outline-danger d-flex align-items-center justify-content-center" style="min-width: 10%" onclick="removeOption(this)"><span
                                                                class="material-symbols-rounded">close</span></button>
        </div>`;
                                    optionsContainer.insertAdjacentHTML('beforeend', newOptionHtml);
                                }

                                function removeOption(button) {
                                    button.parentElement.remove();
                                }

                                function handleFieldTypeChange(select, fieldIndex) {
                                    let optionsContainer = document.getElementById(`optionsContainer${fieldIndex}`);
                                    let selectedValue = select.value;

                                    if (['select', 'radio', 'checkbox'].includes(selectedValue)) {
                                        optionsContainer.classList.remove('d-none');
                                    } else {
                                        optionsContainer.classList.add('d-none');
                                    }
                                }
                            </script>


                        </div>
                    @endif

                    <div class="mt-4">
                        <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">local_activity</span>Tickets
                        </h2>
                        <p>Als het evenement gratis is kun je dit overslaan.</p>
                        <div class="d-flex flex-row-responsive gap-2 justify-content-between align-items-center">
                            {{-- PRICE EDITOR --}}
                            <div class="p-3 border rounded-3 bg-white w-100">
                                 <h2 class="flex-row gap-3"><span
                                class="material-symbols-rounded me-2">attach_money</span>Prijsconfiguratie
                        </h2>

                                <div
                                    class="d-flex flex-column flex-md-row gap-3 align-items-end mb-4 p-3 border rounded">
                                    <div class="flex-grow-1">
                                        <label for="new_price_name" class="form-label mb-1">Naam (bv.
                                            "Basisprijs")</label>
                                        <input type="text" id="new_price_name" class="form-control"
                                               placeholder="Naam van prijscomponent">
                                    </div>
                                    <div style="max-width: 150px;">
                                        <label for="new_price_amount" class="form-label mb-1">Bedrag / %</label>
                                        <input type="number" step="0.01" id="new_price_amount" class="form-control"
                                               placeholder="0.00">
                                    </div>
                                    <div class="flex-grow-1">
                                        <label for="new_price_type" class="form-label mb-1">Type</label>
                                        <select id="new_price_type" class="form-select">
                                            <option value="0">Standaard Prijs (€)</option>
                                            <option value="1">Percentage Toeslag (%)</option>
                                            <option value="2">Vaste Korting (€)</option>
                                            <option value="4">Percentage Korting (%)</option>
                                            <option value="3">Extra Kosten (excl.)</option>
                                        </select>
                                    </div>
                                    <button type="button" id="add-price-btn" class="btn btn-primary"
                                            style="min-width: 100px;">
                                        Toevoegen
                                    </button>
                                </div>
                                <small id="price-add-error" class="text-danger d-block mb-3"></small>
                                @error('prices_to_add')
                                <span class="text-danger small mb-3 d-block">{{ $message }}</span>
                                @enderror

                                <div id="price-list-container"
                                     class="d-flex flex-column gap-2 py-2 border-top border-bottom">
                                    {{-- Prices will be rendered here by JavaScript --}}
                                </div>
                                <p id="price-list-placeholder" class="text-muted w-100 m-0 p-3">
                                    Nog geen prijscomponenten toegevoegd.
                                </p>
                            </div>

                        </div>
                            <div class="d-flex flex-column">
                                <label for="max_tickets" class="col-form-label ">Maximale hoeveelheid tickets</label>
                                <input name="max_tickets" type="number" class="form-control" id="max_tickets"
                                       value="{{ old('max_tickets') }}"
                                >
                                @error('max_tickets')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>


                        <div class="mt-4">
                            <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">event_note</span>Extra's
                            </h2>

                            @if(!isset($lesson))
                                <div class="w-100">
                                    <label for="public" class="col-form-label ">Zichtbaarheid <span
                                            class="required-form">*</span></label>
                                    <select id="public" type="text"
                                            class="form-select @error('public') is-invalid @enderror"
                                            name="public">
                                        <option value="0">Openbaar</option>
                                        <option value="1">Verborgen</option>
                                    </select>
                                    @error('public')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>

                            @else
                                <input name="public" type="hidden" value="0">
                                <input name="lesson_id" type="hidden" value="{{$lesson->id}}">
                            @endif

                            <div class="d-flex flex-column">
                                <label for="location" class="col-form-label ">Locatie, bijvoorbeeld "Tramstraat 45a"</label>
                                <input name="location" type="text" class="form-control" id="location"
                                       value="{{ old('location') }}"
                                >
                                @error('location')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            @if(!isset($lesson))

                                <div class="d-flex flex-column">
                                    <label for="organisator" class="col-form-label ">Organisatie, bijvoorbeeld
                                        "Bosvrienden"</label>
                                    <input name="organisator" type="text" class="form-control" id="organisator"
                                           value="{{ old('organisator') }}"
                                    >
                                    @error('organisator')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endif

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
                                class="btn mt-2 btn-success flex flex-row align-items-center justify-content-center">
                                <span class="button-text">Opslaan</span>
                                <span style="display: none" class="loading-spinner spinner-border spinner-border-sm"
                                      aria-hidden="true"></span>
                                <span style="display: none" class="loading-text" role="status">Laden...</span>
                            </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const CSRF_TOKEN = document.querySelector('input[name="_token"]').value;

        let fileIdCounter = 0;

        // --- Utility Functions ---
        const createElement = (tag, attributes = {}, innerHTML = '') => {
            const el = document.createElement(tag);
            for (const key in attributes) {
                if (key === 'className') el.className = attributes[key];
                else if (key === 'onclick' && typeof attributes[key] === 'function') el.onclick = attributes[key];
                else if (key === 'oninput' && typeof attributes[key] === 'function') el.oninput = attributes[key];
                else el.setAttribute(key, attributes[key]);
            }
            el.innerHTML = innerHTML;
            return el;
        };

        // ===========================================
        // Price Editor
        // ===========================================
        const PriceEditor = {
            prices: [],
            container: document.getElementById('price-list-container'),
            placeholder: document.getElementById('price-list-placeholder'),
            errorEl: document.getElementById('price-add-error'),

            render() {
                this.container.innerHTML = '';
                this.placeholder.style.display = this.prices.length === 0 ? 'block' : 'none';

                this.prices.forEach((price, index) => {
                    this.container.appendChild(this.createPriceRow(price, index));
                });

                // LIVE UPDATE
                this.updateHiddenField();
            },

            updateHiddenField() {
                const json = JSON.stringify(this.prices);
                document.getElementById('prices_to_add').value = json;
            },

            getTypeText(type) {
                switch(parseInt(type, 10)) {
                    case 0: return 'Standaard Prijs (€)';
                    case 1: return 'Percentage Toeslag (%)';
                    case 2: return 'Vaste Korting (€)';
                    case 3: return 'Extra Kosten (excl.)';
                    case 4: return 'Percentage Korting (%)';
                    default: return 'Onbekend';
                }
            },

            createPriceRow(price, index) {
                const wrapper = createElement('div', { className: 'd-flex align-items-center gap-3 p-2 border rounded' });
                const nameEl = createElement('div', { className: 'flex-grow-1' }, `<strong>${price.name}</strong>`);
                const amountText = `${(parseInt(price.type, 10) === 1 || parseInt(price.type, 10) === 4) ? '' : '€ '}${parseFloat(price.amount).toFixed(2)}${(parseInt(price.type, 10) === 1 || parseInt(price.type, 10) === 4) ? '%' : ''}`;
                const amountEl = createElement('div', { className: 'fw-bold', style: 'min-width: 80px; text-align: right;'}, amountText);
                const typeEl = createElement('div', { className: 'text-muted small', style: 'min-width: 150px;' }, this.getTypeText(price.type));
                const removeBtn = createElement('button', {
                    type: 'button', className: 'btn btn-sm btn-outline-danger', title: 'Verwijder prijs', onclick: () => this.removePrice(index)
                }, '&times;');

                wrapper.appendChild(nameEl);
                wrapper.appendChild(typeEl);
                wrapper.appendChild(amountEl);
                wrapper.appendChild(removeBtn);

                return wrapper;
            },

            addPrice() {
                this.errorEl.textContent = '';
                const name = document.getElementById('new_price_name').value.trim();
                const amount = document.getElementById('new_price_amount').value;
                const type = document.getElementById('new_price_type').value;

                if (!name || !amount) {
                    this.errorEl.textContent = 'Naam en bedrag zijn verplicht.';
                    return;
                }

                const newPrice = { name, amount, type };
                this.prices.push(newPrice);
                this.render();

                // Clear inputs
                document.getElementById('new_price_name').value = '';
                document.getElementById('new_price_amount').value = '';
                document.getElementById('new_price_type').value = '0';
            },

            removePrice(index) {
                this.prices.splice(index, 1);
                this.render();
            }
        };


        // ===========================================
        // Main Form Submission & Initialization
        // ===========================================
        document.addEventListener('DOMContentLoaded', () => {
            PriceEditor.render(); // Render in case of old input

            document.getElementById('add-price-btn').addEventListener('click', () => PriceEditor.addPrice());

            const form = document.getElementById('product-form');
            form.addEventListener('submit', function (e) {
                document.getElementById('description').value = document.getElementById('text-input').innerHTML;

                // Force one last update to be sure
                PriceEditor.updateHiddenField();

                const saveButton = document.getElementById('save-button');
                saveButton.disabled = true;
                saveButton.querySelector('.button-text').style.display = 'none';
                saveButton.querySelector('.loading-spinner').style.display = 'inline-block';
                saveButton.querySelector('.loading-text').style.display = 'inline-block';
            });
        });
    </script>
@endsection

