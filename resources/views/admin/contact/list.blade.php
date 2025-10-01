@extends('layouts.dashboard')

@vite('resources/js/user-export.js')

@section('content')
    <div class="container col-md-11">
        <h1>Contact</h1>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item" aria-current="page"><a href="{{route('admin')}}">Administratie</a></li>
                <li class="breadcrumb-item active" aria-current="page">Contact</li>
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
                <div class="d-flex flex-row-responsive gap-2 align-items-center mb-3 w-100"
                     style="justify-items: stretch">
                    <div class="input-group">
                        <label for="search" class="input-group-text" id="basic-addon1">
                            <span class="material-symbols-rounded">search</span></label>
                        <input id="search" name="search" type="text" class="form-control"
                               placeholder="Zoeken op naam, email, adres etc."
                               aria-label="Zoeken" aria-describedby="basic-addon1" value="{{ $search }}"
                               onchange="this.form.submit();">
                    </div>
                    <div class="input-group">
                        <label for="seen" class="input-group-text" id="basic-addon1">
                            <span class="material-symbols-rounded">done</span></label>
                        <select id="seen" name="seen" class="form-select"
                                aria-label="seen" aria-describedby="basic-addon1" onchange="this.form.submit();">
                            <option @if($seen === 'all') selected @endif value="all">Alles</option>
                            <option @if($seen === 'seen') selected @endif value="seen">Afgehandeld</option>
                            <option @if($seen === 'unseen') selected @endif value="unseen">Niet afgehandeld</option>
                        </select>
                    </div>
                </div>
            </div>
        </form>

        @if($contact_submissions->count() > 0)
            <div class="overflow-scroll no-scrolbar" style="max-width: 100vw">
                <table class="table table-striped">
                    <thead class="thead-dark table-bordered table-hover">
                    <tr>
                        <th scope="col">Datum</th>
                        @if($seen === 'all')
                            <th scope="col">Afgehandeld</th>
                        @endif
                        <th scope="col">Naam</th>
                        <th scope="col">Bericht</th>
                        <th scope="col">Opties</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach ($contact_submissions as $contact)
                        <tr id="{{ $contact->id }}" class="@if($contact->done === 1 && $seen === 'all') table-success @endif">
                            <th>{{ $contact->created_at}}</th>
                            @if($seen === 'all')
                            <th>
                                @if($contact->done === 1)
                                    <span class="material-symbols-rounded">check</span>
                                @else
                                    <span class="material-symbols-rounded">close</span>
                                @endif
                            </th>
                            @endif
                            <th>{{ $contact->name}}</th>
                            <th>
                                <p>{{ Str::limit($contact->message, 75, '...') }}</p>
                            </th>
                            <th>
                                <div class="d-flex flex-row flex-wrap gap-2">
                                    <a href="{{ route('admin.contact.details', ['id' => $contact->id]) }}"
                                       class="btn btn-info">Details</a>
                                </div>
                            </th>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{ $contact_submissions->links() }}
        @else
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <span class="material-symbols-rounded me-2">phone_disabled</span>Geen contact gevonden...
            </div>
        @endif

    </div>
@endsection
