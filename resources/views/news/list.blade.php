@extends('layouts.app')

@include('partials.editor')

@section('content')

    <div class="container col-md-11">
        <h1>Het laatste nieuws</h1>
        <p>Lees hier op ons blog onze nieuwsbrieven, nieuwtjes en andere leuke dingen die we gedeeld hebben!</p>
        @if($news->count() > 0)
            <form id="auto-submit" method="GET"
                  class="user-select-forum-submit mt-5 w-100 d-flex justify-content-center">
                <div class="d-flex w-75">
                    <div class="d-flex flex-row-responsive gap-2 align-items-center mb-3 w-100">
                        <div class="input-group">
                            <label for="search" class="input-group-text" id="basic-addon1">
                                <span class="material-symbols-rounded">search</span></label>
                            <input id="search" name="search" type="text" class="form-control"
                                   placeholder="Zoeken op nieuws."
                                   aria-label="Zoeken" aria-describedby="basic-addon1" value="{{ $search }}"
                                   onchange="this.form.submit();">
                        </div>
                    </div>
                </div>
            </form>

            <div class="d-flex flex-row-responsive flex-wrap gap-4 justify-content-center"
                 style="margin: 30px; padding: 5px">
                @foreach($news as $news_item)
                    <a href="{{ route('news.item', $news_item->id) }}" class="text-black text-decoration-none d-flex justify-content-center align-items-center flex-column">
                        <div class="card bg-whited" style="cursor: pointer; margin: 0 auto">
                            <div class="card-img-top d-flex justify-content-center align-items-center">
                                <img alt="Nieuws afbeelding" class="news-image"
                                     style="border-radius: 50%; aspect-ratio: 1/1; object-fit: cover"
                                     src="{{ asset('/files/news/news_images/'.$news_item->image.' ') }}">
                            </div>
                            <div class="align-items-center news-badge d-flex justify-content-center">
                                <p class="badge rounded-pill bg-info text-center text-black"
                                   style="font-size: 1rem">{{ $news_item->category }}</p>
                            </div>

                            <div class="card-body d-flex flex-column justify-content-between p-0"
                                 style="margin-top: -20px">
                                <div>
                                    <h2 class="text-black text-center">{{ $news_item->title }}</h2>
                                    <div class="d-flex flex-row ps-3 pe-3 justify-content-between">
                                        <p class="d-flex align-items-center gap-2"><span
                                                class="material-symbols-rounded text-secondary">person</span><span>{{ $news_item->user->name }}</span>
                                        </p>
                                        <p class="d-flex align-items-center gap-2"><span
                                                class="material-symbols-rounded text-secondary">calendar_month</span><span>{{ $news_item->date->format('d-m-Y') }}</span>
                                        </p>
                                    </div>
                                    <p class="text-center">{{ $news_item->description }}</p>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
                <div class="d-flex flex-row w-100 align-items-center justify-content-center">
                    {{ $news->appends(request()->query())->links() }}
                </div>
        @else
            <div class="alert alert-warning d-flex align-items-center mt-4" role="alert">
                <span class="material-symbols-rounded me-2">unsubscribe</span>Geen nieuws gevonden...
            </div>
        @endif
    </div>
@endsection
