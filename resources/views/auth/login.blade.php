@extends('layouts.login')

@section('content')
    <div class="d-flex justify-content-center align-items-center" style="height: 100vh; width: 100vw; background-image: url('{{ asset('img/logo/doodles/Treewoman2a.webp') }}'); background-repeat: no-repeat; background-size: 115vw; background-position: center">
        <div class="login d-flex gap-4 shadow m-4 bg-white rounded-5 overflow-hidden">
            <div class="login-image d-flex justify-content-center align-items-center"
                 style="background-image: url({{ asset('img/photo/compressed/NoordenLicht2.webp') }});">
                <img style="height: 65%; filter: drop-shadow(0px 0px 25px #000000);" alt="logo" src="{{ asset('img/logo/logo_white.webp') }}">
            </div>

            <div class="d-flex flex-column p-3 login-text justify-content-center">
                <h1>Log In</h1>
                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                <p>Neem bij problemen <a href="mailto:info@noordenlicht.nu">per mail</a> contact op.</p>

                <form method="POST" action="{{ route('login') }}" class="p-3 border-2 border-info-subtle"
                      style="border: solid; border-radius: 15px;">
                    @csrf

                    <div class="row mb-3">
                        <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('E-mail') }}</label>

                        <div class="col-md-6">
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                   name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                            @error('email')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Wachtwoord') }}</label>

                        <div class="col-md-6">
                            <input id="password" type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   name="password" required autocomplete="current-password">

                            @error('password')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-0">
                        <div class="d-flex flex-row-responsive gap-2 justify-content-center">
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
                                <span class="button-text">Inloggen</span>
                                <span style="display: none" class="loading-spinner spinner-border spinner-border-sm"
                                      aria-hidden="true"></span>
                                <span style="display: none" class="loading-text" role="status">Laden...</span>
                            </button>

                            <a href="{{ route('register') }}" class="btn btn-secondary text-white">
                                Registreren
                            </a>
                            <a href="{{ route('home') }}" class="btn btn-outline-dark">
                                Annuleren
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
