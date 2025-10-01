@extends('layouts.dashboard')

@section('content')
    <div class="container col-md-11">
        <h1>Maak rol</h1>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">

                <li class="breadcrumb-item" aria-current="page"><a href="{{route('admin')}}">Dashboard</a></li>
                <li class="breadcrumb-item" aria-current="page"><a href="{{route('admin.role-management')}}">Rollen</a></li>
                <li class="breadcrumb-item active" aria-current="page">Maak rol</li>
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

        <form method="POST" action="{{ route('admin.role-management.create.store') }}"
              enctype="multipart/form-data">
            @csrf
            <div class="overflow-scroll no-scrolbar" style="max-width: 100vw">
                <table class="table table-striped">
                    <tbody>
                    <tr>
                        <th><label for="role" class="col-md-4 col-form-label">Naam</label></th>
                        <th>
                            <input id="role" type="text" class="form-control @error('role') is-invalid @enderror"
                                   name="role" value="{{ old('role') }}" autocomplete="role" autofocus>
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
                                   name="description" value="{{ old('description') }}" autocomplete="description" autofocus>
                            @error('description')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </th>
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
            </div>



        </form>
    </div>
@endsection
