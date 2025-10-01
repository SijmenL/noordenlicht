@extends('layouts.app')

@section('content')

    <div class="container col-md-11">
        <h1>401 error</h1>
        <p>Je mag dit niet bekijken, of je bent niet ingelogd!</p>

        <a class="btn btn-primary text-white" href="{{ route('home') }}">Ga Terug Naar De Homepagina</a>

    </div>
@endsection
