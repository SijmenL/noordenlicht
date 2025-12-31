@extends('layouts.dashboard')
@vite(['resources/js/search-user.js'])

@section('content')
    <div class="container col-md-11">
        <h1>Stuur notificaties</h1>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Stuur notificaties</li>
            </ol>
        </nav>

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

        <p>Dit is het notificatie centrum, waar je gebruikers notificaties kunt sturen. De notificaties worden verstuurd namens het 'systeem', ook per mail.</p>

        <div style="border-radius: 15px" class="bg-info p-3">
            <form method="POST" action="{{ route('admin.notifications.send') }}"
                  enctype="multipart/form-data">
                @csrf
                <label for="users">De gebruiker die de melding ontvangt</label>
                <div class="w-100">
                    <input id="users" name="users" type="hidden" value="{{ old('users') }}"
                           class="user-select-window user-select-none form-control"
                           placeholder="Kies een gebruiker uit de lijst"
                           aria-label="user" aria-describedby="basic-addon1">

                    <div class="user-select-window-popup no-shadow d-none mt-2"
                         style="position: unset; display: block !important;">
                        <div class="input-group">
                            <label class="input-group-text" id="basic-addon1">
                                <span class="material-symbols-rounded">search</span></label>
                            <input type="text" data-type="single" data-stayopen="true"
                                   class="user-select-search form-control" id="user-search"
                                   placeholder="Zoeken op naam, email, adres etc."
                                   aria-label="Zoeken" aria-describedby="basic-addon1">
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

                @error('users')
                <div class="text-danger">{{ $message }}</div>
                @enderror

                <div class="form-group mt-3">
                    <label for="display_text">De tekst die in de melding komt te staan</label>
                    <input class="form-control" id="display_text" type="text" name="display_text"
                           value="{{ old('display_title') }}">
                    @error('display_title')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                @if ($errors->any())
                    <div class="text-danger">
                        <p>Er is iets misgegaan...</p>
                    </div>
                @endif

                <div class="d-flex flex-row flex-wrap gap-2 mt-3">
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
                        class="btn btn-success flex flex-row align-items-center justify-content-center">
                        <span class="button-text">Verzenden</span>
                        <span style="display: none" class="loading-spinner spinner-border spinner-border-sm" aria-hidden="true"></span>
                        <span style="display: none" class="loading-text" role="status">Laden...</span>
                    </button>
                    <a href="{{ route('admin') }}"
                       class="btn btn-danger text-white">Annuleren</a>
                </div>

            </form>
        </div>

    </div>
@endsection
