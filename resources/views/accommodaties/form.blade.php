@extends('layouts.app')

@section('content')
    <div class="rounded-bottom-5 bg-light container-block pb-5"
         style="position: relative; margin-top: 0 !important; z-index: 10; background-image: url('{{ asset('img/logo/doodles/Blad StretchA_white.webp') }}'); background-repeat: repeat; background-size: cover;">

        <div class="container pt-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
                <div>
                    <h1 class="fw-bold">Aanvraagformulier boekingen</h1>
                    <p>Meldt je bij de eerste keer aan middels het aanvraag formulier. Je ontvangt een reactie of je
                        praktijk resoneert bij NoordenLicht. Na goedkeuring krijg je de mogelijkheid om accommodates bij
                        ons te boeken door in te loggen met het e-mail en wachtwoord wat je hier invult.</p>
                </div>
            </div>

            @if(Session::has('error'))
                <div class="alert alert-danger rounded-4 shadow-sm" role="alert">
                    {{ session('error') }}
                </div>
            @endif
            @if(Session::has('success'))
                <div class="alert alert-success rounded-4 shadow-sm" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}"
                  enctype="multipart/form-data">
                @csrf
                <div class="container">
                    <div class="mt-4">
                        <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">person</span>Account
                            Gegevens</h2>
                        <div class="d-flex flex-row-responsive">
                            <div class="w-100">
                                <div class="row">
                                    <div class="col">
                                        <label for="name" class="col-md-4 col-form-label ">Volledige naam <span
                                                class="required-form">*</span></label>

                                        <input id="name" type="text"
                                               class="form-control @error('name') is-invalid @enderror"
                                               name="name" value="{{ old('name') }}" autocomplete="name" autofocus>
                                        @error('name')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror

                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <label for="sex" class="col-md-4 col-form-label ">Geslacht</label>

                                        <select id="sex" type="text"
                                                class="form-select @error('sex') is-invalid @enderror"
                                                name="sex">
                                            <option @if(old('sex') === null) selected @endif >Niet gespecifieerd
                                            </option>
                                            <option @if(old('sex')) selected @endif >Man</option>
                                            <option @if(old('sex')) selected @endif >Vrouw</option>
                                            <option @if(old('sex')) selected @endif >Anders</option>
                                        </select>
                                        @error('sex')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror

                                    </div>
                                    <div class="col">
                                        <label for="birth_date" class="col-md-4 col-form-label ">Geboortedatum</label>
                                        <input id="birth_date" value="{{ old('birth_date') }}" type="date"
                                               class="form-control @error('birth_date') is-invalid @enderror"
                                               name="birth_date">
                                        @error('birth_date')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">key</span>Wachtwoord</h2>
                                <div class="mt-2 alert alert-info">Het wachtwoord moet minstens 8 tekens bevatten.</div>
                                <div class="">
                                    <label for="password" class="col-md-4 col-form-label ">Wachtwoord <span
                                            class="required-form">*</span></label>
                                    <input name="password" type="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           id="password">
                                    @error('password')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="">
                                    <label for="password_confirmation" class="col-md-4 col-form-label ">Herhaal
                                        wachtwoord <span
                                            class="required-form">*</span></label>
                                    <input name="password_confirmation" type="password" class="form-control"
                                           id="password_confirmation">
                                </div>
                            </div>
                    </div>
                </div>

                <div class="mt-4">
                    <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">call</span>Contact Gegevens
                    </h2>
                    <div class="col">
                        <label for="email" class="col-md-4 col-form-label ">E-mail <span
                                class="required-form">*</span></label>
                        <input id="email" value="{{ old('email') }}" type="email"
                               class="form-control @error('email') is-invalid @enderror" name="email"
                               autocomplete="email">
                        @error('email')
                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                        @enderror
                    </div>
                    <div>
                        <label for="phone" class="col-md-4 col-form-label ">Telefoonnummer <span
                                class="required-form">*</span></label>
                        <input id="phone" value="{{ old('phone') }}" type="text"
                               class="form-control @error('phone') is-invalid @enderror" name="phone">
                        @error('phone')
                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                        @enderror
                    </div>
                    <div class="flex-row-responsive w-100 gap-3 d-flex">
                        <div class="w-100">
                            <label for="street" class="col-form-label ">Straat & huisnummer <span
                                    class="required-form">*</span></label>
                            <input id="street" value="{{ old('street') }}" type="text"
                                   class="form-control @error('street') is-invalid @enderror" name="street">
                            @error('street')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>
                        <div class="w-100">
                            <label for="postal_code" class="col-md-4 col-form-label ">Postcode <span
                                    class="required-form">*</span></label>
                            <input id="postal_code" value="{{ old('postal_code') }}" type="text"
                                   class="form-control @error('postal_code') is-invalid @enderror" name="postal_code">
                            @error('postal_code')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>
                        <div class="w-100">
                            <label for="city" class="col-md-4 col-form-label ">Woonplaats <span
                                    class="required-form">*</span></label>
                            <input id="city" value="{{ old('city') }}" type="text"
                                   class="form-control @error('city') is-invalid @enderror" name="city">
                            @error('city')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>
                    </div>

                </div>
                <div class="mt-4">
                    <h2 class="flex-row gap-3"><span class="material-symbols-rounded me-2">work</span>Praktijkgegevens
                    </h2>
                    <div>
                        <label for="website" class="col-md-4 col-form-label ">Website <span
                                class="required-form">*</span></label>
                        <input id="website" value="{{ old('website') }}" type="text"
                               class="form-control @error('website') is-invalid @enderror" name="website">
                        @error('website')
                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                        @enderror
                    </div>
                    <div>
                        <label for="praktijknaam" class="col-md-4 col-form-label ">Parktijknaam <span
                                class="required-form">*</span></label>
                        <input id="praktijknaam" value="{{ old('praktijknaam') }}" type="text"
                               class="form-control @error('praktijknaam') is-invalid @enderror" name="praktijknaam">
                        @error('praktijknaam')
                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                        @enderror
                    </div>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger rounded-4 shadow-sm p-4">
                        <h4 class="h5 fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Er is iets misgegaan...</h4>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="d-flex flex-row flex-wrap gap-2 mt-4">
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
                        <span style="display: none" class="loading-spinner spinner-border spinner-border-sm"
                              aria-hidden="true"></span>
                        <span style="display: none" class="loading-text" role="status">Laden...</span>
                    </button>
                    <a href="{{ route('accommodaties') }}"
                       class="btn btn-secondary">Annuleren</a>
                </div>

            </form>

        </div>
    </div>
@endsection
