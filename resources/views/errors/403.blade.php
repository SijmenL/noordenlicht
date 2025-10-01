@extends('layouts.app')

@section('content')

    <div class="container col-md-11">
        <h1>403 error</h1>
        <p>Je hebt niet de rechten om dit te bekijken</p>

        <a class="btn btn-primary text-white" href="{{ route('home') }}">Ga Terug Naar De Homepagina</a>



    </div>
@endsection
