@extends('layouts.dashboard')

@section('content')
    <div class="container col-md-11">
        <h1>Details @if($account !== null) {{$account->name}} {{$account->infix}} {{$account->last_name}}@endif</h1>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin') }}">Dashboard</a></li>
                <li class="breadcrumb-item" aria-current="page"><a
                        href="{{route('admin.signup')}}">Aanmeldingen</a></li>
                <li class="breadcrumb-item active" aria-current="page">Details @if($account !== null) {{$account->name}} {{$account->infix}} {{$account->last_name}}@endif</li>
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

        @if($account !== null)
            <x-user_details
                :hide="['dolphin_name']"
                :user="$account"
            />
        @else
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <span class="material-symbols-rounded me-2">person_off</span>Geen account gevonden...
            </div>
        @endif

        <div class="d-flex flex-row flex-wrap gap-2">
            <a href="{{ route('admin.signup') }}" class="btn btn-info">Terug</a>
        </div>
    </div>
@endsection
