@extends('layouts.dashboard')

@section('content')
    <div class="container col-md-11">
        <h1>Rollen</h1>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">

                <li class="breadcrumb-item" aria-current="page"><a href="{{route('admin')}}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Rollen</li>
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
                               placeholder="Zoeken op naam, beschrijving"
                               aria-label="Zoeken" aria-describedby="basic-addon1" value="{{ $search }}" onchange="this.form.submit();">
                    </div>
                        <a href="{{ route('admin.role-management.create') }}" class="btn btn-outline-dark make-role-button">Maak een nieuwe rol</a>
                </div>
            </div>
        </form>

        @if($all_roles->count() > 0)
        <div class="overflow-scroll no-scrolbar" style="max-width: 100vw">
            <table class="table table-striped">
                <thead class="thead-dark table-bordered table-hover">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Naam</th>
                    <th class="no-mobile" scope="col">Beschrijving</th>
                    <th scope="col">Opties</th>
                </tr>
                </thead>
                <tbody>

                @foreach ($all_roles as $all_role)
                    <tr id="{{ $all_role->id }}">
                        <th>{{ $all_role->id }}</th>
                        <th>{{ $all_role->role }}</th>
                        <th class="no-mobile">{{ $all_role->description }}</th>
                        <th>
                            <div class="d-flex flex-row flex-wrap gap-2">
                                <a href="{{ route('admin.role-management.edit', ['id' => $all_role->id]) }}"
                                   class="btn btn-dark">Bewerk</a>
                            </div>
                        </th>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $all_roles->links() }}
        @else
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <span class="material-symbols-rounded me-2">no_accounts</span>Geen rollen gevonden...
            </div>
        @endif
    </div>
@endsection
