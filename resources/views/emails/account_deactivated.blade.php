@extends('emails.layouts.mail')

@section('title')
    <h1 class="email-title">Je bent uitgeschreven.</h1>
@endsection

@section('info')
    @php
        use Illuminate\Support\Str;

        $user = \App\Models\User::findOrFail($data['relevant_id']);
    @endphp

    <div>
        <p>Beste {{ $data['reciever_name'] }},</p>

        <p>Jammer dat je ons verlaat. Bij deze bevestigen we dat je uitschrijving bij de Matthijs Heldt Groep definitief
            is.</p>
        <br>
        <p>Je hebt vanaf nu enkel nog toegang tot het ledenportaal om je persoonlijke gegevens in te zien of te
            wijzigen. Na 5 jaar verwijderen we je gegevens uit onze administratie.</p>
        <br>
        <p>Mocht je in de toekomst van gedachten veranderen, stuur ons dan een mailtje. Dan kunnen we je zo weer
            herinschrijven.</p>
        <br>
        <p>We hopen dat je een goede tijd bij ons hebt gehad!</p>
        <br>
        <p>Groetjes van Team Administatie & het hele leidingteam.</p>
    </div>
@endsection

@section('main_footer')
    <td style="padding-top: 30px;">
        <p class="footer-bold">Waarom ontvang jij deze email?</p>
        <p class="footer-text">
            Deze email is automatisch gegenereerd omdat je officieel uitgeschreven bent.
        </p>
    </td>
@endsection
