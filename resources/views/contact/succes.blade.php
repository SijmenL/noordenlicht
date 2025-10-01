@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-center align-items-center container">
        <div class="contact d-flex gap-4 shadow m-4 rounded-5 overflow-hidden">
            <div class="contact-image" style="background-image: url({{ asset('img/photo/compressed/NoordenLicht.webp') }})">
            </div>
            <div class="d-flex flex-column p-3 contact-text justify-content-center">
                <h1>Contact</h1>
                <p>Neem contact op voor vragen of opmerkingen!</p>
                <div class="p-3 border-2 border-info-subtle" style="border: solid; border-radius: 15px;">
                        <p>Bedankt voor het insturen! We komen zo snel mogelijk bij je terug!</p>
                    </div>
            </div>
        </div>
    </div>
@endsection
