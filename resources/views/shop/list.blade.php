@extends('layouts.app')

@section('content')
    {{-- Category Helper Logic for Display --}}
    @php
        $categoryNames = [
            '0' => 'Alles',
            '2' => 'Overnachtingen',
        ];

        // Define active category for styling
        $activeCategory = request('type');
    @endphp

    <div class="rounded-bottom-5 bg-white d-flex flex-column container-block"
         style="position: relative; z-index: 9; margin-top: -25px; background-position: center !important; background-image: url('{{ asset('img/logo/doodles/Wolf.webp') }}'); background-repeat: no-repeat; background-size: cover; min-height: 100vh;">

        <div class="container py-5">
            <div class="container justify-content-center align-items-center d-flex flex-column gap-5">
                <div style="backdrop-filter: blur(2px);">
                    <h1 class="text-center fw-bold display-5">Webshop</h1>
                    <h2 class="text-center fs-4 text-secondary fw-light">Ontdek onze tickets en overnachtingen</h2>
                </div>


                {{-- New Filter & Search Toolbar --}}
                <div class="w-100" style="max-width: 900px;">
                    <form id="auto-submit" method="GET" class="d-flex flex-column gap-4">

                        {{-- 1. Prominent Search Bar --}}
                        <div class="position-relative">
                            <span class="material-symbols-rounded position-absolute text-muted"
                                  style="top: 50%; left: 20px; transform: translateY(-50%); font-size: 24px;">search</span>
                            <input id="search" name="search" type="text"
                                   class="form-control form-control-lg border-0 shadow-sm ps-5 py-3 rounded-pill custom-search-input"
                                   placeholder="Waar ben je naar op zoek?"
                                   value="{{ $search ?? '' }}"
                                   autocomplete="off"
                                   onchange="this.form.submit();">
                        </div>

                        {{-- 2. Category Tabs / Pills (Dedicated Pages Feel) --}}
                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                                                        {{-- Dynamic Categories --}}
                            @foreach($categoryNames as $key => $name)
                                <button type="submit" name="type" value="{{ $key }}"
                                        class="category-pill {{ $activeCategory == $key ? 'active' : '' }}">
                                    {{ $name }}
                                </button>
                            @endforeach
                        </div>
                    </form>
                </div>

                {{-- Products Grid --}}
                @if($products->count() > 0)
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 w-100">
                        @foreach ($products as $product)
                            <a href="{{ route('shop.details', $product->id) }}" class="col text-decoration-none">
                                {{-- Custom Product Tile (No Bootstrap Card Classes) --}}
                                <div class="shop-tile h-100 d-flex flex-column bg-white overflow-hidden position-relative">

                                    {{-- Image Section --}}
                                    <div class="tile-image-wrapper position-relative">
                                        <img src="{{ asset('/files/products/images/'.$product->image) }}"
                                             class="w-100 h-100 object-fit-cover tile-img"
                                             alt="{{ $product->name }}">

                                        {{-- Type Badge --}}
                                        <span class="tile-badge">
                                            {{ $categoryNames[$product->type] ?? 'Product' }}
                                        </span>
                                    </div>

                                    {{-- Content Section --}}
                                    <div class="p-4 d-flex flex-column flex-grow-1">
                                        <h3 class="h5 fw-bold text-dark mb-2">{{ $product->name }}</h3>

                                        <div class="text-muted small mb-4 flex-grow-1 tile-description">
                                            {{ \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode($product->description))), 100, '...') }}
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top border-light">
                                            <div class="price-tag">
                                                € {{ number_format($product->calculated_price, 2, ',', '.') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-5 d-flex justify-content-center">
                        {{ $products->appends(request()->input())->links() }}
                    </div>
                @else
                    {{-- Empty State --}}
                    <div class="text-center py-5">
                        <div class="empty-state-icon mb-3">
                            <span class="material-symbols-rounded">search_off</span>
                        </div>
                        <h3 class="fw-bold text-secondary">Geen resultaten</h3>
                        <p class="text-muted">We konden geen producten vinden in deze categorie.</p>
                        <a href="{{ url()->current() }}" class="btn btn-outline-dark rounded-pill px-4 mt-2">Alles tonen</a>
                    </div>
                @endif
            </div>
        </div>

        <style>
            /* Reset & Custom Styles */
            .custom-search-input:focus {
                box-shadow: 0 0 0 4px rgba(0,0,0,0.05) !important;
            }

            /* Category Pills */
            .category-pill {
                border: 2px solid #e9ecef;
                background: white;
                color: #6c757d;
                padding: 0.5rem 1.5rem;
                border-radius: 50px;
                font-weight: 600;
                font-size: 0.95rem;
                cursor: pointer;
                transition: all 0.2s ease;
                text-decoration: none;
                display: inline-block;
            }
            .category-pill:hover {
                background: #f8f9fa;
                border-color: #dee2e6;
                color: #212529;
            }
            .category-pill.active {
                background: #212529;
                border-color: #212529;
                color: white;
            }

            /* Shop Tile (Replacing Card) */
            .shop-tile {
                border-radius: 16px;
                box-shadow: 0 2px 15px rgba(0,0,0,0.04);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                border: 1px solid rgba(0,0,0,0.02);
            }
            .shop-tile:hover {
                transform: translateY(-5px);
                box-shadow: 0 15px 30px rgba(0,0,0,0.08);
            }

            /* Image Area */
            .tile-image-wrapper {
                height: 240px; /* Fixed height for consistency */
                overflow: hidden;
            }
            .tile-img {
                transition: transform 0.5s ease;
            }
            .shop-tile:hover .tile-img {
                transform: scale(1.05);
            }
            .tile-badge {
                position: absolute;
                top: 15px;
                right: 15px;
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(4px);
                padding: 6px 12px;
                border-radius: 20px;
                font-size: 0.75rem;
                font-weight: 600;
                color: #212529;
                box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            }

            /* Text & Layout */
            .tile-description {
                line-height: 1.6;
                opacity: 0.8;
            }
            .price-tag {
                font-size: 1.25rem;
                font-weight: 700;
                color: #198754; /* Success green or brand color */
            }

            /* Action Buttons */
            .action-btn {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                border: none;
                cursor: pointer;
                transition: all 0.2s;
            }
            .btn-view {
                background: #f1f3f5;
                color: #495057;
            }
            .btn-view:hover {
                background: #e9ecef;
                color: #212529;
            }
            .btn-cart {
                background: #212529;
                color: white;
            }
            .btn-cart:hover {
                background: #000;
                transform: scale(1.05);
            }

            /* Empty State */
            .empty-state-icon {
                width: 80px;
                height: 80px;
                background: #f8f9fa;
                border-radius: 50%;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: #adb5bd;
            }
            .empty-state-icon span {
                font-size: 40px;
            }
        </style>
    </div>
@endsection
