@php
    use Illuminate\Support\Facades\Route;
    use App\Models\Accommodatie;

    $currentRoute = Route::currentRouteName();

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
            'name' => 'Winkel',
            'route' => 'shop',
        ],
        // The Info Dropdown
        [
            'name' => 'Info',
            'route' => null, // This one stays null, so it acts purely as a label if needed, or we point to a general info page
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
]]
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
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,1,0"/>
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
            height: 22px; /* Overall height of the icon */
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

        .hamburger-lines .line1 {
            transform-origin: 0% 0%;
            transition: transform 0.3s ease-in-out;
        }

        .hamburger-lines .line2 {
            transition: transform 0.1s ease-in-out;
        }

        .hamburger-lines .line3 {
            transform-origin: 0% 100%;
            transition: transform 0.3s ease-in-out;
        }

        /* Active State (When aria-expanded="true") */
        .navbar-toggler[aria-expanded="true"] .line1 {
            transform: rotate(45deg);
        }

        .navbar-toggler[aria-expanded="true"] .line2 {
            transform: scaleY(0);
        }

        .navbar-toggler[aria-expanded="true"] .line3 {
            transform: rotate(-45deg);
        }

        /* --- Desktop Specific Styling (Hover & Animation) --- */
        @media (min-width: 768px) {
            .navbar-nav {
                align-items: center; /* Ensure vertical centering */
            }

            /* Dropdown Menu Box */
            .dropdown-menu {
                border: none;
                border-radius: 12px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); /* Softer shadow */
                padding: 10px 0;
                min-width: 220px;
                margin-top: 0;

                /* Center relative to parent */
                left: 50%;
                transform: translateX(-50%) translateY(10px); /* Start slightly lower */

                /* Animation State Initial */
                display: block;
                opacity: 0;
                visibility: hidden;
                pointer-events: none; /* Prevent clicking when invisible */

                /* Subtle Fade & Slide Up */
                transition: opacity 0.3s ease, transform 0.3s ease, visibility 0.3s;
            }

            /* Show Dropdown on Hover of the LI */
            .nav-item.dropdown:hover .dropdown-menu {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
                transform: translateX(-50%) translateY(0); /* Smooth landing */
            }

            /* Little Arrow on top of dropdown */
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

            /* Dropdown Items */
            .dropdown-item {
                padding: 8px 20px;
                color: #555;
                font-size: 0.95rem;
                transition: background-color 0.2s ease, color 0.2s ease;
            }

            .dropdown-item:hover {
                background-color: #f8f9fa;
                color: #5a7123; /* Theme Color */
                /* Removed the drastic slide-right padding change for a cleaner feel */
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
                padding: 1rem;
                margin-top: 1rem;
                border-radius: 1rem;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);

                /* 1. SCROLLABLE NAVBAR FIX */
                max-height: 75vh;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch; /* Smooth scroll on iOS */
            }

            .navbar-nav .nav-item {
                width: 100%;
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                align-items: center;
            }

            .dropdown-menu {
                border: none;
                background-color: #f9f9f9;
                box-shadow: none;
                padding: 0;
                margin-top: 0.5rem;
                border-radius: 0.5rem;
                width: 100%; /* Full width in mobile */
                text-align: center;
            }

            .dropdown-item {
                padding: 12px;
                color: #666;
                border-bottom: 1px solid #eee;
            }

            .dropdown-item:last-child {
                border-bottom: none;
            }

            .dropdown-item:active {
                background-color: #e9ecef;
            }

            /* Mobile Split Button Styling */
            .nav-link.main-link {
                flex-grow: 1; /* Take up space */
                text-align: center;
            }

            /* The specific toggle button for mobile */
            .mobile-dropdown-toggle {
                background: none;
                border: none;
                padding: 10px;
                color: #624B25;
                display: flex;
                align-items: center;
                cursor: pointer;
            }

            /* Rotate chevron when open */
            .mobile-dropdown-toggle[aria-expanded="true"] span {
                transform: rotate(180deg);
                transition: transform 0.3s ease;
            }

            .mobile-dropdown-toggle span {
                transition: transform 0.3s ease;
            }
        }
    </style>
</head>
<body>
<div id="app">
    <div class="d-flex flex-column justify-content-between min-vh-100">
        @yield('above-nav')
        <nav class="navbar navbar-expand-md navbar-light bg-white sticky-top rounded-bottom-5"
             style="z-index: 99998;">
            <div class="container d-flex flex-column">
                <div class="d-flex justify-content-end w-100">
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

                <div class="collapse navbar-collapse justify-content-center rounded-bottom-5 navbar-info"
                     id="navbarContent">
                    <ul class="navbar-nav text-center align-items-center gap-1 gap-md-2">

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
                            @endphp

                            @if($hasSubPages)
                                <li class="nav-item dropdown">
                                    <a class="nav-link main-link white-text {{ $isActive ? 'active' : '' }}"
                                       href="{{ $url }}">
                                        @if(isset($item['is_icon']) && $item['is_icon'])
                                            <span class="material-symbols-rounded">{{ $item['name'] }}</span>
                                        @else
                                            {{ $item['name'] }}
                                        @endif
                                    </a>

                                    <button class="mobile-dropdown-toggle"
                                            type="button"
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                        <span class="material-symbols-rounded fs-5">expand_more</span>
                                    </button>

                                    <ul class="dropdown-menu">
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
                                <li class="nav-item">
                                    <a class="nav-link white-text {{ $isActive ? 'active' : '' }}" href="{{ $url }}">
                                        @if(isset($item['is_icon']) && $item['is_icon'])
                                            <span class="material-symbols-rounded">{{ $item['name'] }}</span>
                                        @else
                                            {{ $item['name'] }}
                                        @endif
                                    </a>
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
    document.addEventListener('DOMContentLoaded', function () {
        // --- Navbar Logic Removed ---
        // The CSS animation handles the hamburger now, so we don't need JS to swap SVGs anymore.
        // Bootstrap's data-bs-toggle handles the aria-expanded state which triggers our CSS rotation.

        // --- Cookie Banner Logic ---
        const banner = document.getElementById('cookie-banner');
        const acceptBtn = document.getElementById('cookie-accept');
        const declineBtn = document.getElementById('cookie-decline');

        // Cookies that are necessary for the site to function (Login/CSRF)
        const essentialCookies = ['XSRF-TOKEN', 'laravel_session'];

        function deleteNonEssentialCookies() {
            const cookies = document.cookie.split(";");

            for (let i = 0; i < cookies.length; i++) {
                const cookie = cookies[i];
                const eqPos = cookie.indexOf("=");
                const name = eqPos > -1 ? cookie.substr(0, eqPos).trim() : cookie.trim();

                // Delete if not in whitelist
                if (!essentialCookies.includes(name)) {
                    // Try to delete for current path and root
                    document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/";
                    document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=" + window.location.pathname;
                    // Try to delete for domain
                    document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/;domain=" + window.location.hostname;
                }
            }
        }

        // Check LocalStorage for consent
        const consent = localStorage.getItem('cookie_consent');

        if (!consent) {
            // Show banner if no choice made
            banner.classList.remove('d-none');
        } else if (consent === 'declined') {
            // If previously declined, ensure clean state
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
