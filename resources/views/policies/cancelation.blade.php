@extends('layouts.app')

@section('content')

    <div class="rounded-bottom-5 bg-white d-flex flex-column container-block"
         style="position: relative; margin-top: -25px; background-position: center !important; background-image: url('{{ asset('img/logo/doodles/Blad.webp') }}'); background-repeat: repeat;">

        <div class="container py-5">
            <div class="container justify-content-center align-items-center d-flex flex-column gap-5">
                <div style="backdrop-filter: blur(2px);">
                    <h1 class="text-center fw-bold display-5" style="text-wrap: auto; hyphens: auto">Annuleringsvoorwaarden</h1>

                    <div>
                        <ul>
                            <li>Je aanvraag wordt na het versturen, in behandeling genomen. Je ontvangt zo spoedig mogelijk een reactie.</li>
                            <li>Wanneer de aanvraag is goedgekeurd, kun je via de de boeking knop jouw accommodatie en data reserveren en via Ideal bevestigen.</li>
                            <li>Na het ontvangen van de betaling is je boeking definitief en wordt jouw activiteit, indien gewenst, gepubliceerd op onze website en nieuwsbrief.</li>
                            <li>Annuleren is mogelijk onder de volgende voorwaarden:</li>
                            <li>Reserveringen dienen uiterlijk 3 maanden voor desbetreffende datum omgezet te worden naar een boeking. Reserveringen zijn pas definitief, na het betalen van de boeking. Wanneer iemand op hetzelfde moment een boeking wilt doen, wordt er contact met je opgenomen en dien je je reservering binnen 24 uur om te zetten naar een boeking. Zodat er geen boekingen mis worden gelopen door NoordenLicht. De voorkeur geniet direct te boeken.</li>
                        </ul>
                        <ul>
                            <li>Boekingen tot 8 uur:
                                <ul>
                                    <li>Tot 72 uur (3 dagen vooraf) is een geboekte tijdvak te verplaatsen naar een ander moment.</li>
                                    <li>Annuleren voor 4 weken van boekingsdatum is gratis.</li>
                                    <li>Annuleren tussen 1-4 weken voor boeking wordt 50% van de gereserveerde accommodatie(s) in rekening gebracht.</li>
                                    <li>Bij annulering binnen 1 week wordt 100% van de geboekte accommodatie(s) in rekening gebracht.</li>
                                    <li>Bij annulering binnen 72 uur (3 dagen) voor boeking wordt 100% van de gereserveerde accommodatie(s) en toevoegingen als overnachtingen, maaltijden en activiteiten in rekening gebracht.</li>
                                    <li>Je mag de reservering overdragen aan iemand anders, deze persoon dient zich wel zelf aan te melden via het boekingsformulier of deze activiteit past binnen het concept van NoordenLicht.</li>
                                </ul>
                            </li>
                        </ul>
                        <ul>
                            <li>Boekingen langer dan 8 uur:
                                <ul>
                                    <li>Annuleren tot 12 weken voor boekingsdatum is gratis.</li>
                                    <li>Annuleren tussen 8-12 weken voor boekingsdatum wordt 25% van de geboekte accommodatie(s) in rekening gebracht.Annuleren tussen 4-8 weken voor boekingsdatum wordt 50% van de geboekte accommodatie(s) in rekening gebracht.</li>
                                    <li>Annuleren tussen 2-4 weken voor boekingsdatum wordt 75% van de geboekte accommodatie(s) in rekening gebracht.Bij annulering binnen 2 weken wordt 100% van de geboekte accommodatie(s) in rekening gebracht.</li>
                                    <li>Bij annulering binnen 72 uur (3 dagen) voor boeking wordt 100% van de gereserveerde accommodatie(s) en toevoegingen als overnachtingen, maaltijden en overige activiteiten in rekening gebracht.</li>
                                    <li>Je mag de reservering overdragen aan iemand anders, deze persoon dient zich wel zelf aan te melden via het boekingsformulier of deze activiteit past binnen het concept van NoordenLicht.</li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
