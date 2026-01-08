@extends('layouts.app')

@section('content')
    <div class="container mt-5 mb-5 col-md-11">
        <h1>Notificaties</h1>


        @if(Session::has('error'))
            <div class="alert alert-danger" role="alert">
                {{ session('error') }}
            </div>
        @endif
        @if(Session::has('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif
        @php

        @endphp

        <style>
            .form-switch {
                padding-left: 0rem;
                /* padding-right: 2rem; */
            }

            td {
                max-width: 250px;
            }
        </style>


        <div class="bg-white border w-100 p-4 rounded mt-3">
            <div>
                <h2 class="d-flex flex-row gap-1 mb-3 align-items-center"><span
                        class="material-symbols-rounded">person</span>Account</h2>
                <form method="POST" action="{{ route('user.settings.edit-notifications.store') }}"
                      enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="hidden_form_field">
                    <table class="table">
                        <thead>
                        <tr>
                            <th scope="col" class="bg-white">Notificatie</th>
                            <th scope="col" class="text-center bg-white">App</th>
                            <th scope="col" class="text-center bg-white">Mail</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="bg-white">Je account gegevens zijn aangepast.</td>
                            <td class="bg-white">
                                <div class="form-check form-switch d-flex align-items-end justify-content-center">
                                    <input class="form-check-input fs-4 m-0" type="checkbox" role="switch"
                                           name='app_account_change' id='app_account_change'
                                           @if( !isset($notification_settings['app_account_change'])) checked @endif>
                                </div>
                            </td>
                            <td class="bg-white">
                                <div class="form-check form-switch d-flex align-items-end justify-content-center">
                                    <input class="form-check-input fs-4 m-0" type="checkbox" role="switch"
                                           name='mail_account_change' id='mail_account_change'
                                           @if( !isset($notification_settings['mail_account_change'])) checked @endif>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td class="bg-white">Je wachtwoord is aangepast.</td>
                            <td class="bg-white">
                                <div class="form-check form-switch d-flex align-items-end justify-content-center">
                                    <input class="form-check-input fs-4 m-0" type="checkbox" role="switch"
                                           name='app_password_change' id='app_password_change'
                                           @if( !isset($notification_settings['app_password_change'])) checked @endif>
                                </div>
                            </td>
                            <td class="bg-white">
                                <div class="form-check form-switch d-flex align-items-end justify-content-center">
                                    <input class="form-check-input fs-4 m-0" type="checkbox" role="switch"
                                           name='mail_password_change' id='mail_password_change'
                                           @if( !isset($notification_settings['mail_password_change'])) checked @endif>
                                </div>
                            </td>
                        </tr>


                        </tbody>
                    </table>
                </form>

            </div>

        </div>

        @if ($errors->any())
            <div class="text-danger">
                <p>Er is iets misgegaan...</p>
            </div>
        @endif
    </div>

    <script>
        document.querySelectorAll('.form-check-input').forEach(item => {
            item.addEventListener('change', async event => {
                // Get the closest form
                let form = item.closest('form');
                // Get id from the checkbox
                let id = item.getAttribute('id');
                // Set the hidden input field's value to the checkbox id
                form.querySelector('input[name="hidden_form_field"]').value = id;

                // Create FormData object from the form
                let formData = new FormData(form);

                // Send the form data with fetch
                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest', // Indicate AJAX request
                            'X-CSRF-Token': form.querySelector('input[name="_token"]').value // CSRF token
                        }
                    });

                    if (response.ok) {
                        console.log("Form submitted successfully");
                        // Optional: Add any success handling here
                    } else {
                        console.error("Error submitting form");
                        // Optional: Handle error feedback here
                    }
                } catch (error) {
                    console.error("Fetch error: ", error);
                }
            });
        });
    </script>


@endsection
