@php
    use Illuminate\Support\Str;
    $carouselId = 'roundedCarousel_' . Str::uuid();
@endphp

<div class="d-flex flex-column align-items-center gap-3">

    <!-- Main Carousel -->
    <div id="{{ $carouselId }}" class="carousel slide mx-auto"
         data-bs-ride="carousel"
         style="width: 100% !important; aspect-ratio: 1/1; border-radius: 50%; overflow: hidden;">
        <div class="carousel-inner w-100 h-100">
            @foreach ($images as $index => $image)
                <div class="carousel-item h-100 w-100 {{ $index === 0 ? 'active' : '' }}">
                    <img src="{{ asset($image) }}"
                         class="d-block w-100 h-100 object-fit-cover overflow-hidden zoomable-image"
                         alt="Carousel Image {{ $index + 1 }}">
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

    <!-- Thumbnails -->
    <div class="w-100 d-flex overflow-x-scroll no-scrolbar justify-content-center">
        <div class="d-flex gap-2  px-2"
             style="max-width: 100%; scrollbar-width: thin; -webkit-overflow-scrolling: touch;">
            @foreach ($images as $index => $image)
                <button type="button"
                        data-bs-target="#{{ $carouselId }}"
                        data-bs-slide-to="{{ $index }}"
                        class="thumbnail-btn {{ $index === 0 ? 'active' : '' }}"
                        aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                        aria-label="Slide {{ $index + 1 }}"
                        style="border: none; background: none; padding: 0;">
                    <img src="{{ asset($image) }}"
                         class="thumbnail-image"
                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 50%;">
                </button>
            @endforeach
        </div>
    </div>
</div>

<style>
    .thumbnail-image {
        border:3px solid transparent; !important;
        transition: 0.2s;
    }

    .thumbnail-btn.active .thumbnail-image {
        border: 3px solid white !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const carouselEl = document.getElementById("{{ $carouselId }}");
        const thumbButtons = carouselEl.parentElement.querySelectorAll('.thumbnail-btn');

        carouselEl.addEventListener('slid.bs.carousel', function (event) {
            // remove active from all thumbnails
            thumbButtons.forEach(btn => btn.classList.remove('active'));
            // add active to the matching one
            const index = event.to;
            if (thumbButtons[index]) {
                thumbButtons[index].classList.add('active');
            }
        });
    });
</script>
