@extends('layouts.dashboard')
@include('partials.editor')

@vite(['resources/js/texteditor.js', 'resources/js/search-user.js', 'resources/css/texteditor.css'])

@php
    use Carbon\Carbon;
    Carbon::setLocale('nl');
@endphp



@section('content')
    <div id="popUp" class="popup" style="margin-top: -122px; display: none">
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

    <div id="deleteModal" class="popup d-none" style="margin-top: -122px;">
        <div class="popup-body">
            <div class="page">
                <h2>Weet je het zeker?</h2>
                <p>Kies hoe je de activiteit wilt verwijderen, deze actie kan hierna niet meer ongedaan gemaakt
                    worden:</p>
                <div class="d-grid gap-2">
                    <button id="deleteSingle" class="btn btn-danger text-white">Alleen deze activiteit verwijderen
                    </button>
                    <button id="deleteFollowing" class="btn btn-outline-danger">Deze en alle volgende verwijderen
                    </button>
                    <button id="deleteAll" class="btn btn-outline-danger">Alle activiteiten verwijderen</button>
                    <button id="cancelDelete" class="btn btn-success">Annuleren</button>
                </div>
            </div>
        </div>
    </div>

    <div id="saveModal" class="popup d-none" style="margin-top: -122px;">
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
                        <span class="button-text">Alleen deze activiteit</span>
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
                        <span class="button-text">Deze activiteit en alle volgende</span>
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
                        <span class="button-text">Alle activiteiten</span>
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
                            <label for="text-input">De content van je activiteit</label>
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
                                Herhaal deze activiteit
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
                            <p>Bij sommige activiteiten is het nodig om een inschrijfformulier toe te voegen,
                                bijvoorbeeld
                                om bij te houden hoeveel teams meedoen met een pubquiz, of hoeveel kinderen mee willen
                                lopen
                                in een spooktocht. Als je niet zeker weet wat elk type veld doet, klik dan op <span
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
                        <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">event_note</span>Extra's
                        </h2>
                        <div class="w-100 d-flex flex-row-responsive align-items-end gap-2">

                            <div class="w-100">
                                <label for="presence" class="col-form-label">
                                    Laat de gebruikers zich aan of af melden voor deze activiteit
                                    <span class="required-form">*</span>
                                </label>

                                <select id="presence" class="form-select @error('presence') is-invalid @enderror"
                                        name="presence">
                                    <option @if($activity->presence === "0") selected @endif value="0">Nee</option>
                                    <option @if($activity->presence !== "0") selected @endif value="1">Ja</option>
                                </select>
                                @error('presence')
                                <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
                                @enderror
                            </div>
                            <div id="date-container" class="w-100 mt-2">
                                <label for="presence-date" class="col-form-label">Deadline om aan- of af te melden:
                                    (wanneer dit veld leeg gelaten wordt zal er geen deadline op zitten)</label>
                                <input type="datetime-local" id="presence-date" name="presence-date"
                                       value="{{ $activity->presence }}"
                                       class="form-control @error('presence-date') is-invalid @enderror">
                                @error('presence-date')
                                <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
                                @enderror
                            </div>
                        </div>

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
                                <label for="public" class="col-form-label ">Maak dit een openbare activiteit (Het zal
                                    ook op
                                    de
                                    normale website komen te staan als activiteit) <span
                                        class="required-form">*</span></label>
                                <select id="public" type="text"
                                        class="form-select @error('public') is-invalid @enderror"
                                        name="public">
                                    <option @if($activity->public === false) selected @endif value="0">Nee</option>
                                    <option @if($activity->public === true) selected @endif value="1">Ja</option>
                                </select>
                                @error('public')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>


                            <div class="w-100">
                                <label for="price" class="col-form-label ">Prijs (als je dit
                                    veld leeg laat vermelden we de prijs niet en voor een gratis evenement kun je 0
                                    invullen)</label>
                                <div class="input-group">
                                    <span class="input-group-text">€</span>
                                    <input name="price" type="number" class="form-control" aria-label="price"
                                           aria-describedby="price" id="price" value="{{ $activity->price }}">
                                </div>
                                @error('price')
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
                            <label for="location" class="col-form-label ">Locatie, bijvoorbeeld Tramstraat 54a"</label>
                            <input name="location" type="text" class="form-control" id="location"
                                   value="{{ $activity->location }}"
                            >
                            @error('location')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        @if(!isset($lesson))

                            <div class="d-flex flex-column">
                                <label for="organisator" class="col-form-label ">Organisatie, bijvoorbeeld "Bosvrienden"/label>
                                <input name="organisator" type="text" class="form-control" id="organisator"
                                       value="{{ $activity->organisator }}"
                                >
                                @error('organisator')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>


                            <div class="mt-4">
                                <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">for_you</span>Rollen
                                    & Mensen</h2>
                                <p>Als je geen rollen of gebruikers toevoegt aan je activiteit wordt deze voor iedereen
                                    zichtbaar!</p>
                                <p>Om een activiteit aan te maken voor bijvoorbeeld de Zeeverkenners, kun je de rol
                                    "Zeeverkenners" kiezen onder "Eventuele rollen waarvoor je activiteit geldt".</p>
                                <p>Vergeet niet om ook de leiding rol toe te voegen als je iets aan je speltak
                                    toevoegd!</p>
                                <div class="d-flex flex-column mt-4 mb-2">
                                    <div class="accordion" id="accordionExample">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#collapseOne"
                                                        aria-expanded="false" aria-controls="collapseOne">
                                                    <label for="select-roles" class="col-md-4 col-form-label ">Eventuele
                                                        rollen
                                                        waarvoor je activiteit geldt</label>
                                                </button>
                                            </h2>

                                            <div id="collapseOne" class="accordion-collapse collapse"
                                                 data-bs-parent="#accordionExample">
                                                <div class="accordion-body">
                                                    <div class="custom-select">
                                                        @php
                                                            $selectedRoles = explode(',', $activity->roles);
                                                        @endphp

                                                        <select id="select-roles" class="d-none" name="roles[]"
                                                                multiple>
                                                            @foreach($all_roles as $role)
                                                                <option data-description="{{ $role->description }}"
                                                                        value="{{ $role->id }}"
                                                                        @if(in_array($role->id, $selectedRoles)) selected @endif>
                                                                    {{ $role->role }}
                                                                </option>
                                                            @endforeach
                                                        </select>


                                                    </div>
                                                    <div class="d-flex flex-wrap gap-1" id="button-container"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#collapseTwo" aria-expanded="false"
                                                        aria-controls="collapseTwo">
                                                    <label for="users" class="col-md-4 col-form-label ">Eventuele
                                                        gebruikers
                                                        waarvoor je activiteit
                                                        geldt</label>
                                                </button>
                                            </h2>
                                            <div id="collapseTwo" class="accordion-collapse collapse"
                                                 data-bs-parent="#accordionExample">
                                                <div class="accordion-body">
                                                    <input id="users" name="users" type="text"
                                                           value="{{ $activity->users }}"
                                                           class="user-select-window user-select-none form-control"
                                                           placeholder="Kies een gebruiker uit de lijst"
                                                           aria-label="user" aria-describedby="basic-addon1">
                                                    <div class="user-select-window-popup d-none mt-2"
                                                         style="position: unset; display: block !important;">
                                                        <h3>Selecteer gebruikers</h3>
                                                        <div class="input-group">
                                                            <label class="input-group-text" id="basic-addon1">
                                                                <span
                                                                    class="material-symbols-rounded">search</span></label>
                                                            <input type="text" data-type="multiple" data-stayopen="true"
                                                                   class="user-select-search form-control"
                                                                   placeholder="Zoeken op naam, email, adres etc."
                                                                   aria-label="Zoeken" aria-describedby="basic-addon1"

                                                            >
                                                        </div>
                                                        <div class="user-list no-scrolbar">
                                                            <div
                                                                class="w-100 h-100 d-flex justify-content-center align-items-center"><span
                                                                    class="material-symbols-rounded rotating"
                                                                    style="font-size: xxx-large">progress_activity</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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


                    <div class="d-flex align-items-center flex-row mt-3 gap-2">
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

@endsection

