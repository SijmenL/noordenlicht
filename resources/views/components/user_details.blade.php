@props([
    'hide',
    'user'
])

<div class="overflow-scroll no-scrolbar" style="max-width: 100vw">
    <table class="table table-striped">
        <tbody>
        @if(!in_array('name', $hide))
            <tr>
                <th>Volledige naam</th>
                <td>{{ $user->name }}</td>
            </tr>
        @endif

        @if($user->profile_picture && !in_array('profile_picture', $hide))
            <tr>
                <th>Profielfoto</th>
                <td>
                    <img alt="profielfoto" class="w-25 zoomable-image"
                         src="{{ asset('/profile_pictures/' . $user->profile_picture) }}">
                <th>
            </tr>
        @endif

        @if(!in_array('parktijknaam', $hide))
            <tr>
                <th>Praktijknaam</th>
                <td>{{ $user->praktijknaam }}</td>
            </tr>
        @endif

        @if(!in_array('parktijknaam', $hide))
            <tr>
                <th>Website</th>
                <td><a href="{{ $user->website }}" target="_blank">{{ $user->website }}</a></td>
            </tr>
        @endif

        @if(!in_array('sex', $hide))
            @if(!isset($user->birth_date))
                <tr>
                    <th>Geboortedatum</th>
                    <td>
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <span class="material-symbols-rounded me-2">frame_person_off</span>Geen geslacht gevonden...
                        </div>
                    </td>
                </tr>
            @else
            <tr>
                <th>Geslacht</th>
                <td>{{ $user->sex }}</td>
            </tr>
            @endif
        @endif

        @if(!in_array('birth_date', $hide))
            @if(!isset($user->birth_date))
                <tr>
                    <th>Geboortedatum</th>
                    <td>
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <span class="material-symbols-rounded me-2">grid_off</span>Geen geboortedatum gevonden...
                        </div>
                    </td>
                </tr>
            @else
                <tr>
                    <th>Geboortedatum</th>
                    <td>{{ \Carbon\Carbon::parse($user->birth_date)->format('d-m-Y') }},
                        @php
                            $birthDate = \Carbon\Carbon::parse($user->birth_date);
                            $age = $birthDate->age;

                            $nextBirthday = $birthDate->copy()->year(now()->year);
                            if ($nextBirthday->isPast()) {
                                $nextBirthday->addYear();
                            }
                            $daysUntilBirthday = now()->diffInDays($nextBirthday);
                        @endphp

                        {{ $age }} jaar
                        ({{ $daysUntilBirthday }} dagen tot volgende verjaardag)
                    </td>
                </tr>

            @endif
        @endif

        @if(!in_array('adress', $hide))
            @if(!isset($user->street) && !isset($user->postal_code) && !isset($user->city))
                <tr>
                    <th>Adres</th>
                    <td>
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <span class="material-symbols-rounded me-2">location_off</span>Geen adresgegevens
                            gevonden...
                        </div>
                    </td>
                </tr>
            @else
                <tr>
                    <th>Adres</th>
                    <td>
                        {{ collect([$user->street, $user->postal_code, $user->city])
                            ->filter()
                        ->implode(', ')
                        }}
                    </td>

                </tr>
            @endif
        @endif

        @if(!in_array('phone', $hide))
            @if(!isset($user->phone))
                <tr>
                    <th>Telefoonnummer</th>
                    <td>
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <span class="material-symbols-rounded me-2">phone_disabled</span>Geen telefoonnummer
                            gevonden...
                        </div>
                    </td>
                </tr>
            @else
                <tr>
                    <th>Telefoonnummer</th>
                    <td><a href="tel:{{ $user->phone }}">{{ $user->phone }}</a></td>
                </tr>
            @endif
        @endif

        @if(!in_array('email', $hide))
            @if(!isset($user->email))
                <tr>
                    <th>E-mail</th>
                    <td>
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <span class="material-symbols-rounded me-2">mail_off</span>Geen e-mailadres gevonden...
                        </div>
                    </td>
                </tr>
            @else
                <tr>
                    <th>E-mail</th>
                    <td><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></td>
                </tr>
            @endif
        @endif


        @if(!in_array('roles', $hide))
            @if($user->roles->count() > 0)
            <tr>
                <th>Rollen</th>
                <td>
                    @foreach ($user->roles as $role)
                        <span title="{{ $role->description }}"
                              class="badge rounded-pill text-bg-primary text-white fs-6 p-2">{{ $role->role }}</span>
                    @endforeach
                </td>
            </tr>
            @else
                <tr>
                    <th>Rollen</th>
                    <td>
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <span class="material-symbols-rounded me-2">remove_moderator</span>Geen rollen gevonden...
                        </div>
                    </td>
                </tr>
            @endif
        @endif

        @if(!in_array('updated_at', $hide))
            <tr>
                <th>Aangepast op</th>
                <td>{{ \Carbon\Carbon::parse($user->updated_at)->format('d-m-Y H:i:s') }}</td>
            </tr>
        @endif

        @if(!in_array('created_at', $hide))
            <tr>
                <th>Aangemaakt op</th>
                <td>{{ \Carbon\Carbon::parse($user->created_at)->format('d-m-Y H:i:s') }}</td>
            </tr>
        @endif
        </tbody>
    </table>
</div>
