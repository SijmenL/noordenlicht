@props([
    'files',
    'breadcrumbs',
    'folderId',
    'isAdmin',
    'adminName',
    'nonAdminName',
    'storageUrl',
    'hasAdminViewers'
])

{{--{{ dd(ini_get('max_file_uploads')) }}--}}

<div id="popup-upload" class="popup d-none" style="margin-top: -122px">
    <div class="popup-body">
        <div class="tab-container no-scrolbar" style="overflow-x: auto;">
            <ul class="nav nav-tabs d-none" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab1-tab" data-bs-toggle="tab" data-bs-target="#tab1"
                            type="button" role="tab" aria-controls="tab1" aria-selected="true">Bestanden
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab2-tab" data-bs-toggle="tab" data-bs-target="#tab2" type="button"
                            role="tab" aria-controls="tab2" aria-selected="false">Hyperlink
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab3-tab" data-bs-toggle="tab" data-bs-target="#tab3" type="button"
                            role="tab" aria-controls="tab3" aria-selected="false">Map
                    </button>
                </li>
            </ul>
        </div>
        <div class="tab-content w-100 bg-light rounded p-4">
            <div class="tab-pane show active" id="tab1" role="tabpanel" aria-labelledby="tab1-tab">
                <h2>Bestanden uploaden</h2>
                <form id="upload-form" enctype="multipart/form-data">
                    @csrf
                    <div class="d-flex flex-column gap-3 align-items-center justify-content-center">
                        <div class="d-flex flex-row gap-4 align-items-center justify-content-center">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="upload-option" id="upload-files"
                                       value="files" checked>
                                <label class="form-check-label" for="upload-files">Bestanden</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="upload-option" id="upload-folder"
                                       value="folder">
                                <label class="form-check-label" for="upload-folder">Map</label>
                            </div>
                        </div>

                        <div class="form-group w-100 text-center">
                            <div id="files-upload-container">
                                <label for="files-input">Kies bestanden om te uploaden</label>
                                <input type="file" name="file[]" multiple class="form-control" id="files-input">
                            </div>
                            <div id="folder-upload-container" class="d-none">
                                <label for="folder-input">Kies een map om te uploaden</label>
                                <input type="file" name="folder_upload[]" multiple class="form-control"
                                       id="folder-input" directory webkitdirectory>
                            </div>
                            <input type="hidden" name="type" id="upload-type" value="0">
                            <input type="hidden" name="folder_id" value="{{ $folderId }}">
                            <input type="hidden" name="folder_paths" id="folder-paths-input">
                        </div>
                        @if($hasAdminViewers)
                        <div class="form-group">
                            <label for="access">Toegang</label>
                            <select name="access" class="form-control" id="access" >
                                <option value="teachers" selected>Alleen {{ $adminName }}</option>
                                <option value="all">Iedereen</option>
                            </select>
                        </div>
                        @else
                            <input type="hidden" name="access" value="all">
                        @endif
                    </div>
                    <div class="progress mt-3" style="display: none;">
                        <div class="progress-bar progress-bar-striped bg-success progress-bar-animated h-100"
                             role="progressbar" style="width: 0%;"></div>
                    </div>
                    <div id="upload-status" class="text-success mt-2" style="display: none;"></div>
                    <div class="button-container justify-content-center">
                        <button type="button" id="upload-button"
                                class="btn btn-success text-white d-flex align-items-center justify-content-center">
                            <span class="button-text">Uploaden</span>
                            <span class="loading-spinner spinner-border spinner-border-sm" style="display: none;"
                                  aria-hidden="true"></span>
                            <span class="loading-text" style="display: none;" role="status">Laden...</span>
                        </button>
                        <a class="popup-upload-button-close btn btn-outline-danger">Annuleren</a>
                    </div>
                </form>
            </div>

            <div class="text-start tab-pane" id="tab2" role="tabpanel" aria-labelledby="tab2-tab">
                <h2 class="text-center">Hyperlink toevoegen</h2>
                <p class="text-center">Vul een url in waarvan we een link kunnen maken. Het moet een geldige url zijn, inclusief <code>https://www.</code></p>
                <form method="post" action="{{ route('files.store', [$location, $locationId]) }}"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="d-flex flex-row-responsive w-100 gap-2 align-items-center justify-content-center">
                        <div class="form-group w-100">
                            <label for="file">Vul een url in</label>
                            <input type="text" name="file" class="form-control">
                            <input type="hidden" name="type" value="1">
                            <input type="hidden" name="folder_id" value="{{ $folderId }}">
                        </div>
                        <div class="form-group w-100">
                            <label for="title">Weergavenaam</label>
                            <input type="text" name="title" class="form-control">
                        </div>
                        @if($hasAdminViewers)
                        <div class="form-group w-100">
                            <label for="access">Toegang</label>
                            <select name="access" class="form-control" id="access">
                                <option value="all">Iedereen</option>
                                <option value="teachers">Alleen {{ $adminName }}</option>
                            </select>
                        </div>
                        @else
                            <input type="hidden" name="access" value="all">
                        @endif
                    </div>

                    <div class="button-container justify-content-center">
                        <button type="submit"
                                class="btn btn-success text-white d-flex align-items-center justify-content-center">
                            <span class="button-text">Hyperlink toevoegen</span>
                            <span class="loading-spinner spinner-border spinner-border-sm" style="display: none;"
                                  aria-hidden="true"></span>
                            <span class="loading-text" style="display: none;" role="status">Laden...</span>
                        </button>
                        <a class="popup-upload-button-close btn btn-outline-danger">Annuleren</a>
                    </div>
                </form>
            </div>

            <div class="text-start tab-pane" id="tab3" role="tabpanel" aria-labelledby="tab3-tab">
                <h2 class="text-center">Map toevoegen</h2>
                <p class="text-center">Vul de naam van je map in</p>
                <form method="post" action="{{ route('files.store', [$location, $locationId]) }}"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="d-flex flex-row-responsive w-100 gap-2 align-items-center justify-content-center">
                        <div class="form-group w-100">
                            <label for="title">Naam</label>
                            <input type="text" name="title" class="form-control">
                            <input type="hidden" name="type" value="2">
                            <input type="hidden" name="folder_id" value="{{ $folderId }}">
                        </div>
                        @if($hasAdminViewers)
                        <div class="form-group w-100">
                            <label for="access">Toegang</label>
                            <select name="access" class="form-control" id="access">
                                <option value="all">Iedereen</option>
                                <option value="teachers">Alleen {{ $adminName }}</option>
                            </select>
                        </div>
                        @else
                            <input type="hidden" name="access" value="all">
                        @endif
                    </div>

                    <div class="button-container justify-content-center">
                        <button type="submit"
                                class="btn btn-success text-white d-flex align-items-center justify-content-center">
                            <span class="button-text">Map toevoegen</span>
                            <span class="loading-spinner spinner-border spinner-border-sm" style="display: none;"
                                  aria-hidden="true"></span>
                            <span class="loading-text" style="display: none;" role="status">Laden...</span>
                        </button>
                        <a class="popup-upload-button-close btn btn-outline-danger">Annuleren</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div>
    <div class="file-manager bg-light p-2 rounded">
        <div class="file-tools">
            <div style="transform: translateY(9px)">
                @if (!isset($folderId))
                    <h2 class="no-mobile">Bestanden</h2>
                @else
                    <h2 class="no-mobile"><a>{{ last($breadcrumbs)['name'] }}</a></h2>
                @endif

                <div aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        @if(isset($folderId))
                            @foreach ($breadcrumbs as $breadcrumb)
                                @if (!$loop->last)
                                    <li class="breadcrumb-item">
                                        <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['name'] }}</a>
                                    </li>
                                @else
                                    <li class="breadcrumb-item active">
                                        <a>{{ $breadcrumb['name'] }}</a>
                                    </li>
                                @endif
                            @endforeach
                        @endif
                    </ol>
                </div>
            </div>

            @if($isAdmin)
                <div class="dropdown d-flex flex-row-reverse" style="min-width: 50%">
                    <button
                        class="btn btn-outline-dark d-flex flex-row gap-2 align-items-center justify-content-center"
                        type="button" style="display: flex !important;"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="material-symbols-rounded">add</span> <span>Nieuw toevoegen</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a id="popup-map-button" class="dropdown-item">
                                Map</a></li>
                        <li><a id="popup-upload-button" class="dropdown-item">
                                Bestand(en) uploaden</a></li>
                        <li><a id="popup-hyperlink-button" class="dropdown-item">
                                Hyperlink toevoegen</a></li>
                    </ul>
                </div>
            @endif
        </div>

        @php

        $accessCount = 0;
            foreach ($files as $file) {
               if(!($file->access === 'teachers' && !$isAdmin)) {
                    $accessCount++;
               }
            }

        @endphp
        @if($accessCount > 0)
            <table class="table table-borderless">
                <thead>
                <tr class="bg-light">
                    <th class="bg-light"></th>
                    <th class="bg-light">Naam</th>
                    <th class="no-mobile bg-light">Bestandsgrootte</th>
                    @if($isAdmin)
                        <th class="no-mobile bg-light">Gewijzigd</th>
                        @if($hasAdminViewers)
                        <th class="bg-light">Toegang</th>
                        @endif
                    @endif
                    <th class="bg-light">Opties</th>
                </tr>
                </thead>
                <tbody>
                @foreach($files as $file)
                    @if(!($file->access === 'teachers' && !$isAdmin))
                        @php
                            $extension = pathinfo($file->file_name, PATHINFO_EXTENSION);
                            $isImage = in_array(strtolower($extension), ["jpg", "jpeg", "png", "gif", "webp", "svg", "bmp", "ico", "avif"]);
                            $isVideo = in_array(strtolower($extension), ["mp4", "webm", "ogv", "mov"]);
                            $isAudio = in_array(strtolower($extension), ["mp3", "wav", "ogg", "oga", "aac", "flac"]);
                            $isPdf = in_array(strtolower($extension), ["html", "htm", "xhtml", "shtml", "txt", "md", "csv", "log", "pdf", "odt", "ods", "odp", "json", "xml", "yaml", "yml", "cs", "showm"]);
                            $isOffice = in_array(strtolower($extension), ["doc", "docx", "xls", "xlsx", "ppt", "pptx"]);

                            $icon = 'unknown.webp';

                                if ($file->type === 0 || $file->type === null) {
                                    switch(strtolower($extension)) {
                                        case 'pdf': $icon = 'pdf.webp'; break;
                                        case 'jpeg':
                                        case 'jpg': $icon = 'jpg.webp'; break;
                                        case 'png': $icon = 'png.webp'; break;
                                        case 'webp': $icon = 'webp.webp'; break;
                                        case 'zip': $icon = 'zip.webp'; break;
                                        case 'docx':
                                        case 'doc': $icon = 'doc.webp'; break;
                                        case 'pptx':
                                        case 'ppt': $icon = 'ppt.webp'; break;
                                        case 'mp4': $icon = 'mp4.webp'; break;
                                        case 'mov': $icon = 'mov.webp'; break;
                                        case 'mp3': $icon = 'mp3.webp'; break;
                                        case 'wav': $icon = 'wav.webp'; break;
                                        case 'xlsx': $icon = 'xlsx.webp'; break;
                                        case 'showm': $icon = 'showm.webp'; break;
                                        case 'exe': $icon = 'exe.webp'; break;
                                        case 'html': $icon = 'html.webp'; break;
                                        case 'css': $icon = 'css.webp'; break;
                                        case 'js': $icon = 'js.webp'; break;
                                        case 'psd':
                                        case 'psb': $icon = 'psd.webp'; break;
                                        case 'ai':
                                        case 'bmp':
                                        case 'eps':
                                        case 'svg': $icon = 'ai.webp'; break;
                                        default: $icon = 'unknown.webp'; break;
                                    }
                                } elseif ($file->type === 1) {
                                    $icon = 'url.webp';
                                } elseif ($file->type === 2) {
                                    $icon = 'folder.webp';
                                }
                                $iconPath = asset('/files/file-icons/' . $icon);
                        @endphp
                        <tr class="file @if($isImage) file-image @endif @if($isVideo) file-video @endif @if($isAudio) file-audio @endif @if($isPdf) file-pdf @endif @if($isOffice) file-office @endif" style="cursor: pointer"
                             data-link="@if($file->type === 2)?folder={{ $file->id }} @elseif($file->type === 0 || $file->type === null) {{ $storageUrl . '/' . $file->file_path }} @else{{ $file->file_path }} @endif"
                             data-target="@if($file->type === 2) _self @else _blank @endif"
                             @if($isImage)
                                 data-is-image="true"
                             data-image-src="{{ $storageUrl . '/' . $file->file_path }}"
                            @endif
                            @if($isVideo)
                                data-is-video="true"
                            data-video-src="{{ $storageUrl . '/' . $file->file_path }}"
                            @endif
                            @if($isAudio)
                                data-is-audio="true"
                            data-audio-src="{{ $storageUrl . '/' . $file->file_path }}"
                            @endif
                            @if($isPdf)
                                data-is-pdf="true"
                            data-pdf-src="{{ $storageUrl . '/' . $file->file_path }}"
                            @endif
                            @if($isOffice)
                                data-is-office="true"
                            data-office-src="{{ $storageUrl . '/' . $file->file_path }}"
                            @endif
                        >

                        <td>
                                    <img style="width: clamp(25px, 50px, 5vw)" class="m-0" alt="file"
                                         src="{{ asset('/files/file-icons/'.$icon) }}">
                            </td>
                            <td>
                                <span>{{ $file->file_name }}</span>
                            </td>
                            <td class="no-mobile">
                                @if(isset($file->file_path) && $file->file_path !== "")
                                    @if(Storage::disk('public')->exists($file->file_path))
                                        <span>{{ number_format(Storage::disk('public')->size($file->file_path) / 1024 / 1024, 2) }} MB</span>
                                    @endif
                                @endif
                            </td>
                            @if($isAdmin)
                                <td class="no-mobile">
                                    <span>{{ $file->updated_at }}</span>
                                </td>
                                @if($hasAdminViewers)
                                <td>
                                    @if($file->access === 'teachers')
                                        <span>{{ $adminName }}</span>
                                    @else
                                        <span>
                                                {{ $nonAdminName }}</span>
                                    @endif
                                </td>
                                @endif
                            @endif
                            <td class="has-dropdown" style="cursor: default">
                                @if($isAdmin)
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                            Opties
                                        </button>
                                        <ul class="dropdown-menu">
                                            @if($file->type === 0 || $file->type === null)
                                                <li><a class="dropdown-item"
                                                       href="{{ route('files.download', ['file' => $file->id]) }}">Downloaden</a>
                                                </li>
                                            @elseif($file->type === 2)
                                                <li><a class="dropdown-item"
                                                       href="{{ route('files.zip', ['folder' => $file->id]) }}">Downloaden
                                                        als ZIP</a></li>
                                            @endif
                                                @if($hasAdminViewers)
                                            @if($file->access === 'teachers')
                                                <li class="w-100"><a class="dropdown-item text-primary"
                                                                     href="{{ route('files.toggle-access', [$location, $file->id]) }}">Maak
                                                        beschikbaar voor {{ $nonAdminName }}</a></li>
                                            @endif
                                            @if($file->access === 'all' || $file->access === '')
                                                <li class="w-100"><a class="dropdown-item text-primary"
                                                                     href="{{ route('files.toggle-access', [$location, $file->id]) }}">Maak
                                                        alleen beschikbaar voor {{ $adminName }}</a></li>
                                            @endif
                                                @endif
                                            <li class="w-100">
                                                <form
                                                    action="{{ route('files.destroy', [$location, $file->id]) }}"
                                                    method="POST" class="d-inline-block w-100">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">Verwijderen
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                @else
                                    @if($file->type === 0 || $file->type === null)
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                Opties
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li class="w-100">
                                                    <a href="{{ route('files.download', ['file' => $file->id]) }}"
                                                       class="dropdown-item">
                                                        Downloaden
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    @elseif($file->type === 2)
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                Opties
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li class="w-100">
                                                    <a href="{{ route('files.zip', ['folder' => $file->id]) }}"
                                                       class="dropdown-item">
                                                        Downloaden als ZIP
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        @else
            <div class="alert alert-warning d-flex align-items-center mt-4" role="alert">
                <span class="material-symbols-rounded me-2">cloud_off</span>Er zijn nog geen bestanden
                toegevoegd aan
                deze map.
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        let uploadPopup = document.getElementById('popup-upload');
        let uploadPopupButton = document.getElementById('popup-upload-button');
        let hyperlinkPopupButton = document.getElementById('popup-hyperlink-button');
        let mapPopupButton = document.getElementById('popup-map-button');
        let uploadPopupButtonClose = document.getElementsByClassName('popup-upload-button-close');
        let uploadForm = document.getElementById('upload-form');
        let uploadButton = document.getElementById('upload-button');
        let filesInput = document.getElementById('files-input');
        let folderInput = document.getElementById('folder-input');
        let uploadOptionRadios = document.querySelectorAll('input[name="upload-option"]');
        let filesUploadContainer = document.getElementById('files-upload-container');
        let folderUploadContainer = document.getElementById('folder-upload-container');
        let progressBar = document.querySelector('.progress-bar');
        let progressContainer = document.querySelector('.progress');
        let uploadStatus = document.getElementById('upload-status');
        let uploadTypeInput = document.getElementById('upload-type');
        let folderPathsInput = document.getElementById('folder-paths-input');
        let xhr = null;

        function showTab(tabId) {
            const tabs = ['tab1', 'tab2', 'tab3'];
            tabs.forEach(tab => {
                document.getElementById(tab + '-tab').classList.remove('active');
                document.getElementById(tab).classList.remove('show', 'active');
            });
            document.getElementById(tabId + '-tab').classList.add('active');
            document.getElementById(tabId).classList.add('show', 'active');
            uploadPopup.classList.remove('d-none');
        }

        if (uploadPopupButton !== null && hyperlinkPopupButton !== null && mapPopupButton !== null) {
            uploadPopupButton.addEventListener('click', () => showTab('tab1'));
            hyperlinkPopupButton.addEventListener('click', () => showTab('tab2'));
            mapPopupButton.addEventListener('click', () => showTab('tab3'));
        }

        Array.from(uploadPopupButtonClose).forEach(button => {
            button.addEventListener('click', function () {
                if (xhr) {
                    xhr.abort();
                }
                uploadPopup.classList.add('d-none');
                progressContainer.style.display = 'none';
                progressBar.style.width = '0%';
                uploadStatus.textContent = '';
                uploadForm.reset();
                uploadButton.disabled = false;
                uploadButton.classList.remove('loading');
                document.querySelector('#upload-button .button-text').style.display = 'inline-block';
                document.querySelector('#upload-button .loading-spinner').style.display = 'none';
                document.querySelector('#upload-button .loading-text').style.display = 'none';
            });
        });

        uploadOptionRadios.forEach(radio => {
            radio.addEventListener('change', function () {
                if (this.value === 'files') {
                    filesUploadContainer.classList.remove('d-none');
                    folderUploadContainer.classList.add('d-none');
                    uploadTypeInput.value = '0';
                } else {
                    filesUploadContainer.classList.add('d-none');
                    folderUploadContainer.classList.remove('d-none');
                    uploadTypeInput.value = '3';
                }
            });
        });

        uploadButton.addEventListener('click', function () {
            let files = [];
            let isFolderUpload = uploadTypeInput.value === '3';
            let inputElement;

            if (isFolderUpload) {
                inputElement = folderInput;
                files = inputElement.files;

                if (files.length === 0) {
                    uploadStatus.style.display = 'block';
                    uploadStatus.textContent = 'Het uploaden van een lege map wordt niet direct ondersteund door de browser. Gebruik de "Map toevoegen" optie om een lege map aan te maken.';
                    return;
                }
            } else {
                inputElement = filesInput;
                files = inputElement.files;

                if (files.length === 0) {
                    uploadStatus.style.display = 'block';
                    uploadStatus.textContent = 'Selecteer bestanden om te uploaden.';
                    return;
                }
            }

            this.disabled = true;
            this.classList.add('loading');
            this.querySelector('.button-text').style.display = 'none';
            this.querySelector('.loading-spinner').style.display = 'inline-block';
            this.querySelector('.loading-text').style.display = 'inline-block';

            let formData = new FormData(uploadForm);
            let totalSize = 0;
            let filePaths = [];

            formData.delete('file[]');
            formData.delete('folder_upload[]');

            for (let i = 0; i < files.length; i++) {
                let file = files[i];
                totalSize += file.size;

                if (isFolderUpload) {
                    formData.append('folder_upload[]', file);
                    filePaths.push(file.webkitRelativePath);
                } else {
                    formData.append('file[]', file);
                }
            }

            if (isFolderUpload) {
                formData.set('folder_paths', JSON.stringify(filePaths));
            }

            progressContainer.style.display = 'block';
            uploadStatus.style.display = 'block';
            progressBar.style.width = '0%';
            uploadStatus.textContent = `0 MB / ${(totalSize / 1024 / 1024).toFixed(2)} MB`;

            xhr = new XMLHttpRequest();
            xhr.open('POST', "{{ route('files.store', [$location, $locationId]) }}", true);
            xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

            xhr.upload.addEventListener('progress', function (e) {
                if (e.lengthComputable) {
                    let uploaded = (e.loaded / 1024 / 1024).toFixed(2);
                    let total = (e.total / 1024 / 1024).toFixed(2);
                    progressBar.style.width = `${(e.loaded / e.total) * 100}%`;
                    uploadStatus.textContent = `${uploaded} MB / ${total} MB`;
                }
            });

            xhr.onload = function () {
                const button = document.getElementById('upload-button');
                if (xhr.status === 200) {
                    progressContainer.style.display = 'none';
                    progressBar.style.width = '0%';
                    uploadStatus.textContent = '';
                    uploadPopup.classList.add('d-none');
                    uploadForm.reset();
                    location.reload();
                } else {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.error) {
                            uploadStatus.textContent = response.error;
                        } else {
                            uploadStatus.textContent = 'Er is iets misgegaan tijdens het uploaden.';
                        }
                    } catch (e) {
                        uploadStatus.textContent = 'Er is een onbekende fout opgetreden.';
                    }
                }
                button.disabled = false;
                button.classList.remove('loading');
                button.querySelector('.button-text').style.display = 'inline-block';
                button.querySelector('.loading-spinner').style.display = 'none';
                button.querySelector('.loading-text').style.display = 'none';
            };

            xhr.onerror = function () {
                uploadStatus.textContent = 'Er is een netwerkfout opgetreden.';
                const button = document.getElementById('upload-button');
                button.disabled = false;
                button.classList.remove('loading');
                button.querySelector('.button-text').style.display = 'inline-block';
                button.querySelector('.loading-spinner').style.display = 'none';
                button.querySelector('.loading-text').style.display = 'none';
            };

            xhr.send(formData);
        });

        const rows = document.querySelectorAll('tr.file');
        rows.forEach(function (row) {
            const link = row.getAttribute('data-link');
            let target = row.getAttribute('data-target');
            target = target ? target.trim() : "_self";
            if (target === "null") {
                target = "_self";
            }
            row.addEventListener('click', function (event) {
                if (event.target.closest('.has-dropdown')) {
                    return;
                }
                // Add this new condition to check for the 'file-image' class
                if (event.target.closest('.file-image') || event.target.closest('.file-video')|| event.target.closest('.file-audio') || event.target.closest('.file-pdf')) {
                    return;
                }
                window.open(link, target);
            });
        });
    });
</script>
