@extends("layouts.dashboard")

@section("content")
    <div class="container">
        @php
            use Carbon\Carbon;

            $timezone = new DateTimeZone('Europe/Amsterdam');
            $date = new DateTime('now', $timezone);
            $formattedDate = $date->format('d-m-Y H:i:s');

            $hour = Carbon::now()->hour;

            if ($hour >= 6 && $hour < 12) {
                $greeting = 'Goeiemorgen';
            } elseif ($hour >= 12 && $hour < 18) {
                $greeting = 'Goeiemiddag';
            } elseif ($hour >= 18 && $hour < 24) {
                $greeting = 'Goedenavond';
            } else {
                $greeting = 'Goedenacht';
            }
        @endphp
        <h1>{{$greeting}}, {{ explode(' ', \App\Models\User::find(Auth::id())->name)[0] }}</h1>
        <p>{{ $formattedDate }}</p>

        @if($totalNotifications > 0)
        <div class="alert alert-warning w-100" role="alert">
            <h4 class="alert-heading">Je hebt nog {{$totalNotifications}} @if($totalNotifications !== 1) openstaande taken @else openstaande taak @endif</h4>
            <ul>
                @if($contact > 0)
                    <li>{{$contact}} @if($contact !== 1) ongelezen contactformulieren @else ongelezen contactformulier @endif</li>
                @endif
                @if($orders > 0)
                    <li>{{$orders}} @if($orders !== 1) openstaande bestellingen @else openstaande bestelling @endif</li>
                @endif
                    @if($signup > 0)
                        <li>{{$signup}} @if($signup !== 1) nieuwe aanmeldingen om een accommodate te huren @else nieuwe aanmelding om een accommodate te huren @endif</li>
                    @endif
            </ul>
        </div>
        @endif
    </div>
@endsection
