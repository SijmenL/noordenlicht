@extends('layouts.app')

@section('content')
    {{-- Category Helper Logic for Display --}}
    @php
        $categoryNames = [
            '0' => 'Supplementen bij accommodatie',
            '1' => 'Evenement ticket',
            '2' => 'Overnachting',
        ];
    @endphp

    <div class="rounded-bottom-5 bg-white d-flex flex-column container-block"
         style="position: relative; z-index: 9; margin-top: -25px; background-position: center !important; background-image: url('{{ asset('img/logo/doodles/Wolf.webp') }}'); background-repeat: no-repeat; background-size: cover; min-height: 100vh;">

        <div class="container py-5">
            <div class="container justify-content-center align-items-center d-flex flex-column gap-5">
                <div style="backdrop-filter: blur(2px);">
                    <h1 class="text-center">Webshop</h1>
                    <h2 class="text-center">Op deze pagina zie je alles wat we verkopen, zowel tickets als supplementen bij accommodates.</h2>
                </div>


                {{-- Filter & Search Toolbar --}}
            <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden" style="max-width: unset">
                <div class="card-body p-2 bg-white">
                    <form id="auto-submit" method="GET" class="row g-2 align-items-center">

                        {{-- Search Input --}}
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-0 ps-3">
                                    <span class="material-symbols-rounded text-primary">search</span>
                                </span>
                                <input id="search" name="search" type="text"
                                       class="form-control border-0 shadow-none py-2"
                                       placeholder="Zoeken op productnaam..."
                                       value="{{ $search ?? '' }}"
                                       onchange="this.form.submit();">
                            </div>
                        </div>

                        {{-- Category Select --}}
                        <div class="col-md-4">
                            <div class="border-start ps-md-2">
                                <select name="type" class="form-select border-0 shadow-none fw-medium py-2 text-secondary cursor-pointer" onchange="this.form.submit()">
                                    <option value="" {{ request('type') == null ? 'selected' : '' }}>Alle categorieën</option>
                                    <option {{ request('type') == "0" ? 'selected' : '' }} value="0">Supplementen bij accommodatie</option>
                                    <option {{ request('type') == "1" ? 'selected' : '' }} value="1">Evenement ticket</option>
                                    <option {{ request('type') == "2" ? 'selected' : '' }} value="2">Overnachting</option>
                                </select>
                            </div>
                        </div>

                    </form>
                </div>
            </div>

            {{-- Products Grid --}}
            @if($products->count() > 0)
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    @foreach ($products as $product)
                        <div class="col">
                            <div class="card h-100 border-0 shadow-hover rounded-4 overflow-hidden product-card bg-white">

                                {{-- Image Wrapper --}}
                                <div class="position-relative overflow-hidden" style="padding-top: 100%;">
                                    <img src="{{ asset('/files/products/images/'.$product->image) }}"
                                         class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover transition-transform"
                                         alt="{{ $product->name }}">

                                    {{-- Category Badge --}}
                                    <div class="position-absolute top-0 end-0 p-3">
                                        <span class="badge bg-white text-dark shadow-sm px-3 py-2 rounded-pill fw-normal category-badge">
                                            {{ $categoryNames[$product->type] ?? 'Overige' }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Card Body --}}
                                <div class="card-body d-flex flex-column p-4">
                                    <h5 class="card-title fw-bold text-dark mb-2">{{ $product->name }}</h5>

                                    <div class="card-text text-muted small mb-4 flex-grow-1" style="line-height: 1.6;">
                                        {{ \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode($product->description))), 120, '...') }}
                                    </div>

                                    <div class="d-flex justify-content-between align-items-end mt-auto pt-3 border-top">
                                        <div>
                                            <small class="text-uppercase text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Prijs</small>
                                            <div class="h4 fw-bold text-success mb-0">€ {{ number_format($product->calculated_price, 2, ',', '.') }}</div>
                                        </div>

                                        <div class="d-flex gap-2">
                                            <a href="{{ route('shop.details', $product->id) }}"
                                               class="btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center transition-btn"
                                               style="width: 42px; height: 42px;"
                                               data-bs-toggle="tooltip" title="Details">
                                                <span class="material-symbols-rounded">visibility</span>
                                            </a>

                                            <form action="{{ route('cart.add', $product->id) }}" method="POST">
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-primary rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm transition-btn"
                                                        style="width: 42px; height: 42px;"
                                                        data-bs-toggle="tooltip" title="Toevoegen">
                                                    <span class="material-symbols-rounded">shopping_cart</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-5 d-flex justify-content-center">
                    {{ $products->links() }}
                </div>
            @else
                {{-- Empty State --}}
                <div class="text-center py-5">
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 100px; height: 100px;">
                        <span class="material-symbols-rounded text-muted" style="font-size: 40px;">search_off</span>
                    </div>
                    <h3 class="fw-bold text-secondary">Geen producten gevonden</h3>
                    <p class="text-muted">Probeer een andere zoekterm of categorie.</p>
                    <a href="{{ url()->current() }}" class="btn btn-outline-primary rounded-pill px-4 mt-2">Filters wissen</a>
                </div>
            @endif
        </div>

        <style>
            .cursor-pointer {
                cursor: pointer;
            }
            .product-card {
                transition: all 0.3s ease;
            }
            .product-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
            }
            .product-card img {
                transition: transform 0.6s ease;
            }
            .product-card:hover img {
                transform: scale(1.05);
            }
            .transition-btn {
                transition: all 0.2s ease;
            }
            .transition-btn:hover {
                transform: scale(1.1);
            }
            .category-badge {
                font-size: 0.75rem;
                letter-spacing: 0.3px;
            }
            /* Styling specific to dropdown to make it look cleaner */
            select.form-select:focus {
                border-color: transparent;
                box-shadow: none;
            }
        </style>
    </div>
@endsection
