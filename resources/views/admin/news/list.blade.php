@extends('layouts.dashboard')

@php
    use Carbon\Carbon;
@endphp

@section('content')
    <div class="container col-md-11">
        <div class="d-flex flex-row justify-content-between align-items-center">
            <div class="d-flex flex-column">
                <h1>Blog</h1>

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">

                        <li class="breadcrumb-item" aria-current="page"><a href="{{route('admin')}}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Blog</li>
                    </ol>
                </nav>
            </div>

            <a href="{{ route('admin.news.new') }}"
               class="d-flex flex-row align-items-center justify-content-center btn btn-primary">
                <span class="material-symbols-rounded me-2">newspaper</span>
                <span class="no-mobile">Nieuw blog</span>
            </a>
        </div>

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
                </div>
            </div>
        </form>

        @if($news->count() > 0)
            <div class="overflow-scroll no-scrolbar" style="max-width: 100vw">
                <table class="table table-striped">
                    <thead class="thead-dark table-bordered table-hover">
                    <tr>
                        <th scope="col">Gepubliceerd</th>
                        <th scope="col">Titel</th>
                        <th scope="col">Datum</th>
                        <th scope="col">Categorie</th>
                        <th scope="col">Auteur</th>
                        <th scope="col">Opties</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach ($news as $news_item)
                        <tr id="{{ $news_item->id }}">
                            <th>
                                @if($news_item->accepted === 1)
                                    <span class="material-symbols-rounded">check</span>
                                @else
                                    <span class="material-symbols-rounded">close</span>
                                @endif
                            </th>
                            <th>{{ $news_item->title}}</th>
                            <th>{{ Carbon::parse($news_item->date)->format('d-m-Y') }}</th>
                            <th>{{ $news_item->category}}</th>
                            <th>
                                <a href="{{ route('admin.account-management.details', ['id' => $news_item->user_id]) }}"
                                   class="d-flex flex-column gap-1 align-items-center m-2 bg-light p-2 rounded text-center"
                                   target="_blank">
                                    @if($news_item->user->profile_picture)
                                        <img alt="profielfoto" class="profle-picture"
                                             src="{{ asset('/profile_pictures/' . $news_item->user->profile_picture) }}">
                                    @else
                                        <img alt="profielfoto" class="profle-picture"
                                             src="{{ asset('img/no_profile_picture.webp') }}">
                                    @endif
                                    {{ $news_item->user->name.' '.$news_item->user->infix.' '.$news_item->user->last_name }}
                                </a>
                            </th>

                            <th>
                                <div class="d-flex flex-row flex-wrap gap-2">
                                    <a href="{{ route('admin.news.details', ['id' => $news_item->id]) }}"
                                       class="btn btn-info">Details</a>
                                </div>
                            </th>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{ $news->links() }}
        @else
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <span class="material-symbols-rounded me-2">unsubscribe</span>Geen nieuws gevonden...
            </div>
        @endif

    </div>
@endsection
