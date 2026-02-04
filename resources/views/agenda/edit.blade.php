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
                        <input id="info2" class="form-control" type="email" value="info@noordenlicht.nl">
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

    <div id="deleteModal" class="popup d-none" style="z-index: 99999; top: 0; left: 0; position: fixed">
        <div class="popup-body">
            <div class="page">
                <h2>Weet je het zeker?</h2>
                <p>Kies hoe je het evenement wilt verwijderen, deze actie kan hierna niet meer ongedaan gemaakt
                    worden:</p>
                <div class="d-grid gap-2">
                    <button id="deleteSingle" class="btn btn-danger text-white">Alleen dit evenement verwijderen
                    </button>
                    <button id="deleteFollowing" class="btn btn-outline-danger">Deze en alle volgende verwijderen
                    </button>
                    <button id="deleteAll" class="btn btn-outline-danger">Alle evenementen verwijderen</button>
                    <button id="cancelDelete" class="btn btn-success">Annuleren</button>
                </div>
            </div>
        </div>
    </div>

    <div id="saveModal" class="popup d-none" style="z-index: 99999; top: 0; left: 0; position: fixed">
        <div class="popup-body">
            <div class="page">
                <h2>Wijziging toepassen op</h2>
                <p>Kies hoe je de wijzigingen wilt doorvoeren:</p>
                <div class="d-grid gap-2 mt-3">
                    <button id="saveSingle"
                            onclick="function handleButtonClick(button) {
                                 button.disabled = true;
                                button.classList.add('loading');

                                // Show the spinner and hide the text
                                button.querySelector('.button-text').style.display = 'none';
                                button.querySelector('.loading-spinner').style.display = 'inline-block';
                                button.querySelector('.loading-text').style.display = 'inline-block';
                            }
                            handleButtonClick(this)"
                            class="btn btn-success flex flex-row align-items-center justify-content-center">
                        <span class="button-text">Alleen dit evenement</span>
                        <span style="display: none" class="loading-spinner spinner-border spinner-border-sm"
                              aria-hidden="true"></span>
                        <span style="display: none" class="loading-text" role="status">Laden...</span>
                    </button>
                    <button id="saveFollowing"
                            onclick="function handleButtonClick(button) {
                                 button.disabled = true;
                                button.classList.add('loading');

                                // Show the spinner and hide the text
                                button.querySelector('.button-text').style.display = 'none';
                                button.querySelector('.loading-spinner').style.display = 'inline-block';
                                button.querySelector('.loading-text').style.display = 'inline-block';
                            }
                            handleButtonClick(this)"
                            class="btn btn-outline-success flex flex-row align-items-center justify-content-center">
                        <span class="button-text">Dit evenement en alle volgende</span>
                        <span style="display: none" class="loading-spinner spinner-border spinner-border-sm"
                              aria-hidden="true"></span>
                        <span style="display: none" class="loading-text" role="status">Laden...</span>
                    </button>
                    <button id="saveAll"
                            onclick="function handleButtonClick(button) {
                                 button.disabled = true;
                                button.classList.add('loading');

                                // Show the spinner and hide the text
                                button.querySelector('.button-text').style.display = 'none';
                                button.querySelector('.loading-spinner').style.display = 'inline-block';
                                button.querySelector('.loading-text').style.display = 'inline-block';
                            }
                            handleButtonClick(this)"
                            class="btn btn-outline-success flex flex-row align-items-center justify-content-center">
                        <span class="button-text">Alle evenementen</span>
                        <span style="display: none" class="loading-spinner spinner-border spinner-border-sm"
                              aria-hidden="true"></span>
                        <span style="display: none" class="loading-text" role="status">Laden...</span>
                    </button>

                    <button id="cancelSave" class="btn btn-danger text-white">Annuleren</button>
                </div>
            </div>
        </div>
    </div>


    <div class="container col-md-11">
        <h1>Bewerken</h1>
        @if(isset($lesson))
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('lessons') }}">Lessen</a></li>
                    <li class="breadcrumb-item"><a
                            href="{{ route('lessons.environment.lesson', $lesson->id) }}">{{ $lesson->title }}</a></li>
                    <li class="breadcrumb-item"><a
                            @if($view === 'month') href="{{ route('agenda.month', ['month' => $monthOffset, 'all' => $wantViewAll ? 1 : 0, 'lessonId' => $lesson->id]) }}"
                            @else href="{{ route('agenda.schedule', ['month' => $monthOffset, 'all' => $wantViewAll ? 1 : 0, 'lessonId' => $lesson->id]) }}" @endif>Planning</a>
                    </li>
                    <li class="breadcrumb-item"><a
                            href="{{ route('agenda.activity', ['lessonId' => $lesson->id, 'id' => $activity->id, 'view' => $view, 'month' => $monthOffset, 'all' => $wantViewAll ? 1 : 0, 'startDate' => date("Y-m-d", strtotime($activity->date_start))]) }}">{{ $activity->title }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Bewerken</li>
                </ol>
            </nav>
        @else
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a
                            @if($view === 'month') href="{{ route('agenda.month', ['month' => $monthOffset, 'all' => $wantViewAll ? 1 : 0]) }}"
                            @else href="{{ route('agenda.schedule', ['month' => $monthOffset, 'all' => $wantViewAll ? 1 : 0]) }}" @endif>Mijn
                            agenda</a></li>
                    <li class="breadcrumb-item"><a
                            href="{{ route('agenda.activity', ['id' => $activity->id, 'view' => $view, 'month' => $monthOffset, 'all' => $wantViewAll ? 1 : 0, 'startDate' => date("Y-m-d", strtotime($activity->date_start))]) }}">{{ $activity->title }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Bewerken</li>
                </ol>
            </nav>

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
                <form id="activityForm" method="POST" action="{{ route('agenda.edit.activity.save', $activity->id) }}"
                      enctype="multipart/form-data">
                    @csrf

                    <div class="mt-4">
                        <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">event</span>Algemene
                            Informatie</h2>
                        <div class="d-flex flex-column">
                            <label for="title" class="col-md-4 col-form-label ">Titel <span
                                    class="required-form">*</span></label>
                            <input name="title" type="text" class="form-control" id="title"
                                   value="{{ $activity->title }}"
                            >
                            @error('title')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="">
                            <label for="image" class="col-md-4 col-form-label ">Coverafbeelding <span
                                    class="required-form">*</span></label>
                            <div class="d-flex flex-row-responsive gap-4 align-items-center justify-content-center">
                                <input class="form-control mt-2 col" id="image" type="file" name="image"
                                       accept="image/*">
                                @if($activity->image)
                                    <img class="zoomable-image" alt="profielfoto"
                                         style="width: 100%; min-width: 25px; max-width: 250px"
                                         src="{{ asset('files/agenda/agenda_images/'.$activity->image) }}">
                                @endif
                            </div>
                            @error('image')
                            <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <label for="text-input">De content van je evenement</label>
                            <div class="editor-parent">
                                @yield('editor')
                                <div id="text-input" contenteditable="true" name="text-input"
                                     class="text-input">{!! $activity->content !!}</div>
                                <small id="characters"></small>
                            </div>

                            <input id="content" name="content" type="hidden" value="{{ $activity->content }}">

                            @error('content')
                            <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">date_range</span>Datum &
                            Tijd</h2>
                        <div class="d-flex flex-row-responsive gap-2 justify-content-between align-items-center">
                            <div class="w-100">
                                <label for="date_start" class="col-md-4 col-form-label ">Start datum en tijd <span
                                        class="required-form">*</span></label>
                                <input id="date_start" value="{{ $activity->date_start }}" type="datetime-local"
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
                                <input id="date_end" value="{{ $activity->date_end }}" type="datetime-local"
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
                                <option value="never" @if($activity->recurrence_rule === "never" || $activity->recurrence_rule === null) selected @endif>Nooit</option>
                                <option value="daily" @if($activity->recurrence_rule === "daily") selected @endif>Dagelijks</option>
                                <option value="weekly" @if($activity->recurrence_rule === "weekly") selected @endif>Wekelijks</option>
                                <option value="monthly" @if($activity->recurrence_rule === "monthly") selected @endif>Maandelijks</option>
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
                            <div class="alert alert-info">Het bewerken van een formulier verwijderd gegeven antwoorden.</div>

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
                                 class="d-flex flex-column bg-info p-2 gap-2 m-2 rounded @if(!$activity->form_labels))d-none @endif">
                                @if(isset($activity->formElements) && $activity->formElements->isNotEmpty())
                                    @foreach($activity->formElements as $index => $formElement)
                                        <div class="d-flex flex-column gap-2 bg-white rounded p-4 align-items-start"
                                             id="formElement{{ $index }}">
                                            <button type="button" class="btn btn-outline-danger align-self-end"
                                                    onclick="removeFormElement({{ $index }})">Verwijder veld
                                            </button>

                                            <label for="fieldLabel{{ $index }}">Veldlabel (bijvoorbeeld: Naam,
                                                Achternaam,
                                                Adres)</label>
                                            <input id="fieldLabel{{ $index }}" class="form-control" type="text"
                                                   name="form_labels[]" value="{{ $formElement->label }}">

                                            <label for="fieldType{{ $index }}">Type veld</label>
                                            <select id="fieldType{{ $index }}" class="form-control" name="form_types[]"
                                                    onchange="handleFieldTypeChange(this, {{ $index }})">
                                                <option
                                                    value="text" {{ $formElement->type == 'text' ? 'selected' : '' }}>
                                                    Tekst
                                                </option>
                                                <option
                                                    value="email" {{ $formElement->type == 'email' ? 'selected' : '' }}>
                                                    E-mail
                                                </option>
                                                <option
                                                    value="number" {{ $formElement->type == 'number' ? 'selected' : '' }}>
                                                    Getal
                                                </option>
                                                <option
                                                    value="date" {{ $formElement->type == 'date' ? 'selected' : '' }}>
                                                    Datum
                                                </option>
                                                <option
                                                    value="select" {{ $formElement->type == 'select' ? 'selected' : '' }}>
                                                    Dropdown
                                                </option>
                                                <option
                                                    value="radio" {{ $formElement->type == 'radio' ? 'selected' : '' }}>
                                                    Radio
                                                </option>
                                                <option
                                                    value="checkbox" {{ $formElement->type == 'checkbox' ? 'selected' : '' }}>
                                                    Checkbox
                                                </option>
                                            </select>

                                            @if(in_array($formElement->type, ['select', 'radio', 'checkbox']))
                                                <div id="optionsContainer{{ $index }}" class="mt-2 w-100">
                                                    <label>Waardes die in je dropdown, radio of checkbox komen te
                                                        staan</label>
                                                    <div id="options{{ $index }}" class="w-100">
                                                        @foreach(explode(',', $formElement->option_value) as $option)
                                                            <!-- Split the string into an array -->
                                                            <div
                                                                class="d-flex flex-row-responsive align-items-center gap-2 w-100 mt-2">
                                                                <input type="text" class="form-control w-full"
                                                                       name="form_options[{{ $index }}][]"
                                                                       value="{{ trim($option) }}">
                                                                <!-- Trim whitespace -->
                                                                <button type="button"
                                                                        class="btn btn-outline-danger d-flex align-items-center justify-content-center"
                                                                        style="min-width: 10%"
                                                                        onclick="removeOption(this)">
                                                                    <span class="material-symbols-rounded">close</span>
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
                                                   name="is_required[]" {{ $formElement->is_required ? 'checked' : '' }}>
                                        </div>
                                    @endforeach
                                @endif

                            </div>
                            <button class="btn btn-primary text-white" type="button" onclick="addFormElement()">Voeg
                                veld
                                toe
                            </button>
                            <script>
                                let fields = {{ isset($activity->formElements) ? $activity->formElements->count() : 0 }};

                                function addFormElement() {
                                    let elementContainer = document.getElementById('form-elements');
                                    elementContainer.classList.remove('d-none');

                                    let html = `
        <div class="d-flex flex-column gap-2 bg-white rounded p-4 align-items-start" id="formElement${fields}">
            <button type="button" class="btn btn-outline-danger align-self-end" onclick="removeFormElement(${fields})">Verwijder veld</button>
            <label for="fieldLabel${fields}">Veldlabel (bijvoorbeeld: Naam, Achternaam, Adres)</label>
            <input id="fieldLabel${fields}" class="form-control" type="text" name="form_labels[]">

            <label for="fieldType${fields}">Type veld (wat je veld accepteert)</label>
            <select id="fieldType${fields}" class="form-select" name="form_types[]" onchange="handleFieldTypeChange(this, ${fields})">
                <option value="text">Tekst</option>
                <option value="email">E-mail</option>
                <option value="number">Getal</option>
                <option value="date">Datum</option>
                <option value="select">Dropdown</option>
                <option value="radio">Radio</option>
                <option value="checkbox">Checkbox</option>
            </select>

            <div id="optionsContainer${fields}" class="d-none mt-2 w-100">
                <label>Waardes die in je dropdown, radio of checkbox komen te staan</label>
                <div id="options${fields}" class="w-100">
                    <div class="d-flex flex-row align-items-center gap-2 w-100 mt-2">
                        <input type="text" class="form-control w-full" name="form_options[${fields}][]">
                        <button type="button" class="btn btn-outline-danger d-flex align-items-center justify-content-center" style="min-width: 10%" onclick="removeOption(this)">
                            <span class="material-symbols-rounded">close</span>
                        </button>
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
                                    if (element) element.remove();
                                }

                                function addOption(fieldIndex) {
                                    let optionsContainer = document.getElementById(`options${fieldIndex}`);
                                    let newOptionHtml = `
        <div class="d-flex align-items-center gap-2 w-100 mt-2">
            <input type="text" class="form-control w-full" name="form_options[${fieldIndex}][]">
            <button type="button" class="btn btn-outline-danger d-flex align-items-center justify-content-center" style="min-width: 10%" onclick="removeOption(this)">
                <span class="material-symbols-rounded">close</span>
            </button>
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
                        <div class="mb-5 p-3 border rounded-3 bg-white">
                             <h2 class="flex-row gap-3"><span
                                class="material-symbols-rounded me-2">attach_money</span>Prijsconfiguratie
                        </h2>

                            <div class="d-flex flex-column flex-md-row gap-3 align-items-end mb-4 p-3 border rounded">
                                <div class="flex-grow-1">
                                    <label for="new_price_name" class="form-label mb-1">Naam (bv. "Basisprijs")</label>
                                    <input type="text" id="new_price_name" class="form-control" placeholder="Naam van prijscomponent">
                                </div>
                                <div>
                                    <label for="new_price_amount" class="form-label mb-1">Bedrag / %</label>
                                    <input type="number" step="0.01" id="new_price_amount" class="form-control" placeholder="0.00">
                                </div>
                                <div class="flex-grow-1">
                                    <label for="new_price_type" class="form-label mb-1">Type</label>
                                    <select id="new_price_type" class="form-select">
                                        <option value="0">Standaard Prijs (€)</option>
                                        <option value="1">BTW</option>
                                        <option value="2">Vaste Korting (€)</option>
                                        <option value="4">Percentage Korting (%)</option>
                                        <option value="3">Extra Kosten (excl.)</option>
                                    </select>
                                </div>
                                <button type="button" id="add-price-btn" class="btn btn-primary" style="min-width: 100px;">
                                    Toevoegen
                                </button>
                            </div>
                            <small id="price-add-error" class="text-danger d-block mb-3"></small>

                            <div id="price-list-container" class="d-flex flex-column gap-2 py-2 border-top border-bottom">
                                {{-- Prices will be rendered here by JavaScript --}}
                            </div>
                            <p id="price-list-placeholder" class="text-muted w-100 m-0 p-3">
                                Nog geen prijscomponenten toegevoegd.
                            </p>
                        </div>
                        <div class="d-flex flex-column">
                            <label for="max_tickets" class="col-form-label ">Maximale hoeveelheid tickets</label>
                            <input name="max_tickets" type="number" class="form-control" id="max_tickets"
                                   value="{{ $activity->max_tickets }}"
                            >
                            @error('max_tickets')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>
                    <div class="mt-4">
                        <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">event_note</span>Extra's
                        </h2>
                        <script>
                            document.addEventListener("DOMContentLoaded", function () {
                                const presenceSelect = document.getElementById("presence");
                                const dateContainer = document.getElementById("date-container");

                                function toggleDateInput() {
                                    if (presenceSelect.value === "1") {
                                        dateContainer.style.display = "block";
                                    } else {
                                        dateContainer.style.display = "none";
                                    }
                                }

                                // Initial check on page load
                                toggleDateInput();

                                // Listen for changes
                                presenceSelect.addEventListener("change", toggleDateInput);
                            });
                        </script>

                        @if(!isset($lesson))

                            <div class="w-100">
                                <label for="public" class="col-form-label ">Zichtbaarheid<span
                                        class="required-form">*</span></label>
                                <select id="public" type="text"
                                        class="form-select @error('public') is-invalid @enderror"
                                        name="public">
                                    <option @if($activity->public === false) selected @endif value="0">Openbaar</option>
                                    <option @if($activity->public === true) selected @endif value="1">Verborgen</option>
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
                            <label for="location" class="col-form-label ">Locatie, bijvoorbeeld "Tramstraat 54a"</label>
                            <input name="location" type="text" class="form-control" id="location"
                                   value="{{ $activity->location }}"
                            >
                            @error('location')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        @if(!isset($lesson))

                            <div class="d-flex flex-column">
                                <label for="organisator" class="col-form-label ">Organisatie, bijvoorbeeld "Bosvrienden" </label>
                                <input name="organisator" type="text" class="form-control" id="organisator"
                                       value="{{ $activity->organisator }}"
                                >
                                @error('organisator')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                        @endif
                    </div>
                    <div>
                        @error('roles')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror

                        @error('users')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <input type="hidden" name="edit_type" id="edit_type" value="all">
                    <input type="hidden" name="occurrence_date" id="occurrence_date" value="{{ \Carbon\Carbon::parse($activity->date_start)->toDateString() }}">

                    <input type="hidden" name="month" id="month" value="{{ $monthOffset }}">
                    <input type="hidden" name="view" id="view" value="{{ $view }}">
                    <input type="hidden" name="all" id="all" value="{{ $wantViewAll }}">


                    <div class="d-flex align-items-center flex-wrap flex-row mt-3 gap-2">
                        <button id="saveButton"
                                onclick="function handleButtonClick(button) {
                                 button.disabled = true;
                                button.classList.add('loading');

                                // Show the spinner and hide the text
                                button.querySelector('.button-text').style.display = 'none';
                                button.querySelector('.loading-spinner').style.display = 'inline-block';
                                button.querySelector('.loading-text').style.display = 'inline-block';
                            }
                            handleButtonClick(this)
                           "
                                class="btn btn-success flex flex-row align-items-center justify-content-center">
                            <span class="button-text">Opslaan</span>
                            <span style="display: none" class="loading-spinner spinner-border spinner-border-sm"
                                  aria-hidden="true"></span>
                            <span style="display: none" class="loading-text" role="status">Laden...</span>
                        </button>
                        @if(isset($lesson))
                            <a class="btn btn-danger text-white"
                               href="{{route('agenda.activity', ['id' => $activity->id, 'lessonId' => $lesson->id, 'view' => $view, 'month' => $monthOffset, 'all' => $wantViewAll ? 1 : 0, 'startDate' => date("Y-m-d", strtotime($activity->date_start))]) }}">Annuleren</a>
                            @if ($activity->recurrence_rule === null || $activity->recurrence_rule === "never")
                                <a class="delete-button btn btn-outline-danger"
                                   data-id="{{ $activity->id }}"
                                   data-name="{{ $activity->title }}"
                                   data-link="{{ route('agenda.delete', $activity->id) }}">Verwijderen</a>
                            @else
                                <a class="button-recurrence btn btn-outline-danger"
                                   data-id="{{ $activity->id }}"
                                   data-name="{{ $activity->title }}"
                                   data-link="{{ route('agenda.delete', $activity->id) }}"
                                   data-date="{{ date("Y-m-d", strtotime($activity->date_start)) }}"
                                >Verwijderen</a>

                            @endif
                        @else
                            <a class="btn btn-danger text-white"
                               href="{{ route('agenda.activity', ['id' => $activity->id, 'view' => $view, 'month' => $monthOffset, 'all' => $wantViewAll ? 1 : 0, 'startDate' => date("Y-m-d", strtotime($activity->date_start))]) }}">Annuleren</a>
                            @if ($activity->recurrence_rule === null || $activity->recurrence_rule === "never")
                                <a class="delete-button btn btn-outline-danger"
                                   data-id="{{ $activity->id }}"
                                   data-name="{{ $activity->title }}"
                                   data-link="{{ route('agenda.delete', $activity->id) }}">Verwijderen</a>
                            @else
                                <a class="button-recurrence btn btn-outline-danger"
                                   data-id="{{ $activity->id }}"
                                   data-name="{{ $activity->title }}"
                                   data-link="{{ route('agenda.delete', $activity->id) }}"
                                   data-date="{{ date("Y-m-d", strtotime($activity->date_start)) }}"
                                >Verwijderen</a>

                            @endif
                        @endif
                    </div>

                </form>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('activityForm');
            const saveBtn = document.getElementById('saveButton');

            const modalSave = document.getElementById('saveModal');
            const btnSaveAll = document.getElementById('saveAll');
            const btnSaveFollowing = document.getElementById('saveFollowing');
            const btnSaveSingle = document.getElementById('saveSingle');
            const btnSaveCancel = document.getElementById('cancelSave');

            const modalDelete = document.getElementById('deleteModal');
            const btnDeleteAll = document.getElementById('deleteAll');
            const btnDeleteFollowing = document.getElementById('deleteFollowing');
            const btnDeleteSingle = document.getElementById('deleteSingle');
            const btnDeleteCancel = document.getElementById('cancelDelete');
            const editTypeInput = document.getElementById('edit_type');

            @php
                $validRecurrences = ['daily', 'weekly', 'monthly'];
                $hasRecurrence = in_array($activity->recurrence_rule, $validRecurrences);
            @endphp

            const hasRecurrence = {{ $hasRecurrence ? 'true' : 'false' }};

            console.log(hasRecurrence)

            // Store these for when the modal is open
            let deleteUrl, occurrenceDate;

            // Open modal when any delete-button is clicked
            document.querySelectorAll('.button-recurrence').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    deleteUrl = this.dataset.link;           // route('agenda.delete', id)
                    occurrenceDate = this.dataset.date;           // YYYY-MM-DD
                    modalDelete.classList.remove('d-none');
                });
            });

            // Cancel
            btnDeleteCancel.addEventListener('click', () => {
                modalDelete.classList.add('d-none');
            });

            // Delete ALL events
            btnDeleteAll.addEventListener('click', () => {
                window.location.href = deleteUrl + '?type=all'
                    @if(isset($lesson))
                    + '&lessonId={{ $lesson->id }}'
                    @endif
                    @if(isset($view))
                    + '&view={{ $view }}'
                    @endif
                    @if(isset($monthOffset))
                    + '&month={{ $monthOffset }}'
                    @endif
                    @if(isset($wantViewAll))
                    + '&all={{ $wantViewAll }}'
                @endif
            });

            // Delete this and following: set end_recurrance = this date
            btnDeleteFollowing.addEventListener('click', () => {
                window.location.href = deleteUrl
                    + '?type=following'
                    + '&end_date=' + encodeURIComponent(occurrenceDate)
                    @if(isset($lesson))
                    + '&lessonId={{ $lesson->id }}'
                @endif
                    @if(isset($view))
                    + '&view={{ $view }}'
                @endif
                    @if(isset($monthOffset))
                    + '&month={{ $monthOffset }}'
                @endif
                    @if(isset($wantViewAll))
                    + '&all={{ $wantViewAll }}'
                @endif
            });

            // Delete only this single occurrence
            btnDeleteSingle.addEventListener('click', () => {
                window.location.href = deleteUrl
                    + '?type=single'
                    + '&date=' + encodeURIComponent(occurrenceDate)
                    @if(isset($lesson))
                    + '&lessonId={{ $lesson->id }}'
                    @endif
                    @if(isset($view))
                    + '&view={{ $view }}'
                    @endif
                    @if(isset($monthOffset))
                    + '&month={{ $monthOffset }}'
                    @endif
                    @if(isset($wantViewAll))
                    + '&all={{ $wantViewAll }}'
                @endif
            });

            saveBtn.addEventListener('click', function (e) {
                if (hasRecurrence) {
                    e.preventDefault();
                    modalSave.classList.remove('d-none');
                } else {
                    form.submit();
                }
            });

            btnSaveAll.addEventListener('click', function () {
                editTypeInput.value = 'all';
                modalSave.classList.add('d-none');
                form.submit();
            });
            btnSaveFollowing.addEventListener('click', function () {
                editTypeInput.value = 'following';
                modalSave.classList.add('d-none');
                form.submit();
            });
            btnSaveSingle.addEventListener('click', function () {
                editTypeInput.value = 'single';
                modalSave.classList.add('d-none');
                form.submit();
            });

            // 3) Cancel just hides the modal
            btnSaveCancel.addEventListener('click', function () {
                modalSave.classList.add('d-none');

                saveBtn.disabled = false;
                saveBtn.classList.remove('loading');

                // Show the spinner and hide the text
                saveBtn.querySelector('.button-text').style.display = 'inline-block';
                saveBtn.querySelector('.loading-spinner').style.display = 'none';
                saveBtn.querySelector('.loading-text').style.display = 'none';
            });
        });
    </script>

    <script>
        // --- Configuration ---
        const ACTIVITY_ID = {{ $activity->id }};

        // Assumes a polymorphic price controller or similar shared logic
        const LINK_PRICE_URL = '{{ route('admin.prices.link') }}';
        const UNLINK_PRICE_URL_BASE = '{{ route('admin.prices.unlink', ['priceLink' => 'PLACEHOLDER']) }}';

        const CSRF_TOKEN = document.querySelector('input[name="_token"]').value;

        // --- Global State ---
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
        // Price Editor (ASYNC version)
        // ===========================================
        const PriceEditor = {
            prices: [],
            container: document.getElementById('price-list-container'),
            placeholder: document.getElementById('price-list-placeholder'),
            errorEl: document.getElementById('price-add-error'),

            initialize() {
                // Initialize with existing prices relation
                const existingPrices = @json($activity->prices->map(function($pp) {
                    return [
                        'id' => $pp->id, // This is the linkage ID
                        'price' => $pp->price
                    ];
                }) ?? []);

                this.prices = existingPrices;
                this.render();
            },

            render() {
                this.container.innerHTML = '';
                this.placeholder.style.display = this.prices.length === 0 ? 'block' : 'none';

                this.prices.forEach((priceData, index) => {
                    this.container.appendChild(this.createPriceRow(priceData, index));
                });
            },

            getTypeText(type) {
                switch(parseInt(type, 10)) {
                    case 0: return 'Standaard Prijs (€)';
                    case 1: return 'BTW';
                    case 2: return 'Vaste Korting (€)';
                    case 3: return 'Extra Kosten (excl.)';
                    case 4: return 'Percentage Korting (%)';
                    default: return 'Onbekend';
                }
            },

            createPriceRow(priceData, index) {
                const wrapper = createElement('div', { className: 'd-flex align-items-center gap-3 p-2 border rounded flex-wrap' });
                const nameEl = createElement('div', { className: 'flex-grow-1' }, `<strong>${priceData.price.name}</strong>`);
                const amountText = `${(parseInt(priceData.price.type, 10) === 1 || parseInt(priceData.price.type, 10) === 4) ? '' : '€ '}${parseFloat(priceData.price.amount).toFixed(2)}${(parseInt(priceData.price.type, 10) === 1 || parseInt(priceData.price.type, 10) === 4) ? '%' : ''}`;
                const amountEl = createElement('div', { className: 'fw-bold', style: 'min-width: 80px; text-align: right;'}, amountText);
                const typeEl = createElement('div', { className: 'text-muted small', style: 'min-width: 150px;' }, this.getTypeText(priceData.price.type));
                const removeBtn = createElement('button', {
                    type: 'button', className: 'btn btn-sm btn-outline-danger', title: 'Verwijder prijs', onclick: () => this.removePrice(index)
                }, '&times;');

                wrapper.appendChild(nameEl);
                wrapper.appendChild(typeEl);
                wrapper.appendChild(amountEl);
                wrapper.appendChild(removeBtn);

                return wrapper;
            },

            async addPrice() {
                this.errorEl.textContent = '';
                const nameInput = document.getElementById('new_price_name');
                const amountInput = document.getElementById('new_price_amount');
                const typeInput = document.getElementById('new_price_type');

                const name = nameInput.value.trim();
                const amount = amountInput.value;
                const type = typeInput.value;

                if (!name || !amount) {
                    this.errorEl.textContent = 'Naam en bedrag zijn verplicht.';
                    return;
                }

                const payload = {
                    model_id: ACTIVITY_ID,
                    model_type: 'activity',
                    name: name,
                    amount: amount,
                    type: type
                };

                try {
                    const response = await fetch(LINK_PRICE_URL, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                        body: JSON.stringify(payload)
                    });

                    const result = await response.json();

                    if (!response.ok || !result.success) {
                        throw new Error(result.message || 'Failed to add price.');
                    }

                    this.prices.push(result.data);
                    this.render();

                    // Clear inputs
                    nameInput.value = '';
                    amountInput.value = '';
                    typeInput.value = '0';

                } catch (error) {
                    this.errorEl.textContent = `Fout: ${error.message}`;
                    console.error("Error adding price:", error);
                }
            },

            async removePrice(index) {
                const priceData = this.prices[index];
                const priceLinkId = priceData.id;

                // FIX: Append model_type so Controller knows which table to check
                const url = UNLINK_PRICE_URL_BASE.replace('PLACEHOLDER', priceLinkId) + '?model_type=activity';

                try {
                    const response = await fetch(url, {
                        method: 'DELETE',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN }
                    });

                    const result = await response.json();

                    if (!response.ok || !result.success) {
                        throw new Error(result.message || 'Failed to remove price.');
                    }

                    this.prices.splice(index, 1);
                    this.render();

                } catch (error) {
                    alert(`Kon prijs niet verwijderen: ${error.message}`);
                    console.error("Error removing price:", error);
                }
            },
        };


        // ===========================================
        // Main Form Submission & Initialization
        // ===========================================
        document.addEventListener('DOMContentLoaded', () => {
            PriceEditor.initialize();
            document.getElementById('add-price-btn').addEventListener('click', () => PriceEditor.addPrice());

            const form = document.getElementById('activity-form');
            form.addEventListener('submit', function (e) {
                // Update description from editor
                document.getElementById('description').value = document.getElementById('text-input').innerHTML;

                // Set loading state
                const saveButton = document.getElementById('save-button');
                saveButton.disabled = true;
                saveButton.querySelector('.button-text').style.display = 'none';
                saveButton.querySelector('.loading-spinner').style.display = 'inline-block';
                saveButton.querySelector('.loading-text').style.display = 'inline-block';
            });
        });
    </script>

@endsection

