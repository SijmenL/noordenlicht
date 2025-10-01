@extends('layouts.dashboard')
@include('partials.editor')

@vite(['resources/js/texteditor.js', 'resources/css/texteditor.css'])

@php
    use Carbon\Carbon;
    Carbon::setLocale('nl');
@endphp

@section('content')
    <div class="container col-md-11">
        <h1>Bewerk {{ $news->title }}</h1>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">

                <li class="breadcrumb-item" aria-current="page"><a href="{{route('admin')}}">Dashboard</a></li>
                <li class="breadcrumb-item" aria-current="page"><a
                        href="{{route('admin.news')}}">Blog</a></li>
                <li class="breadcrumb-item active" aria-current="page">Bewerk {{ $news->title }}</li>
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

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-light rounded-2 p-3">
            <div class="container">
                <form method="POST" action="{{ route('admin.news.edit.save', $news->id) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="d-flex flex-column">
                        <label for="title" class="col-md-4 col-form-label ">Titel van je bericht</label>
                        <input name="title" type="text" class="form-control" id="title" value="{{ $news->title }}"
                        >
                        @error('title')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="d-flex flex-column">
                        <label for="category" class="col-md-4 col-form-label ">Categorie waar je bericht invalt</label>
                        <select id="category"
                                class="w-100 form-select @error('category') is-invalid @enderror"
                                name="category">
                            <option value="Nieuwsbrief" {{ $news->category == 'Nieuwsbrief' ? 'selected' : '' }}>Nieuwsbrief</option>
                            <option value="Post" {{ $news->category == 'Post' ? 'selected' : '' }}>Post</option>
                            <option value="Artikel" {{ $news->category == 'Artikel' ? 'selected' : '' }}>Artikel
                            </option>
                            <option value="Update" {{ $news->category == 'Update' ? 'selected' : '' }}>Update</option>
                        </select>
                        @error('category')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="">
                        <label for="image" class="col-md-4 col-form-label ">Coverafbeelding</label>
                        <div class="d-flex flex-row-responsive gap-4 align-items-center justify-content-center">
                            <img alt="Nieuws afbeelding" class="w-50"
                                 src="{{ asset('/files/news/news_images/'.$news->image.' ') }}">
                            <input class="form-control mt-2 col" id="image" type="file" name="image"
                                   accept="image/*">
                            @error('image')
                        </div>
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="d-flex flex-column">
                        <label for="date" class="col-md-4 col-form-label ">Publicatiedatum</label>
                        <input name="date" type="date" class="form-control" id="date" value="{{ \Carbon\Carbon::parse($news->date)->format('Y-m-d') }}">

                        @error('date')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="d-flex flex-column">
                        <label for="description" class="col-md-4 col-form-label ">Korte samenvatting of beschrijving</label>
                        <input name="description" type="text" class="form-control" id="description" value="{{ $news->description }}"
                        >
                        @error('description')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                        <small id="characters2">0/200</small>
                    </div>

                    <script>
                        let textInput2 = document.getElementById('description')
                        let characters2 = document.getElementById('characters2')

                        setCharacters();

                        addEventListener('input', function () {
                            setCharacters()
                        });

                        function setCharacters() {
                            characters2.innerHTML = `${textInput2.value.toString().length}/200`;

                            if (textInput2.value.toString().length > 200) {
                                characters2.style.color = 'red';
                            } else {
                                characters2.style.color = 'black';
                            }
                        }

                    </script>

                    <div class="mt-4">
                        <label for="text-input">De content van je bericht</label>
                        <div class="editor-parent">
                            @yield('editor')
                            <div id="text-input" contenteditable="true" name="text-input"
                                 class="text-input">{!! $news->content !!}</div>
                            <small id="characters"></small>
                        </div>

                        @error('content')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <input id="content" name="content" type="hidden" value="{{ $news->content }}">

                    <div class="d-flex flex-row gap-2 align-items-center mt-3">
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
                        <a href="{{ route('admin.news.details', $news->id) }}" class="btn btn-danger text-white">Annuleren</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection

