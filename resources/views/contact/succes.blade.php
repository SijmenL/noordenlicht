@extends('layouts.app')

@section('content')
    <div class="rounded-bottom-5 bg-white d-flex flex-column container-block"
         style="position: relative; margin-top: -25px; background-position: unset !important; background-image: url('{{ asset('img/logo/doodles/Blad Buizerd.webp') }}'); background-repeat: repeat;">
        <div class="container py-5">
            {{-- Header Sectie --}}
            <div class="container justify-content-center align-items-center d-flex flex-column gap-5 mb-5">
                <div style="backdrop-filter: blur(2px);">
                    <h1 class="text-center fw-bold display-5">Contact</h1>
                    <h2 class="text-center">Heeft u vragen over onze accommodaties of wilt u een afspraak maken? We
                        horen graag van u.</h2>
                </div>

                <div class="row g-5">
                    <div class="col-lg-7 order-lg-1">
                        <div class="pe-lg-5">
                            <h2 class="h3 fw-bold mb-3">Stuur ons een bericht</h2>
                            <p class="text-muted mb-4">Vul het formulier in en we nemen zo snel mogelijk contact met u
                                op.</p>

                            <div class="alert alert-primary">
                                Uw bericht is verzonden! We komen zo snel mogelijk bij u terug!
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5 order-lg-2">
                        <div class="p-4 bg-light rounded-4 border border-light-subtle h-100">
                            <h3 class="h4 fw-bold mb-4 text-primary">Gegevens</h3>

                            <ul class="list-unstyled d-flex flex-column gap-4 mb-5">
                                <li class="d-flex">
                                    <div class="ms-3">
                                        <h6 class="fw-bold mb-1">Adres</h6>
                                        <p class="mb-1 text-muted">Tramstraat 54A<br>7848 BL Schoonoord, Drenthe</p>
                                        <p>Bezoek enkel op afspraak.</p>
                                    </div>
                                </li>

                                <li class="d-flex">
                                    <div class="flex-shrink-0 mt-1">
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="fw-bold mb-1">E-mail</h6>
                                        <a href="mailto:info@noordenlicht.nu"
                                           class="text-decoration-none text-muted link-primary transition-colors">info@noordenlicht.nu</a>
                                    </div>
                                </li>

                                <li class="d-flex">
                                    <div class="flex-shrink-0 mt-1">
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="fw-bold mb-1">Telefoon</h6>
                                        <a href="tel:0631223045"
                                           class="text-decoration-none text-muted link-primary transition-colors">06-31223045</a>
                                    </div>
                                </li>
                            </ul>

                            <div
                                class="alert alert-info  bg-info-subtle text-info-emphasis d-flex align-items-start mb-4"
                                role="alert">
                                <i class="bi bi-signpost-split-fill me-3 mt-1 fs-5"></i>
                                <div>
                                    <strong>Routebeschrijving:</strong><br>
                                    NoordenLicht ligt achter de lintbebouwing. De locatie is bereikbaar via de
                                    verharde weg tussen nummer 54 en 56.
                                </>
                            </div>
                        </div>

                        <div class="rounded-3 overflow-hidden shadow-sm ratio ratio-16x9" style="min-height: 250px;">
                            <iframe
                                src="https://maps.google.com/maps?q=Tramstraat+54A,+7848+BL+Schoonoord&t=&z=15&ie=UTF8&iwloc=&output=embed"
                                style="border:0;"
                                allowfullscreen=""
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    </div>
                </div>


            </div>

            <div class="mt-5 bg-white  rounded-5 p-4 text-center">
                <p class="fw-bold">Mijn handelsnaam is Cha'kwaini,</p>
                <p class="fw-bold">De praktijk heet KrachtCirkel SchoonOord.</p>
                <p class="fw-bold"> De plek, het bos heet NoordenLicht.</p>
                <p>Cha'kwaini (spreek uit sja-kwa-nie) is een Indianen-naam. Het is een combinatie van 2 woorden.
                    Cha'kwaina wat "huilende wolf" betekend (heel toepasselijk bij mijn grote liefde voor de Alaskan
                    Malamute en mijn Krachtdier Mitok) en Kwani wat "Zij die Weet" betekend (ofwel de juffrouw van de
                    Indianen meisjes). Door deze 2 namen te combineren krijg je een unieke naam met 2 hele mooie
                    betekenissen, en zo werd Cha'kwaini gevonden.

                </p>
                <p class="fw-bold">Nuttah Nakota</p>

                <a href="https://www.krachtcirkelschoonoord.nl/" target="_blank" class="btn btn-primary btn-lg rounded-5" style="background-color: #5a3888 !important;">Naar KrachtCirkel ShoonOord</a>

                <ul class="list-unstyled mt-4">
                    <li>KVK nummer: 20115070</li>
                    <li>BTW nummer: NL001807677B14</li>
                    <li>IBAN-nummer: NL80TRIO0320726770 t.n.v. Cha’kwaini</li>
                </ul>

                <a href="https://www.noordenlicht.nu/">NoordenLicht.nu</a>
                <a href="https://www.krachtcirkelschoonoord.nl/">KrachtcirkelSchoonoord.nl</a>
            </div>

        </div>
    </div>


    <script>
        function handleButtonClick(button) {
            // Simple HTML5 validation check
            const form = button.closest('form');
            if (form.checkValidity()) {
                button.disabled = true;
                button.classList.add('disabled');
                button.querySelector('.button-text').style.display = 'none';
                button.querySelector('.loading-spinner').style.display = 'inline-block';
                button.querySelector('.loading-text').style.display = 'inline-block';
                form.submit();
            } else {
                // Trigger browser validation UI
                form.reportValidity();
            }
        }
    </script>

@endsection
