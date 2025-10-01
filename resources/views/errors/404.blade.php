@extends('layouts.app')

@section('content')

    <div class="container col-md-11">
        <h1>404 error</h1>
        <p>Het lijkt erop dat de pagina die je probeert te bezoeken niet meer bestaat of is verplaatst.</p>

        <a class="btn btn-primary text-white" href="{{ route('home') }}">Ga Terug Naar De Homepagina</a>
    </div>
@endsection
