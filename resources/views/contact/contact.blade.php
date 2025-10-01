@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-center align-items-center container">
        <div class="contact d-flex gap-4 shadow m-4 rounded-5 overflow-hidden">
            <div class="contact-image" style="background-image: url({{ asset('img/photo/compressed/NoordenLicht.webp') }})">
            </div>
            <div class="d-flex flex-column p-3 contact-text justify-content-center">
                <h1>Contact</h1>
                <p>Neem contact op voor vragen of opmerkingen!</p>

                <form method="POST" action="{{ route('contact.submit') }}" class="p-3 border-2 border-info-subtle"
                      style="border: solid; border-radius: 15px;">
                    @csrf

                    <div class="row mb-3">
                        <label for="name" class="col-md-4 col-form-label text-md-end">Naam <span class="text-danger">*</span></label>

                        <div class="col-md-7">
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                   name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

                            @error('name')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="email" class="col-md-4 col-form-label text-md-end">E-mail <span class="text-danger">*</span></label>

                        <div class="col-md-7">
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
                        <label for="phone" class="col-md-4 col-form-label text-md-end">Telefoonnummer</label>

                        <div class="col-md-7">
                            <input id="phone" type="text" class="form-control @error('phone') is-invalid @enderror"
                                   name="phone" value="{{ old('phone') }}" autocomplete="phone" autofocus>

                            @error('phone')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="message" class="col-md-4 col-form-label text-md-end">Bericht <span class="text-danger">*</span></label>

                        <div class="col-md-7">
                      <textarea id="message" name="message" autofocus required rows="2" class="form-control" style="max-height: 250px">{{ old('message') }}</textarea>

                            @error('message')
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
                                <span class="button-text">Versturen!</span>
                                <span style="display: none" class="loading-spinner spinner-border spinner-border-sm" aria-hidden="true"></span>
                                <span style="display: none" class="loading-text" role="status">Laden...</span>
                            </button>

                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
