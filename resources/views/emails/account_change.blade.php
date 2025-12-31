@extends('emails.layouts.mail')

@section('title')


    <h1 class="email-title">Je account is gewijzigd.</h1>
@endsection

@section('info')
    <div>
        <p>Beste {{ $data['reciever_name'] }}, je account is aangepast.</p>
        <br>
        <p>Geen paniek! Je kan deze wijzigingen altijd op de website bekijken.</p>
        <br>
        <p>Bij vragen of opmerkingen kun je altijd op dit mailtje antwoorden!</p>
        <a class="action-button" href="https://noordenlicht.nu/login">Klik hier om naar de inlogpagina te gaan!</a>
    </div>
@endsection

@section('main_footer')
    <td style="padding-top: 30px;">
        <p class="footer-bold">Waarom ontvang jij deze email?</p>
        <p class="footer-text">
            Deze email is automatisch gegenereerd omdat je NoordenLicht account is gewijzigd.
        </p>
    </td>
@endsection

