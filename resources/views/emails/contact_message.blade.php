@extends('emails.layouts.mail')

@section('title')
    <h1 class="email-title">Het contactformulier is ingevuld!</h1>
@endsection

@section('info')
    <div>
        @php
            use Illuminate\Support\Str;

            $contact = \App\Models\Contact::findOrFail($data['relevant_id']);
        @endphp

        <p>Beste {{ $data['reciever_name'] }}, het contactformulier op de site is op {{ $contact->created_at->format('d-m-Y H:i') }} ingevuld.</p>

        <br>


        <div>
            <p><i>{{ $contact->name }}</i></p>
            <p><i>{{ $contact->email }}</i></p>

            @if(isset($contact->phone))
                <p><i>{{ $contact->phone }}</i></p>
            @endif
            <br>
            <div>{!! $contact->message !!}</div>
        </div>

        <a class="action-button" href="{{ route('admin.contact.details', $contact->id) }}">Klik hier om dit contact te
            bekijken!</a>
    </div>
@endsection

@section('main_footer')
    <td style="padding-top: 30px;">
        <p class="footer-bold">Waarom ontvang jij deze email?</p>
        <p class="footer-text">
            Deze email is automatisch gegenereerd omdat het contactformulier is ingevuld.
            Als je deze notificaties niet meer wilt ontvangen, wijzig dan je instellingen op de instellingen pagina.
        </p>
    </td>
@endsection

