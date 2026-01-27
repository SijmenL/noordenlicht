@extends('layouts.app')

@include('partials.editor')

@section('content')

    <div class="rounded-bottom-5 bg-white d-flex flex-column container-block"
         style="position: relative; z-index: 9; margin-top: -25px; background-position: center !important; background-image: url('{{ asset('img/logo/doodles/Wolf.webp') }}'); background-repeat: no-repeat; background-size: cover; min-height: 100vh;">

        <div class="container py-5">
            <div class="container justify-content-center align-items-center d-flex flex-column gap-5">
                <div style="backdrop-filter: blur(2px);">
                    <h1 class="text-center fw-bold display-5">Blog</h1>
                    <h2 class="text-center fs-4 text-secondary fw-light">Lees hier op ons blog nieuwtjes en andere leuke dingen die we gedeeld hebben!</h2>
                </div>

                <div class="w-100" style="max-width: 900px;">
                    <form id="auto-submit" method="GET" class="d-flex flex-column gap-4">

                        {{-- 1. Prominent Search Bar --}}
                        <div class="position-relative">
                            <span class="material-symbols-rounded position-absolute text-muted"
                                  style="top: 50%; left: 20px; transform: translateY(-50%); font-size: 24px;">search</span>
                            <input id="search" name="search" type="text"
                                   class="form-control form-control-lg border-0 shadow-sm ps-5 py-3 rounded-pill custom-search-input"
                                   placeholder="Zoeken naar een blogpost"
                                   value="{{ $search ?? '' }}"
                                   autocomplete="off"
                                   onchange="this.form.submit();">
                        </div>
                    </form>
                </div>

                @if($news->count() > 0)
            <div class="d-flex flex-row-responsive flex-wrap gap-4 justify-content-center"
                 style="margin: 30px; padding: 5px">
                @foreach($news as $news_item)
                    <a href="{{ route('news.item', $news_item->id) }}" class="text-black text-decoration-none d-flex justify-content-center align-items-center flex-column">
                        <div class="card bg-whited" style="cursor: pointer; margin: 0 auto">
                            <div class="card-img-top d-flex justify-content-center align-items-center">
                                <img alt="Blog afbeelding" class="news-image"
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
                    <div class="text-center py-5">
                        <div class="display-1 text-muted"><i class="bi bi-mailbox"></i></div>
                        <p class="h4 mt-3">Geen blogposts gevonden.</p>
                        <a href="{{ route('news.list') }}" class="btn btn-link">Wis zoekopdracht</a>
                    </div>
        @endif
    </div>
@endsection
