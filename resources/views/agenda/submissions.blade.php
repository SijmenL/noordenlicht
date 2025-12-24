@extends('layouts.contact')

@include('partials.editor')

@vite('resources/js/calendar.js')

@php
    use Carbon\Carbon;
    Carbon::setLocale('nl');
@endphp

@section('content')
    <div class="p-2 h-100 overflow-y-scroll">


        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="d-flex flex-row-responsive align-items-center gap-5" style="width: 100%">
            <div class="" style="width: 100%;">


                <form id="auto-submit" method="GET" class="user-select-forum-submit">
                    <div class="d-flex">
                        <div class="d-flex flex-row-responsive gap-2 align-items-center mb-3 w-100"
                             style="justify-items: stretch">
                            <div class="input-group">
                                <label for="search" class="input-group-text" id="basic-addon1">
                                    <span class="material-symbols-rounded">search</span></label>
                                <input id="search" name="search" type="text" class="form-control"
                                       placeholder="Zoeken op naam, email, adres etc."
                                       aria-label="Zoeken" aria-describedby="basic-addon1" value="{{ $search }}"
                                       onchange="this.form.submit();">

                                {{--                                <a @if($formSubmissions->count() > 0) id="submit-export"--}}
                                {{--                                   @endif class="input-group-text @if($formSubmissions->count() < 1)disabled @endif"--}}
                                {{--                                   style="text-decoration: none; cursor: pointer">--}}
                                {{--                                    <span class="material-symbols-rounded">ios_share</span></a>--}}
                            </div>
                        </div>
                    </div>
                </form>

                @if(empty($search))
                    @if($formSubmissions->count() > 0)
                        @if($formSubmissions->count() === 1)
                            <p>Er heeft zich {{ $formSubmissions->count() }} iemand ingeschreven.</p>
                        @else
                            <p>Er hebben zich {{ $formSubmissions->count() }} mensen ingeschreven.</p>
                        @endif
                    @endif
                @endif

                @if($formSubmissions->count() > 0)
                    <table class="table table-striped">
                        <thead class="thead-dark table-bordered table-hover">
                        <tr>
                            <th scope="col">Datum</th>
                            <th scope="col">Gegevens</th>
                        </tr>
                        </thead>
                        <tbody>

                        {{--                                                {{ dd($formSubmissions) }}--}}
                        @foreach($formSubmissions as $submissionGroup)
                            <tr>
                                <th>{{ $submissionGroup[0]->created_at }}</th>
                                <th>
                                    <table class="table table-bordered">
                                        <tbody>
                                        @php
                                            $groupedEntries = $submissionGroup->groupBy('activity_form_element_id');
                                        @endphp

                                        @foreach($groupedEntries as $formElementId => $entries)
                                            <tr>
                                                <th style="width: 10vw; background: rgba(255,255,255,0.3)">
                                                    {{ $entries[0]->formElement->label }}
                                                </th>
                                                <th style="background: rgba(255,255,255,0.3)">
                                                    {{ $entries->pluck('response')->join(', ') }}
                                                </th>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </th>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <span class="material-symbols-rounded me-2">cancel</span>Geen inschrijvingen gevonden...
                    </div>
                @endif

            </div>
        </div>
@endsection
