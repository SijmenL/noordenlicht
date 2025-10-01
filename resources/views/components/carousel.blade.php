@props(['images' => []])

@php
    use Illuminate\Support\Str;
    $carouselId = 'roundedCarousel_' . Str::uuid();
@endphp

<div id="{{ $carouselId }}" class="carousel slide mx-auto" data-bs-ride="carousel" style="width: 100% !important; aspect-ratio: 1/1; border-radius: 50%; overflow: hidden;">
    <div class="carousel-inner w-100 h-100">
        @foreach ($images as $index => $image)
            <div class="carousel-item h-100 w-100 {{ $index === 0 ? 'active' : '' }}">
                <img src="{{ asset($image) }}" class="d-block w-100 h-100 object-fit-cover overflow-hidden zoomable-image" alt="Carousel Image {{ $index + 1 }}">
            </div>
        @endforeach
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>
