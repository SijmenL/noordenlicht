@extends('layouts.app')

@section('content')
    <div class="container py-5" style="min-height: 80vh;">
        <h1 class="mb-4">Winkelmandje</h1>

        @if(Session::has('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(count($cartItems) > 0)
            <div class="card shadow-sm border-0 rounded-4" style="max-width: unset">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                            <tr>
                                <th scope="col" style="width: 40%">Product</th>
                                <th scope="col" style="width: 20%">Prijs</th>
                                <th scope="col" style="width: 20%">Aantal</th>
                                <th scope="col" style="width: 20%" class="text-end">Totaal</th>
                                <th scope="col"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($cartItems as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="{{ asset('files/products/images/'.$item['product']->image) }}"
                                                 alt="{{ $item['product']->name }}"
                                                 class="rounded-3"
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                            <div>
                                                <h5 class="mb-0">{{ $item['product']->name }}</h5>
                                                <small class="text-muted">{{ $item['product']->type }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>€ {{ number_format($item['unit_price'], 2, ',', '.') }}</td>
                                    <td>
                                        <form action="{{ route('cart.update', $item['product']->id) }}" method="POST" class="d-flex align-items-center gap-2">
                                            @csrf
                                            <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" class="form-control form-control-sm" style="width: 70px;">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Update aantal">
                                                <span class="material-symbols-rounded" style="font-size: 16px;">refresh</span>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-end fw-bold">€ {{ number_format($item['total_price'], 2, ',', '.') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('cart.remove', $item['product']->id) }}" class="btn btn-sm btn-outline-danger">
                                            <span class="material-symbols-rounded">delete</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="3" class="text-end fw-bold fs-5 pt-4">Totaal te betalen:</td>
                                <td class="text-end fw-bold fs-5 pt-4 text-success">€ {{ number_format($totalPrice, 2, ',', '.') }}</td>
                                <td></td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('shop') }}" class="btn btn-outline-secondary">Verder winkelen</a>
                        <a href="#" class="btn btn-primary btn-lg rounded-pill px-5">Afrekenen</a>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <span class="material-symbols-rounded display-1 text-muted mb-3">shopping_cart_off</span>
                <h3>Je winkelmandje is leeg.</h3>
                <a href="{{ route('shop') }}" class="btn btn-primary mt-3">Bekijk onze producten</a>
            </div>
        @endif
    </div>
@endsection
