@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class=" border-0 rounded-5 overflow-hidden text-center">
                    <div class="-body p-5">
                        <div class="mb-4">
                            <span class="material-symbols-rounded text-primary" style="font-size: 64px;">payment</span>
                        </div>

                        <h2 class="fw-bold mb-3">Bestelling Aangemaakt</h2>
                        <p class="text-muted mb-4">
                            Bedankt voor je bestelling!<br>
                            Bestelnummer: <strong>{{ $order->order_number }}</strong>
                        </p>

                        <div class="alert alert-light border mb-4">
                            <p class="mb-0">Totaal te voldoen: <strong>€ {{ number_format($order->total_amount, 2, ',', '.') }}</strong></p>
                        </div>

                        <div id="redirect-message" class="mb-4">
                            <div class="spinner-border text-primary mb-3" role="status"></div>
                            <p class="text-muted">Je wordt doorgestuurd naar de betaalpagina...</p>
                        </div>

                        <div class="d-grid gap-3">
                            <a href="{{ $checkoutUrl }}" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm">
                                Nu Betalen
                            </a>
                            <a href="{{ route('shop') }}" class="btn btn-link text-muted text-decoration-none">
                                Annuleren en terug naar de winkel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($autoRedirect)
        <script>
            // Auto-redirect to Mollie after a brief delay
            setTimeout(function() {
                window.location.href = "{{ $checkoutUrl }}";
            }, 1000);
        </script>
    @else
        <script>
            // If not auto-redirecting (e.g. user pressed back), hide the spinner text
            document.getElementById('redirect-message').style.display = 'none';
        </script>
    @endif
@endsection
