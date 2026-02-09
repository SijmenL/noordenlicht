@php
    use Illuminate\Support\Facades\Route;
    use App\Models\Accommodatie;

    $currentRoute = Route::currentRouteName();

    // Fetch Accommodations
    $accommodations = Accommodatie::orderBy('order')->get()->map(function($accommodation) {
        return [
            'name' => $accommodation->name,
            'url' => '/accommodaties/' . $accommodation->id,
        ];
    })->toArray();

    // Define the Menu Structure
    $menuItems = [
        [
            'name' => 'Home',
            'route' => 'home',
        ],
        [
            'name' => 'Accommodaties',
            'route' => 'accommodaties',
            'sub-pages' => $accommodations,
        ],
        [
            'name' => 'Events',
            'route' => 'agenda.public.schedule',
        ],
        [
            'name' => 'Toevoegingen',
            'route' => 'shop',
        ],
        // The Info Dropdown
        [
            'name' => 'Info',
            'route' => null,
            'sub-pages' => [
                [
                    'name' => 'Bosvrienden',
                    'route' => 'home.rules',
                ],
                [
                    'name' => 'Prijslijst',
                    'route' => 'prices.list',
                ],
                [
                    'name' => 'Huisregels',
                    'route' => 'home.rules',
                ],
                [
                    'name' => 'Algemene Voorwaarden',
                    'route' => 'home.eula',
                ],
                [
                    'name' => 'Annuleringsvoorwaarden',
                    'route' => 'home.cancellation',
                ],
                [
                    'name' => 'Privacyverklaring',
                    'route' => 'home.privacy',
                ],
            ]
        ],
        [
            'name' => 'Contact',
            'route' => 'contact',
        ],
        // Shopping Cart (Icon Only)
        [
            'name' => 'shopping_cart',
            'route' => 'checkout',
            'is_icon' => true,
        ],
    ];

    // Append Auth specific items
    if (auth()->guest()) {
        $menuItems[] = [
            'name' => 'login',
            'route' => 'login',
            'is_icon' => true,
        ];
    } else {
        // Admin Link
        if (auth()->user()->roles->contains('role', 'Administratie')) {
            $menuItems[] = [
                'name' => 'dashboard',
                'route' => 'admin',
                'is_icon' => true,
            ];
        }
        // User Settings
        $menuItems[] = [
            'name' => 'person',
            'route' => 'user.settings',
            'is_icon' => true,
            'sub-pages' => [
                [
                    'name' => 'Log uit',
                    'route' => 'logout',
                    'logout' => true
                ]
            ]
        ];
    }
@endphp

