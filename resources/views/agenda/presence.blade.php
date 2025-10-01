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

                                @if(isset($lesson))
                                    <input type="hidden" name="lessonId" value="{{$lesson->id}}">
                                @endif

                                @if($all_roles->count() > 0)
                            </div>


                            <div class="input-group">
                                <label for="role" class="input-group-text" id="basic-addon1">
                                    <span class="material-symbols-rounded">account_circle</span></label>
                                <select id="role" name="role" class="form-select"
                                        aria-label="Rol" aria-describedby="basic-addon1" onchange="this.form.submit();">
                                    <option value="none">Filter</option>

                                    @foreach($all_roles as $role)
                                        <option
                                            @if($selected_role === $role->role) selected @endif>{{ $role->role }}</option>
                                    @endforeach
                                </select>
                                @endif
                                                                <a @if($users->count() > 0) id="submit-export"
                                                                   @endif class="input-group-text @if($users->count() < 1)disabled @endif"
                                                                   style="text-decoration: none; cursor: pointer">
                                                                    <span class="material-symbols-rounded">ios_share</span></a>
                            </div>
                        </div>
                    </div>
                </form>

                <form id="export" action="{{ route('agenda.presence.export') }}" method="POST">
                    @csrf
                    <input type="hidden" name="users" value="{{ json_encode($users->map(function ($user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'last_name' => $user->last_name,
        'email' => $user->email,
        'presence' => $user->presence['status'] ?? 'null',
        'date' => $user->presence['date'] ?? null,
    ];
})) }}">

                    <input type="hidden" name="activity_name" value="{{ $activity->title }}">
                </form>

                <script>
                    let button = document.getElementById('submit-export')
                    button.addEventListener('click', submitExportForm)

                    function submitExportForm() {
                        let form = document.getElementById('export');
                        let formData = new FormData(form);

                        // Using Fetch API to handle form submission
                        fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
                            }
                        })
                            .then(response => response.blob()) // Convert response to a blob
                            .then(blob => {
                                let url = window.URL.createObjectURL(blob);
                                let a = document.createElement('a');
                                a.href = url;
                                a.download = getDownloadFileName(); // Set the file name
                                document.body.appendChild(a);
                                a.click();
                                a.remove();
                            })
                            .catch(error => console.error('Error:', error));
                    }

                    function getDownloadFileName() {
                        // Format the current date as dd-mm-yyyy
                        const now = new Date();
                        const day = String(now.getDate()).padStart(2, '0');
                        const month = String(now.getMonth() + 1).padStart(2, '0'); // Months are 0-based
                        const year = now.getFullYear();
                        const formattedDate = `${day}-${month}-${year}`;

                        // Generate the file name
                        return `Aanwezigheid {{ $activity->title }} {{ date("d-m-Y", strtotime($activity->date_start)) }} [${formattedDate}].xlsx`;
                    }
                </script>

                @if(empty($search))
                    @php
                        // Filter users to get only those who are present
                        $presentUsers = $users->filter(function ($user) {
                            return $user->presence["status"] === 'present';
                        });

                        $absentUsers = $users->filter(function ($user) {
                            return $user->presence["status"] === 'absent';
                        });
                    @endphp

                    <p>

                        @if($presentUsers->count() === 1)
                            <span>Er is {{ $presentUsers->count() }} iemand aanwezig en</span>
                        @else
                            <span>Er zijn {{ $presentUsers->count() }} mensen aanwezig en</span>
                        @endif


                        @if($absentUsers->count() === 1)
                            <span>er is {{ $absentUsers->count() }} iemand afwezig.</span>
                        @else
                            <span>er zijn {{ $absentUsers->count() }} mensen afwezig.</span>
                        @endif

                    </p>
                @endif

                @if($users->count() > 0)
                    <div class="overflow-scroll no-scrolbar" style="max-width: 100vw">
                        <table class="table table-striped">
                            <thead class="thead-dark table-bordered table-hover">
                            <tr>
                                <th class="no-mobile" scope="col">Profielfoto</th>
                                <th scope="col">Naam</th>
                                <th scope="col">Aanwezig?</th>
                                @if($user &&
                                                        ($user->roles->contains('role', 'Dolfijnen Leiding') ||
                                                        $user->roles->contains('role', 'Zeeverkenners Leiding') ||
                                                        $user->roles->contains('role', 'Loodsen Stamoudste') ||
                                                        $user->roles->contains('role', 'Afterloodsen Organisator') ||
                                                        $user->roles->contains('role', 'Administratie') ||
                                                        $user->roles->contains('role', 'Bestuur') ||
                                                        $user->roles->contains('role', 'Praktijkbegeleider') ||
                                                        $user->roles->contains('role', 'Loodsen Mentor') ||
                                                        $user->roles->contains('role', 'Ouderraad')
                                                        ))
                                    <th scope="col">Datum</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>

                            @foreach ($users as $all_user)
                                <tr id="{{ $all_user->id }}" @if($all_user->not_invited) class="not-invited" @endif>
                                    <th class="no-mobile">
                                        @if($all_user->profile_picture)
                                            <img alt="profielfoto" class="profle-picture"
                                                 src="{{ asset('/profile_pictures/' .$all_user->profile_picture) }}">
                                        @else
                                            <img alt="profielfoto" class="profle-picture"
                                                 src="{{ asset('img/no_profile_picture.webp') }}">
                                        @endif
                                    </th>
                                    <th>{{ $all_user->name .' '. $all_user->infix.' '. $all_user->last_name }}</th>
                                    @if($all_user->presence["status"] === 'present')
                                        <th class="bg-success-subtle">
                                            Aangemeld
                                        </th>
                                    @endif
                                    @if($all_user->presence["status"] === 'absent')
                                        <th class="bg-danger-subtle">
                                            Afgemeld
                                        </th>
                                    @endif
                                    @if($all_user->presence["status"] === 'null')
                                        <th>
                                            Niets laten weten
                                        </th>
                                    @endif
                                    @if($user &&
                                     ($user->roles->contains('role', 'Dolfijnen Leiding') ||
                                     $user->roles->contains('role', 'Zeeverkenners Leiding') ||
                                     $user->roles->contains('role', 'Loodsen Stamoudste') ||
                                     $user->roles->contains('role', 'Afterloodsen Organisator') ||
                                     $user->roles->contains('role', 'Administratie') ||
                                     $user->roles->contains('role', 'Bestuur') ||
                                     $user->roles->contains('role', 'Praktijkbegeleider') ||
                                     $user->roles->contains('role', 'Loodsen Mentor') ||
                                     $user->roles->contains('role', 'Ouderraad')
                                     ))
                                        <th>
                                            <p>{{ $all_user->presence['date'] ? \Carbon\Carbon::parse($all_user->presence['date'])->format('d-m-Y H:i:s') : '-' }}</p>
                                        </th>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <span class="material-symbols-rounded me-2">person_off</span>Geen gebruikers gevonden...
                    </div>
                @endif

            </div>
        </div>
@endsection
