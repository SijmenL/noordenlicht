@extends('layouts.app')

@include('partials.editor')

@section('content')


@if($news !== null && $news->accepted === 1)

    <div id="fb-root"></div>
    <script>
        (function (d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s);
            js.id = id;
            js.src = "https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.0";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    </script>

    <div class="header" style="background-image: linear-gradient(rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.5)), url({{ asset('/files/news/news_images/'.$news->image) }})">
        <div>
            <p class="header-title">{{ $news->title }}</p>
        </div>
    </div>
    <div class="container col-md-11">
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <h1>{{ $news->title }}</h1>
        <div class="d-flex gap-2 flex-row-responsive align-items-start mb-3">
            <span>{{ $news->date->format('d-m-Y') }}</span>
            <span class="no-mobile">·</span>
            <span
                class="text-capitalize badge rounded_pill bg-info text-black d-flex align-items-center justify-content-center">{{ $news->category }}</span>
            <span class="no-mobile">·</span>
            <span
                style="font-weight: bolder">{{ $news->user->name . ' ' . $news->user->infix . ' ' . $news->user->last_name }}</span>
            @if(isset($news->speltak))
                <span class="no-mobile">·</span>
                <span>{{ $news->speltak }}</span>
            @endif
        </div>
        <h5 style="font-weight: bolder;">{{ $news->description }}</h5>

        <div class="d-flex flex-row gap-2 align-items-center">
            <div class="fb-share-button"
                 data-href="{{ request()->fullUrl() }}"
                 data-layout="button_count">
            </div>

            <a href="https://twitter.com/share?ref_src=twsrc%5Etfw" class="twitter-share-button"
               data-text="{{ $news->description }}" data-url="{{request()->fullUrl()}}"
               data-show-count="false">Tweet</a>
            <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>

        </div>

        <img class="news-image-full p-4" src="{{ asset('files/news/news_images/' . $news->image) }}"
             alt="nieuws afbeelding">
        <div class="news-content">{!! $news->content !!}</div>
    </div>
@else
    <div class="header" style="background-image: linear-gradient(rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.5)), url({{ asset('/files/news/DSC00617.JPG') }})">
        <div>
            <p class="header-title">Geen nieuws gevonden</p>
        </div>
    </div>
    <div class="container col-md-11">
        <h1>We hebben geen nieuws gevonden</h1>
        <p>Het item is mogelijk verwijderd of verplaatst.</p>

        <button onclick="breakOut()" class="btn btn-primary text-white">Ga terug naar het overzicht</button>

        <script>
            function breakOut() {
                window.parent.location.href = 'https://waterscoutingmhg.nl/over-onze-club/nieuws';
            }
        </script>
    </div>

@endif
@endsection
