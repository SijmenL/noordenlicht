@extends('layouts.dashboard')

@vite('resources/js/search-user.js')

@php
    use Carbon\Carbon;
    Carbon::setLocale('nl');
@endphp

@section('content')
    <div class="container col-md-11">
        <h1>Handmatige Reservatie</h1>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Nieuwe Reservatie</li>
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

        <div class="rounded-2 bg-light p-3">
            <div class="container">
                <form id="admin-booking-form" action="{{ route('admin.reserve.store') }}"
                      method="POST">
                    @csrf
                    <div class="mb-4">
                        <h4 class="fw-bold mb-3 text-primary">Selecteer Accommodatie & Gebruiker</h4>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="accommodatie_id" class="form-label fw-bold">Accommodatie</label>
                                <select class="form-select" id="accommodatie_id" name="accommodatie_id" required onchange="resetAvailability()">
                                    <option value="" disabled selected>Kies een accommodatie...</option>
                                    @foreach($accommodaties as $acco)
                                        <option value="{{ $acco->id }}"
                                                data-min="{{ $acco->min_check_in }}"
                                                data-max="{{ $acco->max_check_in }}">
                                            {{ $acco->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="user" class="form-label fw-bold">Gebruiker (Zoek op ID of Naam)</label>
                                {{-- Reuse existing user search logic --}}
                                <div class="input-group">
                                    <span class="input-group-text"><i class="material-symbols-rounded">person</i></span>
                                    {{-- The class 'user-select-window' triggers the existing JS --}}
                                    <input id="user" name="user" type="text" class="user-select-window form-control"
                                           placeholder="Zoeken op gebruikers id" required>

                                    {{-- Popup structure required by search-user.js --}}
                                    <div class="user-select-window-popup d-none" style="transform: translateY(40px) translateX(13px); z-index: 1050;">
                                        <h3>Selecteer gebruikers</h3>
                                        <div class="input-group">
                                            <label class="input-group-text">
                                                <span class="material-symbols-rounded">search</span>
                                            </label>
                                            <input type="text" data-type="single" class="user-select-search form-control"
                                                   placeholder="Zoeken op naam, email, adres etc.">
                                        </div>
                                        <div class="user-list no-scrolbar">
                                            <div class="w-100 h-100 d-flex justify-content-center align-items-center">
                                                <span class="material-symbols-rounded rotating" style="font-size: xxx-large">progress_activity</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h4 class="fw-bold mb-3 text-primary">Datum & Tijd</h4>

                        {{-- Admin Override Warnings --}}
                        <div id="warning-container" class="alert alert-warning d-none mb-3">
                            <div class="d-flex align-items-center">
                                <span class="material-symbols-rounded me-2">warning</span>
                                <div>
                                    <strong>Let op!</strong>
                                    <ul class="mb-0 ps-3 small" id="warning-list"></ul>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="start_date">Start</label>
                                <div class="input-group">
                                    <input type="datetime-local" class="form-control" id="start_date" name="start_date" required onchange="validateAdminTime()">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="end_date">Eind</label>
                                <div class="input-group">
                                    <input type="datetime-local" class="form-control" id="end_date" name="end_date" required onchange="validateAdminTime()">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h4 class="fw-bold mb-3 text-primary">Notities</h4>
                        <label for="comment" class="form-label">Interne opmerking</label>
                        <textarea class="form-control" id="comment" name="comment" rows="3" placeholder="Reden voor handmatige boeking..."></textarea>
                    </div>

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
                        class="btn btn-primary text-white flex flex-row align-items-center justify-content-center">
                        <span class="button-text">Reservatie Bevestigen</span>
                        <span style="display: none" class="loading-spinner spinner-border spinner-border-sm"
                              aria-hidden="true"></span>
                        <span style="display: none" class="loading-text" role="status">Laden...</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let bookings = [];
        let currentAccommodatieId = null;

        function resetAvailability() {
            bookings = [];
            currentAccommodatieId = document.getElementById('accommodatie_id').value;
            // Optionally fetch availability if you want to be fancy, but simpler to just fetch on validate
            // For now, let's just trigger validation if dates are set
            validateAdminTime();
        }

        async function validateAdminTime() {
            const accoSelect = document.getElementById('accommodatie_id');
            const sDate = document.getElementById('start_date').value;
            const sTime = document.getElementById('start_time').value;
            const eDate = document.getElementById('end_date').value;
            const eTime = document.getElementById('end_time').value;

            if (!accoSelect.value || !sDate || !sTime || !eDate || !eTime) return;

            const warningContainer = document.getElementById('warning-container');
            const warningList = document.getElementById('warning-list');
            warningList.innerHTML = '';
            let warnings = [];

            // 1. Check Standard Hours
            const minTime = accoSelect.options[accoSelect.selectedIndex].dataset.min || '00:00';
            const maxTime = accoSelect.options[accoSelect.selectedIndex].dataset.max || '23:59';

            if (sTime < minTime || eTime > maxTime) {
                // Only warn if dates are same, otherwise simple logic fails for overnight
                if (sDate === eDate) {
                    warnings.push(`Tijd valt buiten standaard openingstijden (${minTime} - ${maxTime}).`);
                }
            }

            // 2. Fetch Availability to check overlap
            // We fetch the month of the start date
            const startD = new Date(sDate);
            const month = startD.getMonth() + 1;
            const year = startD.getFullYear();

            // Fetch bookings for that accommodation/month if we haven't or if month changed
            // Ideally we cache this, but for admin simple logic: fetch now
            try {
                const res = await fetch(`{{ route('accommodatie.availability', ':id') }}`.replace(':id', accoSelect.value) + `?month=${month}&year=${year}`);
                const data = await res.json();
                const events = data.events || [];

                // Check overlap
                // Convert inputs to comparable dates
                const checkStart = new Date(`${sDate}T${sTime}`);
                const checkEnd = new Date(`${eDate}T${eTime}`);

                let hasOverlap = false;
                events.forEach(ev => {
                    // event dates are ISO strings
                    const evStart = new Date(ev.start);
                    const evEnd = new Date(ev.end);

                    if (checkStart < evEnd && checkEnd > evStart) {
                        hasOverlap = true;
                    }
                });

                if (hasOverlap) {
                    warnings.push("Er is een overlap met een bestaande boeking!");
                }

            } catch (e) {
                console.error("Availability check failed", e);
            }

            // Update UI
            if (warnings.length > 0) {
                warningContainer.classList.remove('d-none');
                warnings.forEach(w => {
                    const li = document.createElement('li');
                    li.innerText = w;
                    warningList.appendChild(li);
                });
            } else {
                warningContainer.classList.add('d-none');
            }
        }
    </script>
@endsection
