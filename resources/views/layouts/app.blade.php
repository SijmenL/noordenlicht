@include('partials.footer')

    <!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>NoordenLicht</title>
    <meta name="description" content="Natuurlijk Centrum voor Verbinding en BewustZijn">
    <link rel="apple-touch-icon" sizes="180x180" href="/public/apple-touch-icon.png">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link rel="mask-icon" href="/public/safari-pinned-tab.svg" color="#0092df">
    <meta name="msapplication-TileColor" content="#1c244b">
    <meta name="theme-color" content="#ffffff">

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Figtree">
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,1,0"/>
    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/css/app.css', 'resources/js/app.js', 'resources/js/bootstrap.js'])
</head>
<body>
<div id="app">
    <div>
        <nav class="navbar rounded-bottom-5 navbar-expand-md navbar-light bg-white sticky-top">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img class="logo" alt="logo" src="{{ asset('img/logo/logo.png') }}">
                </a>
                <div class="d-flex flex-row justify-content-center align-items-center">
                    <a id="hamburger-menu" class="navbar-toggler" data-bs-toggle="collapse"
                       data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                       aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                        <svg id="hamburger-icon" aria-hidden="true" role="presentation"
                             class="w-100 navbar-closed"
                             viewBox="0 0 1000 1000" xmlns="http://www.w3.org/2000/svg">
                            <path fill="#624B25FF"
                                  d="M104 333H896C929 333 958 304 958 271S929 208 896 208H104C71 208 42 237 42 271S71 333 104 333ZM104 583H896C929 583 958 554 958 521S929 458 896 458H104C71 458 42 487 42 521S71 583 104 583ZM104 833H896C929 833 958 804 958 771S929 708 896 708H104C71 708 42 737 42 771S71 833 104 833Z"></path>
                        </svg>
                        <svg id="hamburger-close-icon" aria-hidden="true" role="presentation"
                             class="w-100 navbar-open d-none"
                             viewBox="0 0 1000 1000" xmlns="http://www.w3.org/2000/svg">
                            <path fill="#624B25FF"
                                  d="M742 167L500 408 258 167C246 154 233 150 217 150 196 150 179 158 167 167 154 179 150 196 150 212 150 229 154 242 171 254L408 500 167 742C138 771 138 800 167 829 196 858 225 858 254 829L496 587 738 829C750 842 767 846 783 846 800 846 817 842 829 829 842 817 846 804 846 783 846 767 842 750 829 737L588 500 833 258C863 229 863 200 833 171 804 137 775 137 742 167Z"></path>
                        </svg>
                    </a>
                </div>
                <div class="collapse navbar-collapse navbar-info rounded-bottom-5" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav align-items-start justify-content-start justify-content-md-start"
                        id="navbar">
                        <li class="nav-item">
                            <a class="nav-link d-flex flex-row gap-1 justify-content-center align-items-center white-text"
                               href="">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex flex-row gap-1 justify-content-center align-items-center white-text"
                               href="">Accommodaties</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex flex-row gap-1 justify-content-center align-items-center white-text"
                               href="">Events</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex flex-row gap-1 justify-content-center align-items-center white-text"
                               href="">BosVrienden</a>
                        </li>
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto d-flex align-items-center">
                        <!-- Authentication Links -->
                        <ul class="navbar-nav align-items-start justify-content-start justify-content-md-start"
                            id="navbar">
                            <li class="nav-item">
                                <a class="nav-link no-line d-flex flex-row gap-1 justify-content-center align-items-center white-text"
                                   href=""><span class="material-symbols-rounded">shopping_cart</span></a>
                            </li>
                            @guest
                                @if (Route::has('login'))
                                    <li class="nav-item">
                                        <a class="nav-link no-line d-flex flex-row gap-1 justify-content-center align-items-center white-text"
                                           href=""><span class="material-symbols-rounded">login</span></a>
                                    </li>
                                @endif
                            @else
                                <li class="nav-item dropdown" id="menu-dropdown">
                                    <a id="navbarDropdown" class="dropdown-toggle" href="#" role="button"
                                       data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                        @if(Auth::user()->profile_picture)
                                            <img alt="profielfoto" class="profle-picture"
                                                 src="{{ asset('/profile_pictures/' . Auth::user()->profile_picture) }}">
                                        @else
                                            <img alt="profielfoto" class="profle-picture"
                                                 src="{{ asset('img/no_profile_picture.webp') }}">
                                        @endif
                                    </a>
                                    <div id="dropdown-menu" class="dropdown-menu dropdown-menu-end"
                                         aria-labelledby="navbarDropdown">
                                        <a class="dropdown-item" href="{{ route('logout') }}"
                                           onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                            {{ __('Log uit') }}
                                        </a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                              class="d-none">
                                            @csrf
                                        </form>
                                    </div>
                                </li>
                            @endguest
                        </ul>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
    <div>
        <footer>
            @yield('footer')
        </footer>
    </div>
</div>
</body>
</html>
