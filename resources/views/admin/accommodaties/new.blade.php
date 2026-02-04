@extends('layouts.dashboard')
@include('partials.editor')

@vite(['resources/js/texteditor.js', 'resources/css/texteditor.css'])

@php
    use Carbon\Carbon;
    Carbon::setLocale('nl');
@endphp

@section('content')
    <div class="container col-md-11">
        <h1>Nieuwe accommodatie</h1>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.accommodaties') }}">Accommodaties</a></li>
                <li class="breadcrumb-item active" aria-current="page">Nieuwe accommodatie</li>
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
                <form method="POST" action="{{ route('admin.accommodaties.new.save') }}" enctype="multipart/form-data"
                      id="accommodation-form">
                    @csrf

                    {{-- Hidden fields for structured data --}}
                    {{-- These are now updated continuously by JS --}}
                    <input type="hidden" name="temp_image_ids" id="temp_image_ids" value="">
                    <input type="hidden" name="temp_icon_data" id="temp_icon_data" value="">
                    <input type="hidden" name="prices_to_add" id="prices_to_add" value="">

                    {{-- Legacy/Fallback fields --}}
                    <input type="hidden" name="icon_data" id="icon_data" value="{{ old('icon_data', '[]') }}">
                    <input type="hidden" name="temp_icon_ids" id="temp_icon_ids" value="">

                    <div class="d-flex flex-column mb-3">
                        <label for="name" class="col-md-4 col-form-label ">Naam van de accommodatie</label>
                        <input name="name" type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" value="{{ old('name') }}">
                        @error('name')
                        <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-9 d-flex flex-column mb-3">
                            <label for="type_input" class="col-form-label ">Type accommodatie</label>
                            <input placeholder="e.g. Kleine groepsruimte" name="type" type="text" class="form-control @error('type') is-invalid @enderror" id="type_input" value="{{ old('type') }}">
                            @error('type')
                            <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-3 d-flex flex-column mb-3">
                            <label for="order_input" class="col-form-label">Volgorde (Index)</label>
                            <input name="order" type="number" class="form-control @error('order') is-invalid @enderror" id="order_input" value="{{ old('order') }}">
                            <small class="text-muted">Lager nummer = hoger in de lijst</small>
                            @error('order')
                            <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="image" class="col-md-4 col-form-label ">Hoofdafbeelding (wordt vertoond op alle
                            pagina's)</label>
                        <input class="form-control mt-2 @error('image') is-invalid @enderror" id="image" type="file"
                               name="image" accept="image/*">
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

                    <div class="mt-4">
                        <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">date_range</span>Openingstijden
                        </h2>
                        <div class="d-flex flex-row-responsive gap-2 justify-content-between align-items-center">
                            <div class="w-100">
                                <label for="min_check_in" class="col-md-4 col-form-label ">Openingstijd <span
                                        class="required-form">*</span></label>
                                <input id="min_check_in" value="{{ old('min_check_in', '08:00') }}" type="time"
                                       class="form-control @error('date_start') is-invalid @enderror" name="date_start">
                                @error('min_check_in')
                                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                                @enderror
                            </div>

                            <div class="w-100">
                                <label for="max_check_in" class="col-md-4 col-form-label ">Sluitingstijd <span
                                        class="required-form">*</span></label>
                                <input id="max_check_in" value="{{ old('max_check_in', '23:00') }}" type="time"
                                       class="form-control @error('max_check_in') is-invalid @enderror" name="date_end">
                                @error('max_check_in')
                                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                                @enderror
                            </div>

                        </div>
                        <div class="w-100">
                            <label for="min_duration_minutes" class="col-md-4 col-form-label ">Minimale duur van een
                                boeking (in minuten) <span
                                    class="required-form">*</span></label>
                            <input id="min_duration_minutes" value="{{ old('min_duration_minutes', 120) }}"
                                   type="number"
                                   class="form-control @error('min_duration_minutes') is-invalid @enderror"
                                   name="date_end">
                            @error('min_duration_minutes')
                            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                            @enderror
                        </div>
                    </div>

                    {{-- PRICE EDITOR --}}
                    <div class="mb-5 mt-5 p-3 border rounded-3 bg-white">
                        <h2 class="flex-row gap-3"><span
                                class="material-symbols-rounded me-2">attach_money</span>Prijsconfiguratie
                        </h2>

                        <div class="d-flex flex-column flex-md-row gap-3 align-items-end mb-4 p-3 border rounded">
                            <div class="flex-grow-1">
                                <label for="new_price_name" class="form-label mb-1">Naam (bv. "Standaardtarief")</label>
                                <input type="text" id="new_price_name" class="form-control"
                                       placeholder="Naam van prijscomponent">
                            </div>
                            <div>
                                <label for="new_price_amount" class="form-label mb-1">Bedrag / %</label>
                                <input type="number" step="0.01" id="new_price_amount" class="form-control"
                                       placeholder="0.00">
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

                    {{-- IMAGE CAROUSEL EDITOR --}}
                    <div class="mb-5 p-3 border rounded-3 bg-white">
                        <h2 class="flex-row gap-3"><span
                                class="material-symbols-rounded me-2">image</span>Afbeeldingencarousel
                        </h2>

                        <div class="d-flex align-items-center gap-3 mb-4">
                            <label for="carousel_images_input" class="form-label mb-0">Nieuwe afbeeldingen
                                uploaden:</label>
                            <input class="form-control" type="file" id="carousel_images_input" multiple
                                   accept="image/*">
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


                    {{-- ICON/FEATURE EDITOR --}}
                    <div class="mb-5 p-3 border rounded-3 bg-white">
                        <h2 class="flex-row gap-3"><span
                                class="material-symbols-rounded me-2">chess_bishop_2</span>Iconen en kenmerken
                        </h2>

                        <div class="d-flex flex-column flex-md-row gap-3 align-items-end mb-4 p-3 border rounded">
                            <div class="flex-grow-1">
                                <label for="new_icon_text" class="form-label mb-1">Tekst (bv. "Thee en water
                                    inclusief")</label>
                                <input type="text" id="new_icon_text" class="form-control"
                                       placeholder="Tekst voor het kenmerk">
                            </div>
                            <div class="flex-grow-1">
                                <label for="new_icon_file" class="form-label mb-1">Icoontje (.svg)</label>
                                <input type="file" id="new_icon_file" class="form-control" accept="image/svg+xml">
                                <small id="icon-file-error" class="text-danger"></small>
                            </div>
                            <button type="button" id="add-icon-btn" class="btn btn-primary" style="min-width: 100px;">
                                Toevoegen
                            </button>
                        </div>
                        @error('temp_icon_data')
                        <span id="error-temp_icon_data" class="text-danger small mb-3 d-block">{{ $message }}</span>
                        @enderror

                        <div id="icon-list-container" class="row row-cols-1 row-cols-sm-2 g-4">
                        </div>
                        <p id="icon-list-placeholder" class="text-muted m-0 p-3 text-center" style="display: block;">
                            Nog geen iconen/kenmerken toegevoegd.
                        </p>
                    </div>

                    <button
                        type="submit"
                        id="save-button"
                        class="btn btn-success mt-3 d-flex align-items-center justify-content-center">
                        <span class="button-text">Opslaan</span>
                        <span style="display: none" class="loading-spinner spinner-border spinner-border-sm"
                              aria-hidden="true"></span>
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
        // --- Configuration ---
        const TEMP_IMAGE_UPLOAD_URL = '/dashboard/accommodaties/temp/image';
        const TEMP_ICON_UPLOAD_URL = '/dashboard/accommodaties/temp/icon';
        const TEMP_IMAGE_DELETE_URL = '/dashboard/accommodaties/temp/image/';
        const TEMP_ICON_DELETE_URL = '/dashboard/accommodaties/temp/icon/';
        // ------------------------------------------------------------------

        // Data for repopulating form on error
        const PHP_TEMP_IMAGES = @json($tempImages ?? []);
        const PHP_TEMP_ICONS = @json($tempIcons ?? []);
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

                // LIVE UPDATE HIDDEN FIELD
                this.updateHiddenField();
            },

            updateHiddenField() {
                const json = JSON.stringify(this.prices);
                document.getElementById('prices_to_add').value = json;
                console.log('Price Data Updated:', json);
            },

            getTypeText(type) {
                switch (parseInt(type, 10)) {
                    case 0:
                        return 'Standaard Prijs (€)';
                    case 1:
                        return 'BTW';
                    case 2:
                        return 'Vaste Korting (€)';
                    case 3:
                        return 'Extra Kosten (excl.)';
                    case 4:
                        return 'Percentage Korting (%)';
                    default:
                        return 'Onbekend';
                }
            },

            createPriceRow(price, index) {
                const wrapper = createElement('div', {className: 'd-flex align-items-center gap-3 p-2 border rounded flex-wrap'});
                const nameEl = createElement('div', {className: 'flex-grow-1'}, `<strong>${price.name}</strong>`);
                const amountText = `${(parseInt(price.type, 10) === 1 || parseInt(price.type, 10) === 4) ? '' : '€ '}${parseFloat(price.amount).toFixed(2)}${(parseInt(price.type, 10) === 1 || parseInt(price.type, 10) === 4) ? '%' : ''}`;
                const amountEl = createElement('div', {
                    className: 'fw-bold',
                    style: 'min-width: 80px; text-align: right;'
                }, amountText);
                const typeEl = createElement('div', {
                    className: 'text-muted small',
                    style: 'min-width: 150px;'
                }, this.getTypeText(price.type));
                const removeBtn = createElement('button', {
                    type: 'button',
                    className: 'btn btn-sm btn-outline-danger',
                    title: 'Verwijder prijs',
                    onclick: () => this.removePrice(index)
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

                const newPrice = {name, amount, type};
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
                    preview: `/uploads/accommodaties/temp/images/${item.image}`
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

                // LIVE UPDATE HIDDEN FIELD
                this.updateHiddenField();
            },

            updateHiddenField() {
                const ids = this.images
                    .filter(img => img.db_id !== null)
                    .map(img => img.db_id);
                const val = ids.join(',');
                document.getElementById('temp_image_ids').value = val;
                console.log('Image IDs Updated:', val);
            },

            createImageCard(image, index) {
                const card = createElement('div', {className: 'card', style: 'width: 200px;'});
                const container = createElement('div', {className: 'image-card-container'});

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

                fetch(TEMP_IMAGE_UPLOAD_URL, {method: 'POST', body: formData,})
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            imageObj.db_id = data.data.id;
                            imageObj.file = null;
                            this.updateHiddenField(); // UPDATE AGAIN ON SUCCESS
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
                    headers: {'X-CSRF-TOKEN': CSRF_TOKEN}
                }).catch(err => console.error("Failed to delete temp image", err));
            }
        };

        // ===========================================
        // Icon Feature Editor
        // ===========================================
        const IconEditor = {
            icons: [],
            container: document.getElementById('icon-list-container'),
            placeholder: document.getElementById('icon-list-placeholder'),
            newIconTextEl: document.getElementById('new_icon_text'),
            newIconFileEl: document.getElementById('new_icon_file'),
            errorEl: document.getElementById('icon-file-error'),

            loadOldData() {
                const oldIconData = JSON.parse(document.getElementById('icon_data').value || '[]');
                const savedIconMap = new Map();

                PHP_TEMP_ICONS.forEach(item => {
                    savedIconMap.set(item.temp_id, {
                        db_id: item.id,
                        preview: `/uploads/accommodaties/temp/icons/${item.icon}`
                    });
                });

                this.icons = oldIconData.map(oldItem => {
                    const savedItem = savedIconMap.get(oldItem.temp_id);
                    return {
                        ...oldItem,
                        db_id: savedItem ? savedItem.db_id : null,
                        preview: savedItem ? savedItem.preview : 'data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs='
                    };
                });
                this.render();
            },

            render() {
                this.container.innerHTML = '';
                this.placeholder.style.display = this.icons.length === 0 ? 'block' : 'none';
                this.icons.forEach((icon, index) => {
                    this.container.appendChild(this.createIconRow(icon, index));
                });

                // LIVE UPDATE HIDDEN FIELD
                this.updateHiddenField();
            },

            updateHiddenField() {
                // Prepare primary JSON data
                const data = this.icons
                    .filter(icon => icon.db_id !== null)
                    .map(icon => ({id: icon.db_id, text: icon.text}));

                const json = JSON.stringify(data);
                document.getElementById('temp_icon_data').value = json;

                // Also update legacy IDs for fallback/repopulation
                const ids = data.map(i => i.id).join(',');
                document.getElementById('temp_icon_ids').value = ids;

                // Update repopulation specific field
                const repopData = this.icons.map(ic => ({temp_id: ic.temp_id, text: ic.text}));
                document.getElementById('icon_data').value = JSON.stringify(repopData);

                console.log('Icon Data Updated:', json);
            },

            createIconRow(icon, index) {
                const col = createElement('div', {className: 'col'});
                const wrapper = createElement('div', {className: 'd-flex align-items-center gap-3 p-2 border rounded flex-wrap'});
                const img = createElement('img', {
                    src: icon.preview,
                    alt: `Icoon`,
                    style: 'width:32px;height:32px;object-fit:contain;'
                });

                const textInput = createElement('input', {
                    type: 'text',
                    className: 'form-control form-control-sm flex-grow-1',
                    value: icon.text,
                    oninput: (e) => {
                        icon.text = e.target.value;
                        this.updateHiddenField(); // Update text changes immediately
                    }
                });

                const removeBtn = createElement('button', {
                    type: 'button',
                    className: 'btn btn-sm btn-danger',
                    title: 'Verwijder kenmerk',
                    onclick: () => this.removeIcon(index)
                }, '&times;');

                wrapper.appendChild(img);
                wrapper.appendChild(textInput);
                wrapper.appendChild(removeBtn);
                col.appendChild(wrapper);
                return col;
            },

            addIcon() {
                this.errorEl.textContent = '';
                const file = this.newIconFileEl.files[0];
                const text = this.newIconTextEl.value.trim();
                if (!text || !file) {
                    this.errorEl.textContent = 'Voeg een icoontje en een tekst toe.';
                    return;
                }

                fileIdCounter++;
                const newTempId = 'icon-' + fileIdCounter;
                const newIcon = {
                    temp_id: newTempId,
                    db_id: null,
                    text: text,
                    file: file,
                    preview: URL.createObjectURL(file)
                };
                this.icons.push(newIcon);
                this.render();
                this.uploadFile(newIcon);
                this.newIconTextEl.value = '';
                this.newIconFileEl.value = '';
            },

            uploadFile(iconObj) {
                const formData = new FormData();
                formData.append('file', iconObj.file);
                formData.append('unique_id', iconObj.temp_id);
                formData.append('text', iconObj.text);
                formData.append('_token', CSRF_TOKEN);

                fetch(TEMP_ICON_UPLOAD_URL, {method: 'POST', body: formData,})
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            iconObj.db_id = data.data.id;
                            iconObj.file = null;
                            this.updateHiddenField(); // UPDATE AGAIN ON SUCCESS
                        } else {
                            alert('Icon upload failed: ' + data.message);
                            this.removeIconByTempId(iconObj.temp_id);
                        }
                    })
                    .catch(err => {
                        console.error('Network Error:', err);
                        this.removeIconByTempId(iconObj.temp_id);
                    });
            },

            removeIconByTempId(tempId) {
                const index = this.icons.findIndex(icon => icon.temp_id === tempId);
                if (index > -1) this.removeIcon(index);
            },

            removeIcon(index) {
                const iconObj = this.icons[index];
                if (iconObj.db_id !== null) {
                    this.deleteFile(iconObj.db_id);
                }
                this.icons.splice(index, 1);
                this.render();
            },

            deleteFile(dbId) {
                fetch(TEMP_ICON_DELETE_URL + dbId, {
                    method: 'DELETE',
                    headers: {'X-CSRF-TOKEN': CSRF_TOKEN}
                }).catch(err => console.error("Failed to delete temp icon", err));
            }
        };

        // ===========================================
        // Main Form Submission & Initialization
        // ===========================================
        document.addEventListener('DOMContentLoaded', () => {
            PriceEditor.render();
            ImageEditor.loadOldData();
            IconEditor.loadOldData();

            document.getElementById('add-price-btn').addEventListener('click', () => PriceEditor.addPrice());
            document.getElementById('carousel_images_input').addEventListener('change', e => ImageEditor.handleNewImages(e.target.files));
            document.getElementById('add-icon-btn').addEventListener('click', () => IconEditor.addIcon());

            const form = document.getElementById('accommodation-form');
            form.addEventListener('submit', function (e) {
                // Update description from editor
                document.getElementById('description').value = document.getElementById('text-input').innerHTML;

                // Force one last update to be sure
                PriceEditor.updateHiddenField();
                ImageEditor.updateHiddenField();
                IconEditor.updateHiddenField();

                const saveButton = document.getElementById('save-button');
                saveButton.disabled = true;
                saveButton.querySelector('.button-text').style.display = 'none';
                saveButton.querySelector('.loading-spinner').style.display = 'inline-block';
                saveButton.querySelector('.loading-text').style.display = 'inline-block';
            });
        });
    </script>
@endsection
