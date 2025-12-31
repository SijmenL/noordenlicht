@extends('emails.layouts.mail')

@section('title')
    <h1 class="email-title">Welkom bij je NoordenLicht account!</h1>
@endsection

@section('info')
    @php
        use Illuminate\Support\Str;

        $user = \App\Models\User::findOrFail($data['relevant_id']);
    @endphp

    <div>
        <p>Beste {{ $data['reciever_name'] }},</p>
        <br>
        <p>Fijn dat je er bent! Je account bij NoordenLicht is aangemaakt.</p>
        <br>
        <p>In dit account vind je een overzicht van je bestellingen en je eventuele boekingen terug.</p>
        <br>
        <p>Heb je hier vragen over? Laat het ons weten door op deze mail te reageren.</p>
        <br>
        <p>Liefs,<br>Team NoordenLicht</p>

        <a class="action-button"
           href="{{ route('login') }}">Log in een bekijk je account</a>
    </div>
@endsection

@section('main_footer')
    <td style="padding-top: 30px;">
        <p class="footer-bold">Waarom ontvang jij deze email?</p>
        <p class="footer-text">
            Deze email is automatisch gegenereerd omdat je je hebt ingeschreven voor een NoordenLicht account.
            Als jij dit niet was kun je contact met ons opnemen door te reageren op deze mail.
        </p>
    </td>
@endsection

