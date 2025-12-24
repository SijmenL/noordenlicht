@php use App\Models\Contact; @endphp
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
        /* Mobile-first responsive styles */
        .mobile-header {
            display: none; /* Hidden by default (desktop) */
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: var(--bs-primary, #212529);
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1001;
            height: 70px;
        }

        .navbar-root {
            overflow-y: scroll;
            max-height: calc(100vh - 300px);
        }

        .hamburger-btn {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 8px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .hamburger-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .navbar-container {
            transition: transform 0.3s ease-in-out;
            color: white;
            width: 280px;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
        }

        .main-content {
            margin-left: 280px;
            transition: margin-left 0.3s ease-in-out;
        }

        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .mobile-overlay.visible {
            display: block;
        }


        .sub-menu-item.active a {
            background-color: rgba(255, 255, 255, 0.2);
        }


        /* Mobile styles */
        @media (max-width: 768px) {
            .mobile-header {
                display: flex; /* Show on mobile */
            }

            .navbar-container {
                transform: translateX(-100%);
                top: 70px; /* Account for mobile header */
                height: calc(100vh - 70px);
            }

            .navbar-container.visible {
                transform: translateX(0);
            }

            .navbar-root {
                max-height: calc(100vh - 150px);
            }

            .main-content {
                margin-left: 0;
                padding-top: 100px !important; /* Account for mobile header */
            }
        }
    </style>
</head>
<body>
<div id="app">
    <!-- Mobile Header (only visible on mobile) -->
    <div class="mobile-header">
        <a href="{{ route('admin') }}"><img style="height: 40px" src="{{ asset('img/logo/logo_white.webp') }}" alt="Logo Noordenlicht"></a>
        <button class="hamburger-btn" id="hamburger-toggle">
            <span class="material-symbols-rounded">menu</span>
        </button>
    </div>

    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobile-overlay"></div>

    <div class="d-flex flex-row">
        <div class="navbar-container d-flex flex-column justify-content-between p-2" id="sidebar">
            <div class="sticky-top">
                <a href="{{ route('admin') }}">
                <img style="width: clamp(50px, 100%, 250px)" src="{{ asset('img/logo/logo_white.webp') }}"
                     alt="Logo Noordenlicht" class="d-none d-md-block">
                </a>

                <div id="navbar-root" class="no-scrolbar navbar-root">
                    @php
                        $currentPath = request()->path();
                        $menuItems = [
                            [
                                'name' => 'Dashboard',
                                'uri' => '/dashboard',
                                'fontSize' => '18px',
                                'icon-name' => 'dashboard',
                                'sub-pages' => [],
                            ],
                            [
                                'name' => 'Content',
                                'fontSize' => '18px',
                                'icon-name' => 'newspaper',
                                'sub-pages' => [
                                    [
                                        'name' => 'Nieuwsbrieven',
                                        'uri' => '/dashboard/nieuws',
                                        'fontSize' => '14px',
                                    ],
                                    [
                                        'name' => 'Blog',
                                        'uri' => '/dashboard/nieuws',
                                        'fontSize' => '14px',
                                    ],
                                    [
                                        'name' => 'Nieuw blog',
                                        'uri' => '/dashboard/nieuws/nieuw-nieuwtje',
                                        'fontSize' => '14px',
                                    ],
                                ],
                            ],
                            [
                                'name' => 'Events',
                                'uri' => '/agenda/maand',
                                'fontSize' => '18px',
                                'icon-name' => 'calendar_month',
                                'sub-pages' => [
                                    [
                                        'name' => 'Overzicht',
                                        'uri' => '/dashboard/agenda/overzicht',
                                        'fontSize' => '14px',
                                    ],
                                    [
                                        'name' => 'Nieuw event',
                                        'uri' => '/dashboard/agenda/nieuw',
                                        'fontSize' => '14px',
                                    ],
                                ],
                            ],
                            [
                                'name' => 'Contact',
                                'uri' => '/dashboard/contact',
                                'fontSize' => '18px',
                                'icon-name' => 'call',
                                'sub-pages' => [],
                                'notificationsCount' => Contact::where('done', false)->count(),
                            ],
                            [
                                'name' => 'Accommodaties',
                                'uri' => '',
                                'fontSize' => '18px',
                                'icon-name' => 'door_open',

                                'sub-pages' => [
                                    [
                                        'name' => 'Boekingen',
                                        'uri' => '',
                                        'fontSize' => '14px',
                                    ],
                                    [
                                        'name' => 'Beheer',
                                        'uri' => '/dashboard/accommodaties',
                                        'fontSize' => '14px',
                                    ],
                                ],
                            ],
                            [
                                'name' => 'Webshop',
                                'uri' => '',
                                'fontSize' => '18px',
                                'icon-name' => 'shop',
                                'sub-pages' => [
                                    [
                                        'name' => 'Producten',
                                        'uri' => '/dashboard/products',
                                        'fontSize' => '14px',
                                    ],
                                    [
                                        'name' => 'Bestellingen',
                                        'uri' => '',
                                        'fontSize' => '14px',
                                    ],
                                    [
                                        'name' => 'Verzendingen',
                                        'uri' => '',
                                        'fontSize' => '14px',
                                    ],
                                    [
                                        'name' => 'Betalingen',
                                        'uri' => '',
                                        'fontSize' => '14px',
                                    ],
                                    [
                                        'name' => 'Coupons',
                                        'uri' => '',
                                        'fontSize' => '14px',
                                    ],
                                    [
                                        'name' => 'Statistieken',
                                        'uri' => '',
                                        'fontSize' => '14px',
                                    ],
                                    [
                                        'name' => 'Instellingen',
                                        'uri' => '',
                                        'fontSize' => '14px',
                                    ],
                                ],
                            ],
                            [
                                'name' => 'Administratie',
                                'fontSize' => '18px',
                                'icon-name' => 'manage_accounts',
                                'sub-pages' => [
                                    [
                                        'name' => 'Gebruikers',
                                        'uri' => '/dashboard/account-beheer',
                                        'fontSize' => '14px',
                                    ],
                                    [
                                        'name' => 'Nieuw account',
                                        'uri' => '/dashboard/maak-account',
                                        'fontSize' => '14px',
                                    ],
                                    [
                                        'name' => 'Rollen beheer',
                                        'uri' => '/dashboard/rol-beheer',
                                        'fontSize' => '14px',
                                    ],
                                    [
                                        'name' => 'Logs',
                                        'uri' => '/dashboard/logs',
                                        'fontSize' => '14px',
                                    ],
                                ],
                            ],
                        ];
                    @endphp

                    @foreach($menuItems as $index => $item)
                        @php
                            $isMainItemActive = ($item['uri'] ?? '') === '/' . $currentPath;
                            $hasActiveSubItem = false;
                            if (!empty($item['sub-pages'])) {
                                foreach ($item['sub-pages'] as $subPage) {
                                    if (($subPage['uri'] ?? '') === '/' . $currentPath) {
                                        $hasActiveSubItem = true;
                                        break;
                                    }
                                }
                            }
                            $shouldShowSubmenu = $hasActiveSubItem;
                        @endphp

                        <div class="main-menu-item {{ $isMainItemActive ? 'active' : '' }}" data-has-submenu="{{ !empty($item['sub-pages']) ? 'true' : 'false' }}" data-uri="{{ $item['uri'] ?? '' }}">
                            <div class="menu-content" style="font-size: {{ $item['fontSize'] }};">
                                <span class="material-symbols-rounded">{{ $item['icon-name'] }}</span>
                                <span class="menu-text">{{ $item['name'] }}</span>
                                @if(!empty($item['notificationsCount']) && $item['notificationsCount'] > 0)
                                    <span class="notification-badge">{{ $item['notificationsCount'] }}</span>
                                @endif
                            </div>
                            @if(!empty($item['sub-pages']))
                                <span class="material-symbols-rounded arrow-icon {{ $shouldShowSubmenu ? 'rotated' : '' }}">arrow_drop_down</span>
                            @endif
                        </div>

                        @if(!empty($item['sub-pages']))
                            <ul class="sub-menu {{ $shouldShowSubmenu ? 'show' : '' }}">
                                @foreach($item['sub-pages'] as $subPage)
                                    @php
                                        $isSubItemActive = ($subPage['uri'] ?? '') === '/' . $currentPath;
                                    @endphp
                                    <li class="sub-menu-item {{ $isSubItemActive ? 'active' : '' }}">
                                        <a href="{{ $subPage['uri'] }}" style="font-size: {{ $subPage['fontSize'] }};">{{ $subPage['name'] }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    @endforeach
                </div>
            </div>
            <div>
                <div class="main-menu-item">
                    <div onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="menu-content" style="font-size: 18px;"><span
                            class="material-symbols-rounded">logout</span><span class="menu-text">Uitloggen</span>
                    </div>
                </div>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </div>
        <main class="py-4 p-4 w-100 main-content" style="max-width: 100vw; overflow: hidden">
            @yield('content')
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const hamburgerToggle = document.getElementById('hamburger-toggle');
            const sidebar = document.getElementById('sidebar');
            const mobileOverlay = document.getElementById('mobile-overlay');
            const mainMenuItems = document.querySelectorAll('.main-menu-item');

            // Mobile menu toggle functionality
            function toggleMobileMenu() {
                sidebar.classList.toggle('visible');
                mobileOverlay.classList.toggle('visible');

                // Update hamburger icon
                const icon = hamburgerToggle.querySelector('.material-symbols-rounded');
                if (sidebar.classList.contains('visible')) {
                    icon.textContent = 'close';
                } else {
                    icon.textContent = 'menu';
                }
            }

            hamburgerToggle.addEventListener('click', toggleMobileMenu);
            mobileOverlay.addEventListener('click', toggleMobileMenu);

            // Close mobile menu when clicking on menu items
            function closeMobileMenuOnItemClick() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('visible');
                    mobileOverlay.classList.remove('visible');
                    const icon = hamburgerToggle.querySelector('.material-symbols-rounded');
                    icon.textContent = 'menu';
                }
            }

            // Handle menu item clicks
            mainMenuItems.forEach(function(menuItem) {
                menuItem.addEventListener('click', function(e) {
                    const hasSubmenu = this.getAttribute('data-has-submenu') === 'true';
                    const uri = this.getAttribute('data-uri');

                    if (hasSubmenu) {
                        e.preventDefault();
                        const submenu = this.nextElementSibling;
                        const arrowIcon = this.querySelector('.arrow-icon');

                        if (submenu && submenu.classList.contains('sub-menu')) {
                            submenu.classList.toggle('show');
                            if (arrowIcon) {
                                arrowIcon.classList.toggle('rotated');
                            }
                        }
                    } else if (uri) {
                        closeMobileMenuOnItemClick();
                        window.location.href = uri;
                    }
                });
            });

            // Handle sub-menu item clicks (close mobile menu)
            const subMenuItems = document.querySelectorAll('.sub-menu-item a');
            subMenuItems.forEach(function(link) {
                link.addEventListener('click', closeMobileMenuOnItemClick);
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    // Reset mobile menu state on desktop
                    sidebar.classList.remove('visible');
                    mobileOverlay.classList.remove('visible');
                    const icon = hamburgerToggle.querySelector('.material-symbols-rounded');
                    icon.textContent = 'menu';
                }
            });
        });
    </script>
</div>
</body>
</html>