@include('partials.footer')

    <!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>NoordenLicht</title>
    <meta name="description" content="Natuurlijk Centrum voor Verbinding en BewustZijn">
    <link rel="apple-touch-icon" sizes="180x180" href="/public/apple-touch-icon.png">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link rel="mask-icon" href="/public/safari-pinned-tab.svg" color="#0092df">
    <meta name="msapplication-TileColor" content="#1c244b">
    <meta name="theme-color" content="#ffffff">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Figtree">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,1,0"/>

    @vite(['resources/sass/app.scss', 'resources/css/app.css', 'resources/js/app.js', 'resources/js/bootstrap.js', 'resources/js/texteditor.js', 'resources/js/user-export.js', 'resources/css/texteditor.css'])

    <style>
        /* --- General Navbar Styling --- */
        .navbar-nav .nav-link {
            transition: color 0.3s ease;
            position: relative;
        }

        /* --- Hamburger Animation CSS --- */
        .hamburger-lines {
            width: 30px;
            height: 22px;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .hamburger-lines .line {
            display: block;
            height: 3px;
            width: 100%;
            border-radius: 10px;
            background: #624B25; /* Theme Color */
        }

        .hamburger-lines .line1 { transform-origin: 0% 0%; transition: transform 0.3s ease-in-out; }
        .hamburger-lines .line2 { transition: transform 0.1s ease-in-out; }
        .hamburger-lines .line3 { transform-origin: 0% 100%; transition: transform 0.3s ease-in-out; }

        .navbar-toggler[aria-expanded="true"] .line1 { transform: rotate(45deg); }
        .navbar-toggler[aria-expanded="true"] .line2 { transform: scaleY(0); }
        .navbar-toggler[aria-expanded="true"] .line3 { transform: rotate(-45deg); }

        /* Remove ugly outline on focus */
        .navbar-toggler:focus {
            box-shadow: none;
            outline: none;
        }

        /* --- Desktop Specific Styling (Hover & Animation) --- */
        @media (min-width: 768px) {
            .navbar-nav {
                align-items: center;
            }

            /* Dropdown Menu Box - Desktop */
            .dropdown-menu {
                border: none;
                border-radius: 12px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
                padding: 10px 0;
                min-width: 220px;
                margin-top: 0;
                left: 50%;
                transform: translateX(-50%) translateY(10px);
                display: block;
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                transition: opacity 0.3s ease, transform 0.3s ease, visibility 0.3s;
            }

            .nav-item-bar.dropdown:hover .dropdown-menu {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
                transform: translateX(-50%) translateY(0);
            }

            .dropdown-menu::before {
                content: '';
                position: absolute;
                top: -6px;
                left: 50%;
                transform: translateX(-50%);
                border-left: 7px solid transparent;
                border-right: 7px solid transparent;
                border-bottom: 7px solid white;
            }

            .dropdown-item {
                padding: 8px 20px;
                color: #555;
                font-size: 0.95rem;
                transition: background-color 0.2s ease, color 0.2s ease;
            }

            .dropdown-item:hover {
                background-color: #f8f9fa;
                color: #5a7123;
            }

            /* Hide the mobile toggle button on desktop */
            .mobile-dropdown-toggle {
                display: none !important;
            }
        }

        /* --- Mobile Specific Styling --- */
        @media (max-width: 767.98px) {
            .navbar-collapse {
                background-color: white;
                /* Make it full width and clean */
                position: absolute;
                /* Overlap slightly with the navbar to hide behind the rounded corners */
                top: calc(100% - 20px);
                left: 0;
                right: 0;
                width: 100%;
                /* Add padding to account for the overlap */
                padding: 20px 0 0 0;
                box-shadow: 0 10px 20px rgba(0,0,0,0.1);
                border-top: none;
                max-height: 80vh;
                overflow-y: auto;
                /* Ensure it sits behind the navbar header (which we will set to 99998) */
                z-index: 99997;
            }

            .navbar-nav {
                width: 100%;
                padding: 1rem 0;
            }

            .nav-item-bar {
                width: 100%;
                border-bottom: 1px solid #f8f8f8;
            }

            .nav-item-bar:last-child {
                border-bottom: none;
            }

            /* Container to split Text and Arrow to edges */
            .mobile-nav-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                width: 100%;
                padding-right: 15px; /* Space for the arrow */
            }

            .nav-link-bar {
                padding: 15px 20px !important; /* Larger touch target */
                font-size: 1.1rem;
                text-align: left;
                width: 100%;
                color: #333;
            }

            .nav-link-bar.active {
                color: #5a7123 !important;
                font-weight: 600;
            }

            /* The Arrow Button */
            .mobile-dropdown-toggle {
                background: none;
                border: none;
                padding: 10px;
                color: #624B25;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: transform 0.3s;
                /* Ensure large touch target */
                min-width: 44px;
                min-height: 44px;
            }

            .mobile-dropdown-toggle[aria-expanded="true"] span {
                transform: rotate(180deg);
            }

            /* Dropdown Items in Mobile */
            .dropdown-menu {
                border: none;
                background-color: #fcfcfc; /* Slight contrast */
                box-shadow: inset 0 2px 5px rgba(0,0,0,0.03); /* Inner shadow for depth */
                /* Increased padding as requested */
                padding: 1rem 0 0.5rem 0;
                margin: 0;
                border-radius: 0;
                width: 100%;

                /* IMPORTANT: Override Popper.js inline styles to force correct flow */
                position: static !important;
                transform: none !important;
                float: none;
            }

            .dropdown-item {
                padding: 12px 20px 12px 40px; /* Indented */
                color: #666;
                border-bottom: 1px solid #eee;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
<div id="app">
    <div class="d-flex flex-column justify-content-between min-vh-100">
        @yield('above-nav')

        <!-- Navbar z-index set higher than collapse so collapse slides from "behind" -->
        <nav class="navbar navbar-expand-md navbar-light bg-white sticky-top rounded-bottom-5" style="z-index: 99998;">
            <div class="container">
                <!-- Branding or Logo could go here -->
                <a class="navbar-brand d-md-none" href="{{ route('home') }}">
                    <!-- Optional: Add Logo Here -->
                </a>

                <div class="d-flex justify-content-end w-100 d-md-none">
                    <button class="navbar-toggler border-0 p-2" type="button" data-bs-toggle="collapse"
                            data-bs-target="#navbarContent" aria-controls="navbarContent"
                            aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                        <div class="hamburger-lines">
                            <span class="line line1"></span>
                            <span class="line line2"></span>
                            <span class="line line3"></span>
                        </div>
                    </button>
                </div>

                <div class="collapse navbar-collapse justify-content-center rounded-bottom-5" id="navbarContent">
                    <ul class="navbar-nav">
                        @foreach($menuItems as $item)
                            @php
                                // Resolve URL
                                $url = '#';
                                if (isset($item['route']) && $item['route']) {
                                    $url = route($item['route']);
                                } elseif (isset($item['url'])) {
                                    $url = $item['url'];
                                }

                                // Check Active State
                                $isActive = false;
                                if (isset($item['route']) && $item['route'] === $currentRoute) {
                                    $isActive = true;
                                }

                                // Check if it has dropdown items
                                $hasSubPages = !empty($item['sub-pages']);
                                // Unique ID for collapse
                                $collapseId = 'collapse-' . Str::slug($item['name']);
                                $menuId = 'menu-' . Str::slug($item['name']);
                            @endphp

                            @if($hasSubPages)
                                <li class="nav-item nav-item-bar dropdown">
                                    <div class="mobile-nav-row">
                                        <!-- Main Link -->
                                        <a class="nav-link nav-link-bar main-link {{ $isActive ? 'active' : '' }}"
                                           href="{{ isset($item['route']) || isset($item['url']) ? $url : 'javascript:void(0);' }}"
                                           @if(!isset($item['route']) && !isset($item['url'])) onclick="toggleMobileDropdown(event, '{{ $menuId }}', 'btn-{{ $collapseId }}')" @endif
                                        >
                                            @if(isset($item['is_icon']) && $item['is_icon'])
                                                <span class="material-symbols-rounded align-middle">{{ $item['name'] }}</span>
                                            @else
                                                {{ $item['name'] }}
                                            @endif
                                        </a>

                                        <!-- Mobile Dropdown Toggle (Arrow) -->
                                        <!-- Removed data-bs-toggle="dropdown" to fix the JS error -->
                                        <button class="mobile-dropdown-toggle"
                                                id="btn-{{ $collapseId }}"
                                                type="button"
                                                onclick="toggleMobileDropdown(event, '{{ $menuId }}', 'btn-{{ $collapseId }}')"
                                                aria-expanded="false">
                                            <span class="material-symbols-rounded fs-4">expand_more</span>
                                        </button>
                                    </div>

                                    <ul class="dropdown-menu" id="{{ $menuId }}">
                                        @foreach($item['sub-pages'] as $subPage)
                                            @php
                                                $subUrl = '#';
                                                if (isset($subPage['route']) && $subPage['route']) {
                                                    $subUrl = route($subPage['route']);
                                                } elseif (isset($subPage['url'])) {
                                                    $subUrl = $subPage['url'];
                                                }
                                            @endphp
                                            <li>
                                                @if(isset($subPage['logout']))
                                                    <a onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                                       class="dropdown-item">Uitloggen
                                                    </a>
                                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                                        @csrf
                                                    </form>
                                                @else
                                                    <a class="dropdown-item" href="{{ $subUrl }}">
                                                        {{ $subPage['name'] }}
                                                    </a>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @else
                                <li class="nav-item nav-item-bar">
                                    <div class="mobile-nav-row">
                                        <a class="nav-link nav-link-bar {{ $isActive ? 'active' : '' }}" href="{{ $url }}">
                                            @if(isset($item['is_icon']) && $item['is_icon'])
                                                <span class="material-symbols-rounded align-middle">{{ $item['name'] }}</span>
                                            @else
                                                {{ $item['name'] }}
                                            @endif
                                        </a>
                                    </div>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
        </nav>

        <main>
            @yield('content')
        </main>
        <footer>
            @yield('footer')
        </footer>
    </div>
</div>

<div id="cookie-banner" class="fixed-bottom bg-white rounded-top-5 p-3 d-none shadow-lg" style="z-index: 99999;">
    <div class="container d-flex flex-column justify-content-between align-items-center gap-2">
        <h1>Wij gebruiken cookies</h1>
        <div>
            <p class="mb-0">Om je de beste ervaring te geven, maken we gebruik van technologieën zoals cookies om
                informatie over je apparaat op te slaan of te raadplegen. Hiermee kunnen we bijvoorbeeld je surfgedrag
                bijhouden of unieke ID's op deze site verwerken. Als je akkoord gaat, kunnen we je een soepelere en meer
                gepersonaliseerde ervaring bieden. Als je geen toestemming geeft of deze later intrekt, kan dat ervoor
                zorgen dat sommige functies of mogelijkheden van de site minder goed werken.</p>
        </div>
        <div class="d-flex flex-row gap-2">
            <button id="cookie-accept" class="btn btn-success text-nowrap">Accepteren</button>
            <button id="cookie-decline" class="btn btn-light text-nowrap">Weigeren</button>
        </div>
    </div>
</div>

<script>
    // --- Custom Mobile Dropdown Toggle ---
    function toggleMobileDropdown(event, menuId, btnId) {
        // Prevent default anchor behavior if clicking empty link
        // and stop propagation to prevent weird bubbling issues
        if(event) {
            event.stopPropagation();
        }

        const menu = document.getElementById(menuId);
        const btn = document.getElementById(btnId);

        if(menu) {
            menu.classList.toggle('show');

            // Handle arrow rotation
            if(btn) {
                const isExpanded = btn.getAttribute('aria-expanded') === 'true';
                btn.setAttribute('aria-expanded', !isExpanded);
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // --- Cookie Banner Logic ---
        const banner = document.getElementById('cookie-banner');
        const acceptBtn = document.getElementById('cookie-accept');
        const declineBtn = document.getElementById('cookie-decline');

        const essentialCookies = ['XSRF-TOKEN', 'laravel_session'];

        function deleteNonEssentialCookies() {
            const cookies = document.cookie.split(";");

            for (let i = 0; i < cookies.length; i++) {
                const cookie = cookies[i];
                const eqPos = cookie.indexOf("=");
                const name = eqPos > -1 ? cookie.substr(0, eqPos).trim() : cookie.trim();

                if (!essentialCookies.includes(name)) {
                    document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/";
                    document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=" + window.location.pathname;
                    document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/;domain=" + window.location.hostname;
                }
            }
        }

        const consent = localStorage.getItem('cookie_consent');

        if (!consent) {
            banner.classList.remove('d-none');
        } else if (consent === 'declined') {
            deleteNonEssentialCookies();
        }

        acceptBtn.addEventListener('click', function () {
            localStorage.setItem('cookie_consent', 'accepted');
            banner.classList.add('d-none');
        });

        declineBtn.addEventListener('click', function () {
            localStorage.setItem('cookie_consent', 'declined');
            banner.classList.add('d-none');
            deleteNonEssentialCookies();
        });
    });
</script>

</body>
</html>
