@extends('layouts.dashboard')

@section('content')
    <div class="container col-md-11">
        <h1>Contact Details</h1>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item" aria-current="page"><a href="{{route('admin')}}">Administratie</a></li>
                <li class="breadcrumb-item" aria-current="page"><a
                        href="{{route('admin.contact')}}">Contact</a></li>
                <li class="breadcrumb-item active" aria-current="page">Contact Details</li>
            </ol>
        </nav>

        @if(Session::has('error'))
            <div class="alert alert-danger" role="alert">
                {{ session('error') }}
            </div>
        @endif
        @if(Session::has('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if($contact !== null)
            <div class="bg-light p-3 rounded m-2">
                <p>{!! nl2br(e($contact->message)) !!}</p>

                <div class="overflow-scroll no-scrolbar" style="max-width: 100vw">
                    <table class="table table-striped">
                        <tbody>
                        <tr>
                            <th>Naam</th>
                            <th>{{ $contact->name }}</th>
                        </tr>
                        <tr>
                            <th>Telefoonnummer</th>
                            <th><a href="tel:{{ $contact->phone }}">{{ $contact->phone }}</a></th>
                        </tr>
                        <tr>
                            <th>E-mail</th>
                            <th><a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a></th>
                        </tr>
                        <tr>
                            <th>Ingestuurd</th>
                            <th>{{ \Carbon\Carbon::parse($contact->created_at)->format('d-m-Y H:i:s') }}</th>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <span class="material-symbols-rounded me-2">phone_disabled</span>Geen contact gevonden...
            </div>
        @endif

        <div class="d-flex flex-row flex-wrap gap-2">
            <a href="{{ route('admin.contact') }}" class="btn btn-info">Terug</a>
            <a href="{{ route('admin.contact.seen', $contact->id) }}" class="btn btn-outline-dark">Markeer als @if($contact->done) niet @endif afgehandeld</a>
            <a class="delete-button btn btn-outline-danger"
               data-id="{{ $contact->id }}"
               data-name="dit ingestuurde contactformulier"
               data-link="{{ route('admin.contact.delete', $contact->id) }}">Verwijderen</a>
        </div>
    </div>
@endsection
