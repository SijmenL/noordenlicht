@section('footer')
    <div class="bg-primary shadow-sm p-5 mb-0 text-white rounded-top-5" style="position: relative; z-index: 3; margin-top: -25px;">
        <div class="container d-flex flex-row-responsive gap-5 align-items-center">

            <div class="col-md-8">
                <div class="d-flex flex-column gap-1" style="line-height: 10px">
                    <p>&copy;NoordenLicht 2021 - {{ date("Y") }}</p>
                    <p>Versie {{ env("VERSION", "1.0") }}</p>
                    <br>
                    <a class="text-white" href="https://maps.app.goo.gl/WFHn4RoEyxuUf1ndA" target="_blank">
                    <p>NoordenLicht</p>
                    <p>Tramstraat 54A</p>
                    <p>7848 BL Schoonoord</p>
                    </a>
                    <br>
                    <a class="text-white" href="tel:06-31223045"><p>06-31223045</p></a>
                    <a class="text-white" href="mailto:info@noordenlicht.nu"><p>info@noordenlicht.nu</p></a>
                    <br>
                    <a class="text-white" href="https://www.noordenlicht.nu/"><p>www.NoordenLicht.nu</p></a>
                    <br>
                    <p>KVK nummer: 20115070</p>
                    <p>BTW nummer: NL001807677B14</p>
                    <p>IBAN-nummer: NL80TRIO0320726770 t.n.v. Cha’kwaini</p>
                </div>
            </div>

            <div class="col-md-4 text-center mb-3">
                <a class="text-white" href="/">
                <img class=" img-fluid" style="max-width: 350px; width: 75%" alt="logo" src="{{ asset('img/logo/logo_white.webp') }}">
                </a>
            </div>

        </div>

        <div class="text-center mt-5">
            <p>Website met liefde gemaakt door <a class="text-white" href="https://www.sijmenlokers.nl/" target="_blank">Sijmen Lokers</a>.</p>
        </div>
    </div>
@endsection
