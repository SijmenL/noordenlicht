@extends('layouts.dashboard')

@section('content')
    <div class="container col-md-11">
        <h1>Accommodaties</h1>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">

                <li class="breadcrumb-item" aria-current="page"><a href="{{route('admin')}}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Accommodaties</li>
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

        <form id="auto-submit" method="GET">
            <div class="d-flex">
                <div class="d-flex flex-row-responsive justify-content-between gap-2 mb-3 w-100">
                    <div class="input-group">
                        <label for="search" class="input-group-text" id="basic-addon1">
                            <span class="material-symbols-rounded">search</span></label>
                        <input id="search" name="search" type="text" class="form-control"
                               placeholder="Zoeken op naam, type"
                               aria-label="Zoeken" aria-describedby="basic-addon1" value="{{ $search }}" onchange="this.form.submit();">
                    </div>
                        <a href="{{ route('admin.accommodaties.new') }}" class="btn btn-outline-dark make-role-button">Maak een nieuwe accommodatie</a>
                </div>
            </div>
        </form>

        @if($all_accommodaties->count() > 0)
        <div class="" style="max-width: 100vw">
            <table class="table table-striped">
                <thead class="thead-dark table-bordered table-hover">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Naam</th>
                    <th scope="col">Type</th>
                    <th scope="col">Opties</th>
                </tr>
                </thead>
                <tbody>

                @foreach ($all_accommodaties as $accommodaties)
                    <tr id="{{ $accommodaties->id }}">
                        <th>{{ $accommodaties->id }}</th>
                        <th>{{ $accommodaties->name }}</th>
                        <th>{{ $accommodaties->type }}</th>
                        <th>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Opties
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a href="{{ route('admin.accommodaties.details', ['id' => $accommodaties->id]) }}"
                                           class="dropdown-item">Details</a></li>
                                    <li>
                                        <a href="{{ route('admin.accommodaties.edit', ['id' => $accommodaties->id]) }}"
                                           class="dropdown-item">Bewerk</a>
                                    </li>
                                </ul>
                            </div>
                        </th>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $all_accommodaties->links() }}
        @else
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <span class="material-symbols-rounded me-2">home</span>Geen accommodaties gevonden...
            </div>
        @endif
    </div>
@endsection
