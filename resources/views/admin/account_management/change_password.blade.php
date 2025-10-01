@extends('layouts.dashboard')

@section('content')
    <div class="container col-md-11">
        <h1>Verander wachtwoord</h1>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">

                <li class="breadcrumb-item" aria-current="page"><a href="{{route('admin')}}">Dashboard</a></li>
                <li class="breadcrumb-item" aria-current="page"><a
                        href="{{route('admin.account-management')}}">Gebruikers</a></li>
                <li class="breadcrumb-item" aria-current="page"><a
                        href="{{route('admin.account-management.edit', $account)}}">Bewerk @if($account !== null) {{$account->name}} {{$account->infix}} {{$account->last_name}}@endif</a></li>
                <li class="breadcrumb-item active" aria-current="page">Verander wachtwoord</li>
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

        @if($account !== null)
            <form method="POST" action="{{ route('admin.account-management.password.store', $account) }}"
                  enctype="multipart/form-data">
                @csrf
                <div class="overflow-scroll no-scrolbar" style="max-width: 100vw">
                    <table class="table table-striped">
                        <tbody>
                        <tr>
                            <th><label for="new_password" class="col-md-4 col-form-label ">Nieuw wachtwoord</label></th>
                            <th>
                                <input id="new_password" type="password" class="form-control @error('new_password') is-invalid @enderror"
                                       name="new_password" autofocus>
                                <small>Minstens 8 karakters</small>
                                @error('new_password')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </th>
                        </tr>
                        <tr>
                            <th><label for="new_password_confirmation" class="col-md-4 col-form-label ">Herhaal wachtwoord</label></th>
                            <th>
                                <input id="new_password_confirmation" type="password" class="form-control @error('new_password_confirmation') is-invalid @enderror"
                                       name="new_password_confirmation" autofocus>
                                @error('new_password_confirmation')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </th>
                        </tr>
                        </tbody>
                    </table>

                </div>

                @if ($errors->any())
                    <div class="text-danger">
                        <p>Er is iets misgegaan...</p>
                    </div>
                @endif

                <div class="d-flex flex-row flex-wrap gap-2">
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
                        <span class="button-text">Opslaan</span>
                        <span style="display: none" class="loading-spinner spinner-border spinner-border-sm" aria-hidden="true"></span>
                        <span style="display: none" class="loading-text" role="status">Laden...</span>
                    </button>


                    <a href="{{ route('admin.account-management.edit', ['id' => $account->id]) }}"
                       class="btn btn-danger text-white">Annuleren</a>
                </div>

                <p class="mt-2">Wanneer je op 'Opslaan' drukt, krijgt de gebruiker hier een notificatie van.</p>

                @else
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <span class="material-symbols-rounded me-2">person_off</span>Geen account gevonden...
                    </div>

                    <div class="d-flex flex-row flex-wrap gap-2">
                        <a href="{{ route('admin.account-management')}}" class="btn btn-info">Terug</a>
                    </div>
                @endif



            </form>
    </div>
@endsection
