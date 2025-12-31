@extends('emails.layouts.mail')

@section('title')
    <h1 class="email-title">Je inschrijving is rond!</h1>
@endsection

@section('info')
    @php
        use Illuminate\Support\Str;

        $user = \App\Models\User::findOrFail($data['relevant_id']);
    @endphp

    <div>
        <p>Beste {{ $data['reciever_name'] }}, je herinschrijving is geaccepteerd. Welkom terug!</p>
        <br>
        <p>Je hebt vanaf nu volledige toegang tot ons ledenportaal waar alle informatie te vinden is.</p>
        <a class="action-button"
           href="https://portal.waterscoutingmhg.nl/">Ga naar het ledenportaal</a>
        <br>
        <br>
        <p>Mocht je vragen hebben, dan kun je deze altijd stellen door dit mailtje te beantwoorden, of door een mailtje te sturen naar <a href="mailto:administratie@waterscoutingmhg.nl">administratie@waterscoutingmhg.nl</a></p>

    </div>
@endsection

@section('main_footer')
    <td style="padding-top: 30px;">
        <p class="footer-bold">Waarom ontvang jij deze email?</p>
        <p class="footer-text">
            Deze email is automatisch gegenereerd omdat je herinschrijving voor de MHG is goedgekeurd.
        </p>
    </td>
@endsection

