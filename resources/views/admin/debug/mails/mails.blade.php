@extends('layouts.dashboard')

@section('content')
    <div class="container col-md-11">
        <h1>Mail debug</h1>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Mail debug</li>
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

        <div class="d-flex flex-column gap-2">
            <a class="btn btn-info" href="{{ route('admin.debug.mail.view', 'account_change') }}">account_change</a>
            <a class="btn btn-info" href="{{ route('admin.debug.mail.view', 'password_change') }}">password_change</a>
            <br>
            <a class="btn btn-info" href="{{ route('admin.debug.mail.view', 'new_activity_registration') }}">new_activity_registration</a>
            <br>
            <a class="btn btn-info" href="{{ route('admin.debug.mail.view', 'contact_message') }}">contact_message</a>
            <a class="btn btn-info" href="{{ route('admin.debug.mail.view', 'new_registration') }}">new_registration</a>
            <br>
            <a class="btn btn-info" href="{{ route('admin.debug.mail.view', 'new_account') }}">new_account</a>
            <br>
            <a class="btn btn-info" href="{{ route('admin.debug.mail.view', 'new_order') }}">new_order</a>
            <a class="btn btn-info" href="{{ route('admin.debug.mail.view', 'ticket') }}">ticket</a>

        </div>
    </div>
@endsection
