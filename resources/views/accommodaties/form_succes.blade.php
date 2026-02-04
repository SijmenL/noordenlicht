@extends('layouts.app')

@section('content')
    <div class="rounded-bottom-5 bg-light container-block align-items-center justify-content-center d-flex"
         style="min-height: 80vh; position: relative; margin-top: 0 !important; background-image: url('{{ asset('img/logo/doodles/Blad StretchA_white.webp') }}'); background-repeat: repeat; background-size: cover;">

        <div class="container pt-4 pb-5">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">

                    <div class="border-0 rounded-5 p-5 text-center bg-white animate-up">

                        <div class="mb-4">
                            <span class="material-symbols-rounded icon-success" style="font-size: 6rem; color: #5a7123;">
                                check_circle
                            </span>
                        </div>

                        <h1 class="fw-bold text-primary mb-3">Aanvraag Verzonden!</h1>

                        <p class="lead text-muted mb-4">
                            Bedankt! We hebben je aanvraag voor een boeking in goede orde ontvangen.
                        </p>

                        <p class="text-muted mb-5">
                            We gaan je gegevens bekijken en nemen zo snel mogelijk contact met je op.
                            Je ontvangt tevens een bevestiging per e-mail.
                        </p>

                        <div class="d-grid gap-3 d-sm-flex justify-content-sm-center">
                            <a href="/" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">
                                Terug naar Home
                            </a>
                            <a href="{{ route('user.settings') }}" class="btn btn-outline-primary btn-lg rounded-pill px-5">
                                Naar mijn account
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
