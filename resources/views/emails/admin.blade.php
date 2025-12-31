@extends('emails.layouts.mail')

@section('title')
    <h1 class="email-title">Systeemnotificatie</h1>
@endsection

@section('info')
    <div>

        <div class="post">
            <div> {{ $data['message'] }}</div>
        </div>

    </div>
@endsection

@section('main_footer')
    <td style="padding-top: 30px;">
        <p class="footer-bold">Waarom ontvang jij deze email?</p>
        <p class="footer-text">
            Deze mail is door het systeem of een van de beheerders verstuurd. Neem contact op voor meer informatie.
        </p>
    </td>
@endsection

