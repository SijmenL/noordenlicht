@extends('layouts.app')

@section('content')
    <style>

    </style>

    <div class="container col-md-11 mt-5 mb-5">
        @php
            use Carbon\Carbon;

            $timezone = new DateTimeZone('Europe/Amsterdam');
            $date = new DateTime('now', $timezone);
            $formattedDate = $date->format('d-m-Y H:i:s');

            $hour = Carbon::now()->hour;

            if ($hour >= 6 && $hour < 12) {
                $greeting = 'Goeiemorgen';
            } elseif ($hour >= 12 && $hour < 18) {
                $greeting = 'Goeiemiddag';
            } elseif ($hour >= 18 && $hour < 24) {
                $greeting = 'Goedenavond';
            } else {
                $greeting = 'Goedenacht';
            }
        @endphp

        <div class="page-header">
            <h1 class="fw-bold">{{$greeting}}, {{ explode(' ', \App\Models\User::find(Auth::id())->name)[0] }}</h1>
            <p class="text-muted">Beheer hier je persoonlijke instellingen en voorkeuren.</p>
        </div>

        @if(Session::has('error'))
            <div class="alert alert-danger shadow-sm border-0 rounded-3 mb-4" role="alert">
                {{ session('error') }}
            </div>
        @endif
        @if(Session::has('success'))
            <div class="alert alert-success shadow-sm border-0 rounded-3 mb-4" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white border w-100 p-4 rounded mt-3">
            <div class="settings-grid">
                <a class="setting-item" href="{{ route('user.settings.account.edit') }}">
                    <div class="setting-content">
                        <h3>Persoonlijke informatie</h3>
                        <small>Pas je persoonlijke informatie aan zoals je adres en profielfoto</small>
                    </div>
                    <div class="icon-box">
                        <span class="material-symbols-rounded">person</span>
                    </div>
                </a>

                <a class="setting-item" href="{{ route('user.settings.change-password') }}">
                    <div class="setting-content">
                        <h3>Verander wachtwoord</h3>
                        <small>Beveilig je account en pas je wachtwoord aan</small>
                    </div>
                    <div class="icon-box">
                        <span class="material-symbols-rounded">lock</span>
                    </div>
                </a>

                <a class="setting-item" href="{{ route('user.orders') }}">
                    <div class="setting-content">
                        <h3>Bestelgeschiedenis</h3>
                        <small>Bekijk een overzicht van wat je besteld hebt</small>
                    </div>
                    <div class="icon-box">
                        <span class="material-symbols-rounded">history</span>
                    </div>
                </a>

                <a class="setting-item" href="{{ route('user.bookings') }}">
                    <div class="setting-content">
                        <h3>Jouw boekingen</h3>
                        <small>Bekijk al je gemaakte boekingen</small>
                    </div>
                    <div class="icon-box">
                        <span class="material-symbols-rounded">calendar_month</span>
                    </div>
                </a>

                <a class="setting-item" href="{{ route('user.settings.edit-notifications') }}">
                    <div class="setting-content">
                        <h3>Notificaties</h3>
                        <small>Beheer je e-mail en app notificatie voorkeuren</small>
                    </div>
                    <div class="icon-box">
                        <span class="material-symbols-rounded">notifications</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
@endsection
