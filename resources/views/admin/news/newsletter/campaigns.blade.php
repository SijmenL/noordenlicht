@extends('layouts.dashboard')

@php
    use Carbon\Carbon;
@endphp

@section('content')
    <div class="container col-md-11">
        <div class="d-flex flex-row justify-content-between align-items-center">
            <div class="d-flex flex-column">
                <h1>Nieuwsbrieven</h1>

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Nieuwsbrieven</li>
                    </ol>
                </nav>
            </div>
        </div>

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
                               placeholder="Zoeken op naam, beschrijving, etc."
                               aria-label="Zoeken" aria-describedby="basic-addon1" value="{{ $search }}"
                               onchange="this.form.submit();">

                        <a class="input-group-text" style="text-decoration: none; cursor: pointer" href="{{ route('admin.laposta.refresh') }}">
                            <span class="material-symbols-rounded">refresh</span></a>
                    </div>
                </div>
            </div>
        </form>

        @if($campaigns->count() > 0)
            <div class="overflow-scroll no-scrolbar" style="max-width: 100vw">
                <table class="table table-striped mb-0">
                    <thead class="thead-dark table-bordered table-hover">
                    <tr>
                        <th scope="col">Onderwerp</th>
                        <th scope="col">Verzonden op</th>
                        <th scope="col">Opties</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach ($campaigns as $campaign)
                        <tr>
                            <th>
                                <strong>{{ $campaign["campaign"]['name'] }}</strong><br>
                                <small class="text-muted">{{ $campaign["campaign"]['subject'] ?? '' }}</small>
                            </th>

                            <th>
                                @if(isset($campaign["campaign"]['delivery_requested']))
                                    {{ Carbon::parse($campaign["campaign"]['delivery_requested'])->format('d-m-Y H:i') }}
                                @else
                                    -
                                @endif
                            </th>

                            <th>
                                <div class="d-flex flex-row flex-wrap gap-2">
                                    {{-- Preview Button --}}
                                    <button type="button"
                                            class="btn btn-primary d-flex align-items-center"
                                            onclick="openPreview('{{ $campaign['campaign']['web'] }}')">
                                        <span class="material-symbols-rounded me-1">visibility</span>
                                        Bekijk
                                    </button>

                                </div>
                            </th>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $campaigns->links() }}
            </div>
        @else
            <div class="alert alert-warning" role="alert">
                Geen nieuwsbrieven gevonden. Controleer of je de juiste API key hebt en druk op "Ververs Alles".
            </div>
        @endif
    </div>



    {{-- Newsletter Preview Popup --}}
    <div id="newsletterPopup" class="popup" style="display: none; z-index: 99999; top: 0; left: 0; position: fixed; width: 100vw; height: 100vh; background: rgba(0,0,0,0.7);">
        <div class="popup-body" style="position: relative; width: 90%; height: 90%; margin: 2.5% auto; background: white; border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0px 10px 30px rgba(0,0,0,0.5);">

            <div class="d-flex justify-content-between align-items-center p-3 border-bottom w-100">
                <h5 class="mb-0 fw-bold">Nieuwsbrief Voorvertoning</h5>
                <button type="button" class="btn btn-outline-danger d-flex align-items-center" onclick="closePreview()">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>

            <div style="flex-grow: 1; width: 100%; overflow: hidden;">
                <iframe id="previewIframe" src="" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
        </div>
    </div>

    <script>
        function openPreview(url) {
            const popup = document.getElementById('newsletterPopup');
            const iframe = document.getElementById('previewIframe');

            // Set URL and display popup
            iframe.src = url;
            popup.style.display = 'block';

            // Prevent background scrolling
            document.body.style.overflow = 'hidden';
        }

        function closePreview() {
            const popup = document.getElementById('newsletterPopup');
            const iframe = document.getElementById('previewIframe');

            // Hide popup and reset iframe
            popup.style.display = 'none';
            iframe.src = '';

            // Restore scrolling
            document.body.style.overflow = 'auto';
        }

        // Close on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                closePreview();
            }
        });

        // Close when clicking outside the content area
        window.onclick = function(event) {
            const popup = document.getElementById('newsletterPopup');
            if (event.target == popup) {
                closePreview();
            }
        }
    </script>
@endsection
