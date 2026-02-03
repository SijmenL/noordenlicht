@extends('layouts.dashboard')
@include('partials.editor')

@vite(['resources/js/texteditor.js', 'resources/css/texteditor.css'])

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

    <div class="container col-md-11">
        <h1>Nieuw product</h1>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.products') }}">Producten</a></li>
                <li class="breadcrumb-item active" aria-current="page">Nieuw product</li>
            </ol>
        </nav>

        {{-- Global Success/Error Messages --}}
        <div id="global-message-container">
            @if(Session::has('error'))
                <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
            @endif
            @if(Session::has('success'))
                <div class="alert alert-success" role="alert">{{ session('success') }}</div>
            @endif
        </div>

        @if ($errors->any())
            <div id="validation-error-summary" class="alert alert-danger">
                <p>Er zijn fouten opgetreden in de validatie:</p>
                <ul id="validation-error-list">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-light rounded-2 p-3">
            <div class="container">
                <form method="POST" action="{{ route('admin.products.new.save') }}" enctype="multipart/form-data" id="product-form">
                    @csrf

                    {{-- Hidden fields for structured data --}}
                    {{-- Updated continuously by JS --}}
                    <input type="hidden" name="temp_image_ids" id="temp_image_ids" value="">
                    <input type="hidden" name="prices_to_add" id="prices_to_add" value="">

                    {{-- Legacy/Unused fields (kept to prevent backend errors if it expects them) --}}
                    <input type="hidden" name="icon_data" id="icon_data" value="[]">
                    <input type="hidden" name="temp_icon_ids" id="temp_icon_ids" value="">

                    <div class="d-flex flex-column mb-3">
                        <label for="name" class="col-md-4 col-form-label ">Naam van het product</label>
                        <input name="name" type="text" class="form-control @error('name') is-invalid @enderror" id="name" value="{{ old('name') }}">
                        @error('name')
                        <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="d-flex flex-column mb-3">
                        <label for="type" class="col-md-4 col-form-label ">Type product</label>
                        <select id="type" class="form-select @error('type') is-invalid @enderror"
                                name="type">
                            <option @if(old('type') == "null") selected @endif value="null">Selecteer een optie</option>
                            <option @if(old('type') == "0") selected @endif value="0">Toevoegingen bij verhuur accommodatie</option>
                            <option @if(old('type') == "2") selected @endif value="2">Overnachting</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="image" class="col-md-4 col-form-label ">Hoofdafbeelding (wordt vertoond op alle pagina's)</label>
                        <input class="form-control mt-2 @error('image') is-invalid @enderror" id="image" type="file" name="image" accept="image/*">
                        @error('image')
                        <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <label for="text-input">Beschrijving <span
                                class="required-form">*</span></label>
                        <div class="editor-parent">
                            @yield('editor')
                            <div id="text-input" contenteditable="true" name="text-input"
                                 class="text-input">{!! old('description') !!}</div>
                            <small id="characters"></small>
                        </div>

                        <input id="content" name="description" type="hidden" value="{{ old('description') }}">

                        @error('description')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="mt-4 mb-4 bg-white rounded-3 border border-1 p-3" id="custom-form">
                        <div class="d-flex flex-row-responsive justify-content-between align-items-center">
                            <h2 class="flex-row gap-3"><span
                                    class="material-symbols-rounded me-2">app_registration</span>Formulier
                            </h2>
                            <a id="help-button"
                               class="btn btn-outline-dark d-flex align-items-center justify-content-center"
                               style="border: none">
                                <span class="material-symbols-rounded" style="font-size: xx-large">help</span>
                            </a>
                        </div>
                        <p>Bij sommige producten is extra informatie nodig, zoals verblijfdatum, ontbijt gegevens, etc. <span
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

                    {{-- PRICE EDITOR --}}
                    <div class="mb-4 p-3 border rounded-3 bg-white">
                         <h2 class="flex-row gap-3"><span
                                class="material-symbols-rounded me-2">attach_money</span>Prijsconfiguratie
                        </h2>


                        <div class="d-flex flex-column flex-md-row gap-3 align-items-end mb-4 p-3 border rounded">
                            <div class="flex-grow-1">
                                <label for="new_price_name" class="form-label mb-1">Naam (bv. "Basisprijs")</label>
                                <input type="text" id="new_price_name" class="form-control" placeholder="Naam van prijscomponent">
                            </div>
                            <div style="max-width: 150px;">
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
                        @error('prices_to_add')
                        <span class="text-danger small mb-3 d-block">{{ $message }}</span>
                        @enderror

                        <div id="price-list-container" class="d-flex flex-column gap-2 py-2 border-top border-bottom">
                            {{-- Prices will be rendered here by JavaScript --}}
                        </div>
                        <p id="price-list-placeholder" class="text-muted w-100 m-0 p-3">
                            Nog geen prijscomponenten toegevoegd.
                        </p>
                    </div>

                    <div class="mb-4 p-3 border rounded-3 bg-white">
                        <h2 class="flex-row gap-3"><span
                                class="material-symbols-rounded me-2">image</span>Afbeeldingencarousel bewerken
                        </h2>

                        <div class="d-flex align-items-center gap-3 mb-4">
                            <label for="carousel_images_input" class="form-label mb-0">Nieuwe afbeeldingen uploaden:</label>
                            <input class="form-control" type="file" id="carousel_images_input" multiple accept="image/*">
                        </div>
                        @error('temp_image_ids')
                        <span class="text-danger small mb-3 d-block">{{ $message }}</span>
                        @enderror

                        <div id="image-list-container" class="d-flex flex-wrap gap-3 py-2 border-top border-bottom">
                            <p id="image-list-placeholder" class="text-muted w-100 m-0 p-3">
                                Nog geen afbeeldingen toegevoegd aan de carousel.
                            </p>
                        </div>
                    </div>

                    <hr>

                    <button
                        type="submit"
                        id="save-button"
                        class="btn btn-success mt-3 d-flex align-items-center justify-content-center">
                        <span class="button-text">Opslaan</span>
                        <span style="display: none" class="loading-spinner spinner-border spinner-border-sm" aria-hidden="true"></span>
                        <span style="display: none" class="loading-text" role="status">Laden...</span>
                    </button>
                </form>
            </div>
        </div>

    </div>
    <style>
        .image-card-container {
            position: relative;
            overflow: hidden;
            border-radius: 0.375rem;
        }
        .image-card-container .remove-btn {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            z-index: 10;
            opacity: 0;
            transition: opacity 0.2s ease-in-out;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            line-height: 1;
        }
        .image-card-container:hover .remove-btn {
            opacity: 1;
        }
    </style>
    <script>
        // --- Configuration (Adapted for Products) ---
        const TEMP_IMAGE_UPLOAD_URL = '/dashboard/producten/temp/image';
        const TEMP_IMAGE_DELETE_URL = '/dashboard/producten/temp/image/';
        // ------------------------------------------------------------------

        const PHP_TEMP_IMAGES = @json($tempImages ?? []);
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
                    case 1: return 'BTW';
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
        // Image Carousel Editor
        // ===========================================
        const ImageEditor = {
            images: [],
            container: document.getElementById('image-list-container'),
            placeholder: document.getElementById('image-list-placeholder'),

            loadOldData() {
                const initialTempData = PHP_TEMP_IMAGES;

                this.images = initialTempData.map(item => ({
                    temp_id: item.temp_id,
                    db_id: item.id,
                    preview: `/uploads/products/temp/images/${item.image}` // Updated path for products
                }));

                if (this.images.length > 0) {
                    this.render();
                }
            },

            render() {
                this.container.innerHTML = '';
                if (this.images.length === 0) {
                    this.placeholder.style.display = 'block';
                } else {
                    this.placeholder.style.display = 'none';
                    this.images.forEach((image, index) => {
                        this.container.appendChild(this.createImageCard(image, index));
                    });
                }

                // LIVE UPDATE
                this.updateHiddenField();
            },

            updateHiddenField() {
                const ids = this.images
                    .filter(img => img.db_id !== null)
                    .map(img => img.db_id);
                document.getElementById('temp_image_ids').value = ids.join(',');
            },

            createImageCard(image, index) {
                const card = createElement('div', { className: 'card', style: 'width: 200px;' });
                const container = createElement('div', { className: 'image-card-container' });

                const img = createElement('img', {
                    src: image.preview,
                    className: 'card-img-top',
                    alt: 'Carousel image',
                    style: 'height: 120px; object-fit: cover;'
                });

                const removeBtn = createElement('button', {
                    type: 'button',
                    className: 'btn btn-danger btn-sm remove-btn',
                    title: 'Afbeelding verwijderen',
                    onclick: () => this.removeImage(index)
                }, '&times;');

                container.appendChild(img);
                container.appendChild(removeBtn);
                card.appendChild(container);

                return card;
            },

            handleNewImages(files) {
                Array.from(files).forEach(file => {
                    fileIdCounter++;
                    const newTempId = 'img-' + fileIdCounter;

                    const newImage = {
                        temp_id: newTempId,
                        db_id: null,
                        file: file,
                        preview: URL.createObjectURL(file)
                    };
                    this.images.push(newImage);
                    this.render();
                    this.uploadFile(newImage);
                });
                document.getElementById('carousel_images_input').value = '';
            },

            uploadFile(imageObj) {
                const formData = new FormData();
                formData.append('file', imageObj.file);
                formData.append('unique_id', imageObj.temp_id);
                formData.append('_token', CSRF_TOKEN);

                fetch(TEMP_IMAGE_UPLOAD_URL, { method: 'POST', body: formData, })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            imageObj.db_id = data.data.id;
                            imageObj.file = null;
                            this.updateHiddenField(); // UPDATE ON SUCCESS
                        } else {
                            alert('Image upload failed: ' + data.message);
                            this.removeImageByTempId(imageObj.temp_id);
                        }
                    })
                    .catch(err => {
                        console.error('Network Error:', err);
                        this.removeImageByTempId(imageObj.temp_id);
                    });
            },

            removeImageByTempId(tempId) {
                const index = this.images.findIndex(img => img.temp_id === tempId);
                if (index > -1) this.removeImage(index);
            },

            removeImage(index) {
                const imageObj = this.images[index];
                if (imageObj.db_id !== null) {
                    this.deleteFile(imageObj.db_id);
                }
                this.images.splice(index, 1);
                this.render();
            },

            deleteFile(dbId) {
                fetch(TEMP_IMAGE_DELETE_URL + dbId, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN }
                }).catch(err => console.error("Failed to delete temp image", err));
            }
        };


        // ===========================================
        // Main Form Submission & Initialization
        // ===========================================
        document.addEventListener('DOMContentLoaded', () => {
            PriceEditor.render(); // Render in case of old input
            ImageEditor.loadOldData();

            document.getElementById('add-price-btn').addEventListener('click', () => PriceEditor.addPrice());
            document.getElementById('carousel_images_input').addEventListener('change', e => ImageEditor.handleNewImages(e.target.files));
            // Removed IconEditor listeners as IconEditor is not used in Products

            const form = document.getElementById('product-form');
            form.addEventListener('submit', function (e) {
                document.getElementById('description').value = document.getElementById('text-input').innerHTML;

                // Force one last update to be sure
                PriceEditor.updateHiddenField();
                ImageEditor.updateHiddenField();

                const saveButton = document.getElementById('save-button');
                saveButton.disabled = true;
                saveButton.querySelector('.button-text').style.display = 'none';
                saveButton.querySelector('.loading-spinner').style.display = 'inline-block';
                saveButton.querySelector('.loading-text').style.display = 'inline-block';
            });
        });
    </script>
@endsection
