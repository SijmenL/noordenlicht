@extends('layouts.dashboard')

@section('content')
    <div class="container col-md-11">
        <h1>Producten</h1>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item" aria-current="page"><a href="{{route('admin')}}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Producten</li>
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

        <form id="auto-submit" method="GET">
            <div class="d-flex">
                <div class="d-flex flex-row-responsive justify-content-between gap-2 mb-3 w-100">
                    <div class="input-group">
                        <label for="search" class="input-group-text" id="basic-addon1">
                            <span class="material-symbols-rounded">search</span></label>
                        <input id="search" name="search" type="text" class="form-control"
                               placeholder="Zoeken op naam, type"
                               aria-label="Zoeken" aria-describedby="basic-addon1" value="{{ $search }}" onchange="this.form.submit();">
                    </div>
                    <a href="{{ route('admin.products.new') }}" class="btn btn-outline-dark make-role-button">Maak een nieuw product</a>
                </div>
            </div>
        </form>

        @if($products->count() > 0)
            <div class="" style="max-width: 100vw">
                <table class="table table-striped">
                    <thead class="thead-dark table-bordered table-hover">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Naam</th>
                        <th scope="col">Type</th>
                        <th scope="col">Opties</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach ($products as $product)
                        <tr id="{{ $product->id }}">
                            <th>{{ $product->id }}</th>
                            <th>{{ $product->name }}</th>
                            <th>{{ $product->type }}</th>
                            <th>
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Opties
                                    </button>
                                    <ul class="dropdown-menu">
                                        {{-- Link to public shop details for preview --}}
                                        <li>
                                            <a href="{{ route('shop.details', ['id' => $product->id]) }}" target="_blank" class="dropdown-item">
                                                Bekijk in shop
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('admin.products.edit', ['id' => $product->id]) }}" class="dropdown-item">
                                                Bewerk
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a href="{{ route('admin.products.delete', ['id' => $product->id]) }}"
                                               class="dropdown-item text-danger"
                                               onclick="return confirm('Weet je zeker dat je dit product wilt verwijderen?');">
                                                Verwijderen
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </th>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{ $products->links() }}
        @else
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <span class="material-symbols-rounded me-2">info</span>Geen producten gevonden...
            </div>
        @endif
    </div>
@endsection
