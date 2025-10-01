@extends('layouts.dashboard')

@section('content')
    <div class="container col-md-11">
        <h1>Bewerk @if($role !== null) {{$role->role}}@endif</h1>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">

                <li class="breadcrumb-item" aria-current="page"><a href="{{route('admin')}}">Dashboard</a></li>
                <li class="breadcrumb-item" aria-current="page"><a
                        href="{{route('admin.role-management')}}">Rollen</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    Bewerk @if($role !== null) {{$role->role}}@endif</li>
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

        @if($role !== null)
        <form method="POST" action="{{ route('admin.role-management.store', $role) }}"
              enctype="multipart/form-data">
            @csrf
            <div class="overflow-scroll no-scrolbar" style="max-width: 100vw">
                <table class="table table-striped">
                    <tbody>
                    <tr>
                        <th><label for="role" class="col-md-4 col-form-label">Naam</label></th>
                        <th>
                            <input id="role" type="text" class="form-control @error('role') is-invalid @enderror"
                                   name="role" value="{{ $role->role }}" autocomplete="role" autofocus>
                            @error('role')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </th>
                    </tr>
                    <tr>
                        <th><label for="description" class="col-md-4 col-form-label">Beschrijving</label></th>
                        <th>
                            <input id="description" type="text" class="form-control @error('description') is-invalid @enderror"
                                   name="description" value="{{ $role->description }}" autocomplete="description" autofocus>
                            @error('description')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </th>
                    </tr>
                    <tr>
                        <th>Aangepast op</th>
                        <th>{{ \Carbon\Carbon::parse($role->updated_at)->format('d-m-Y H:i:s') }}</th>
                    </tr>
                    <tr>
                        <th>Aangemaakt op</th>
                        <th>{{ \Carbon\Carbon::parse($role->created_at)->format('d-m-Y H:i:s') }}</th>
                    </tr>
                    </tbody>
                </table>

            </div>

            @if ($errors->any())
                <div class="text-danger">
                   <p>Er is iets misgegaan...</p>
                </div>
            @endif

            <div class="d-flex flex-row flex-wrap gap-2">
                <button
                    onclick="function handleButtonClick(button) {
                                 button.disabled = true;
                                button.classList.add('loading');

                                // Show the spinner and hide the text
                                button.querySelector('.button-text').style.display = 'none';
                                button.querySelector('.loading-spinner').style.display = 'inline-block';
                                button.querySelector('.loading-text').style.display = 'inline-block';

                                button.closest('form').submit();
                            }
                            handleButtonClick(this)"
                    class="btn btn-success flex flex-row align-items-center justify-content-center">
                    <span class="button-text">Opslaan</span>
                    <span style="display: none" class="loading-spinner spinner-border spinner-border-sm" aria-hidden="true"></span>
                    <span style="display: none" class="loading-text" role="status">Laden...</span>
                </button>
                <a href="{{ route('admin.role-management') }}"
                   class="btn btn-danger text-white">Annuleren</a>
                <a class="delete-button btn btn-outline-danger"
                   data-id="{{ $role->id }}"
                   data-name="{{ $role->role }}"
                   data-link="{{ route('admin.role-management.delete', $role->id) }}">Verwijderen</a>
            </div>

            @else
                <div class="alert alert-warning d-flex align-items-center" role="alert">
                    <span class="material-symbols-rounded me-2">no_accounts</span>Geen rol gevonden...
                </div>

                <div class="d-flex flex-row flex-wrap gap-2">
                    <a href="{{ route('admin.role-management')}}" class="btn btn-info">Terug</a>
                </div>
            @endif

        </form>
    </div>
@endsection
