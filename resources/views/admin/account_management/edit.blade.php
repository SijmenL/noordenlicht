@extends('layouts.dashboard')

@vite('resources/js/search-user.js')

@section('content')
    <div class="container col-md-11">
        <h1>Bewerk
                {{$account->name}}
            </h1>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">

                <li class="breadcrumb-item" aria-current="page"><a href="{{route('admin')}}">Dashboard</a></li>
                <li class="breadcrumb-item" aria-current="page"><a
                        href="{{route('admin.account-management')}}">Gebruikers</a></li>
                <li class="breadcrumb-item active" aria-current="page">Bewerk
                        {{$account->name}}
                </li>
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
            <form method="POST" action="{{ route('admin.account-management.store', $account) }}"
                  enctype="multipart/form-data">
                @csrf
                <div class="no-scrolbar overflow-hidden" style="max-width: 100%">
                    <table class="table table-striped" style="max-width: 100%">
                        <tbody>
                        <tr>
                            <th><label for="name" class="col-md-4 col-form-label ">Volledige naam</label></th>
                            <th>
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                       name="name" value="{{ $account->name }}" autocomplete="name" autofocus>
                                @error('name')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </th>
                        </tr>

                        <tr>
                            <th><label for="email" class="col-md-4 col-form-label ">E-mail</label></th>
                            <th><input id="email" value="{{ $account->email }}" type="email"
                                       class="form-control @error('email') is-invalid @enderror" name="email"
                                       autocomplete="email">
                                @error('email')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror</th>
                        </tr>
                        <tr>
                            <th><label for="profile_picture" class="col-md-4 col-form-label ">Profielfoto</label></th>
                            <th>
                                @if($account->profile_picture)
                                    <img alt="profielfoto" class="w-25"
                                         src="{{ asset('/profile_pictures/' .$account->profile_picture) }}">
                                @endif
                                <input class="form-control mt-2" value="{{ $account->profile_picture }}"
                                       id="profile_picture"
                                       type="file" name="profile_picture"
                                       accept="image/*">
                                @error('profile_picture')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                            <th>
                        </tr>
                        <tr>
                            <th><label for="roles" class="col-md-4 col-form-label ">Rollen</label></th>
                            <th>
                                <div class="custom-select">
                                    <select id="select-roles" class="d-none" id="roles" name="roles[]" multiple>
                                        @foreach($all_roles as $role)
                                            <option data-description="{{ $role->description }}"
                                                    value="{{ $role->id }}" {{ in_array($role->id, $selectedRoles) ? 'selected' : '' }}>
                                                {{ $role->role }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="d-flex flex-wrap gap-1" id="button-container">
                                </div>
                            </th>
                        </tr>

                        <tr>
                            <th><label for="sex" class="col-md-4 col-form-label ">Geslacht</label></th>
                            <th>
                                <select id="sex" type="text" class="form-select @error('sex') is-invalid @enderror"
                                        name="sex">
                                    <option @if($account->sex === 'Man') selected @endif >Man</option>
                                    <option @if($account->sex === 'Vrouw') selected @endif >Vrouw</option>
                                    <option @if($account->sex === 'Anders') selected @endif >Anders</option>
                                </select>
                                @error('sex')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </th>
                        </tr>
                        <tr>
                            <th><label for="birth_date" class="col-md-4 col-form-label ">Geboortedatum</label></th>
                            <th><input id="birth_date" value="{{ $account->birth_date }}" type="date"
                                       class="form-control @error('birth_date') is-invalid @enderror" name="birth_date">
                                @error('birth_date')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror</th>
                        </tr>
                        <tr>
                            <th><label for="street" class="col-md-4 col-form-label ">Straat & huisnummer</label></th>
                            <th><input id="street" value="{{ $account->street }}" type="text"
                                       class="form-control @error('street') is-invalid @enderror" name="street">
                                @error('street')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror</th>
                        </tr>
                        <tr>
                            <th><label for="postal_code" class="col-md-4 col-form-label ">Postcode</label></th>
                            <th><input id="postal_code" value="{{ $account->postal_code }}" type="text"
                                       class="form-control @error('postal_code') is-invalid @enderror"
                                       name="postal_code">
                                @error('postal_code')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror</th>
                        </tr>
                        <tr>
                            <th><label for="city" class="col-md-4 col-form-label ">Woonplaats</label></th>
                            <th><input id="city" value="{{ $account->city }}" type="text"
                                       class="form-control @error('city') is-invalid @enderror" name="city">
                                @error('city')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror</th>
                        </tr>
                        <tr>
                            <th><label for="phone" class="col-md-4 col-form-label ">Telefoonnummer</label></th>
                            <th><input id="phone" value="{{ $account->phone }}" type="text"
                                       class="form-control @error('phone') is-invalid @enderror" name="phone">
                                @error('phone')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror</th>
                        </tr>

                        <tr>
                            <th>Aangepast op</th>
                            <th>{{ \Carbon\Carbon::parse($account->updated_at)->format('d-m-Y H:i:s') }}</th>
                        </tr>
                        <tr>
                            <th>Aangemaakt op</th>
                            <th>{{ \Carbon\Carbon::parse($account->created_at)->format('d-m-Y H:i:s') }}</th>
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
                    <a href="{{ route('admin.account-management.details', ['id' => $account->id]) }}"
                       class="btn btn-danger text-white">Annuleren</a>
                    <a href="{{ route('admin.account-management.password', ['id' => $account->id]) }}"
                       class="btn btn-dark text-white">Wijzig wachtwoord</a>
                    <a class="delete-button btn btn-outline-danger"
                       data-id="{{ $account->id }}"
                       data-name="{{ $account->name . ' ' . $account->infix . ' ' . $account->last_name }}"
                       data-link="{{ route('admin.account-management.delete', $account->id) }}">Verwijderen</a>
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
