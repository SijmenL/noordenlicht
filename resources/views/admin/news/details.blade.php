@extends('layouts.dashboard')

@section('content')
    <div class="container col-md-11">
        <h1>Blog Details</h1>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">

                <li class="breadcrumb-item" aria-current="page"><a href="{{route('admin')}}">Dashboard</a></li>
                <li class="breadcrumb-item" aria-current="page"><a
                        href="{{route('admin.news')}}">Blog</a></li>
                <li class="breadcrumb-item active" aria-current="page">Blog Details</li>
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

        @if($news !== null)
            <div class="bg-light p-3 rounded m-2">
                <h1>{{ $news->title }}</h1>
                <div class="d-flex align-items-center gap-2 flex-row-responsive mb-3">
                    <span>{{ $news->date }}</span>
                    <span class="no-mobile">·</span>
                    <span
                        class="text-capitalize badge rounded_pill bg-info text-black d-flex align-items-center justify-content-center">{{ $news->category }}</span>
                    <span class="no-mobile">·</span>
                    <span
                        style="font-weight: bolder">{{ $news->user->name.' '.$news->user->infix.' '.$news->user->last_name }}</span>
                    @if(isset($news->speltak))
                        <span class="no-mobile">·</span>
                        <span>{{ $news->speltak }}</span>
                    @endif
                </div>
                <h5 style="font-weight: bolder;">{{ $news->description }}</h5>
                <img class="news-image-full zoomable-image p-3" src="{{ asset('/files/news/news_images/'.$news->image.' ') }}"
                     alt="nieuws afbeelding">
                <div class="news-content">{!! $news->content !!}</div>

            </div>
        @else
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <span class="material-symbols-rounded me-2">unsubscribe</span>Geen niews gevonden...
            </div>
        @endif

        <div class="d-flex flex-row flex-wrap gap-2">
            <a href="{{ route('admin.news') }}" class="btn btn-info">Terug</a>
            <a href="{{ route('admin.news.edit', $news->id) }}" class="btn btn-dark">Bewerken</a>
            <a href="{{ route('admin.news.publish', $news->id) }}"
               class="btn btn-outline-dark">
                @if($news->accepted === 0)
                    Publiceer dit nieuws
                @else
                    Haal dit nieuws offline
                @endif</a>
            <a class="delete-button btn btn-outline-danger"
               data-id="{{ $news->id }}"
               data-name="'{{ $news->title }}'"
               data-link="{{ route('admin.news.delete', $news->id) }}">Verwijderen</a>
        </div>
    </div>
@endsection
