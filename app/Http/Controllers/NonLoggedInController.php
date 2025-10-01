<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityFormElement;
use App\Models\ActivityFormResponses;
use App\Models\Contact;
use App\Models\Log;
use App\Models\Notification;
use App\Models\User;
use App\Models\Role;
use App\Controllers\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;


class NonLoggedInController extends Controller
{
    public function __construct()
    {

    }

    public function contact() {

        return view('contact.contact');
    }

    public function contactSubmit(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone' => 'string|max:20|nullable',
            'message' => 'required|string'
        ]);

        $data = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'message' => $request->input('message'),
            'done' => false
        ];

        $contact = Contact::create($data);

        $log = new Log();
        $log->createLog(null, 2, 'Contact', 'Admin', $contact->id, 'Contact formulier opgeslagen');

        return view('contact.succes');
    }

    public function inschrijven() {

        return view('forms.inschrijven.inschrijven');
    }

    public function inschrijvenSubmit(Request $request)
    {
        $request->validate([
            'name' => 'string|max:255|required',
            'email' => 'string|email|max:255|unique:users,email|required',
            'sex' => 'string|required',
            'infix' => 'nullable|string',
            'last_name' => 'string|required',
            'birth_date' => 'date|required',
            'street' => 'string|required',
            'postal_code' => 'string|required',
            'city' => 'string|required',
            'phone' => 'string|required',
            'avg' => 'bool',
            'new_password' => 'required|confirmed|min:8',
            'voorwaarden' => 'required',
            'speltak' => 'required'
        ]);

        if (User::where('email', $request->email)->exists()) {
            return redirect()->back()->withErrors(['email' => 'Dit emailadres is al in gebruik.']);
        } else {
            if (!$request->input('avg')) {
                $avg = false;
            } else {
                $avg = true;
            }

            $data = [
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('new_password')),
                'sex' => $request->input('sex'),
                'name' => $request->input('name'),
                'infix' => $request->input('infix'),
                'last_name' => $request->input('last_name'),
                'birth_date' => $request->input('birth_date'),
                'street' => $request->input('street'),
                'postal_code' => $request->input('postal_code'),
                'city' => $request->input('city'),
                'phone' => $request->input('phone'),
                'avg' => $avg,
                'accepted' => false,
                'member_date' => Date::now(),
            ];

            $account = User::create($data);

            if ($request->input('speltak') === 'dolfijnen') {
                $role = Role::where('role', 'Dolfijn')->first();
                $account->roles()->syncWithoutDetaching([$role->id]);
            }
            if ($request->input('speltak') === 'zeeverkenners') {
                $role = Role::where('role', 'Zeeverkenner')->first();
                $account->roles()->syncWithoutDetaching([$role->id]);
            }
            if ($request->input('speltak') === 'loodsen') {
                $role = Role::where('role', 'Loods')->first();
                $account->roles()->syncWithoutDetaching([$role->id]);
            }
            if ($request->input('speltak') === 'afterloodsen') {
                $role = Role::where('role', 'Afterloods')->first();
                $account->roles()->syncWithoutDetaching([$role->id]);
            }

            $log = new Log();
            $log->createLog(null, 2, 'Inschrijven', 'Inschrijven', $account->name.' '.$account->infix.' '.$account->last_name, 'Nieuw account aangemaakt en rol toegevoegd');

            $notification = new Notification();
            $notification->sendNotification(null, [$account->id], 'Welkom bij de MHG! Je account is succesvol aangemaakt!', '', '','new_account', $account->id);

            $userIds = User::whereHas('roles', function ($query) {
                $query->whereIn('role', ['Administratie', 'Secretaris']);
            })->pluck('id');

            $notification = new Notification();
            $notification->sendNotification($account->id, $userIds, 'heeft zich ingeschreven', 'administratie/inschrijvingen/details/'.$account->id, null,'new_registration', $account->id);



            return view('forms.inschrijven.succes');

        }
    }

    public function handleActivityForm(Request $request, $id)
    {
        // Validate the request
        $validatedData = $request->validate([
            'form_elements' => 'required|array',
        ]);

        try {
            // Retrieve the activity to ensure it exists
            $activity = Activity::findOrFail($id);

            // Retrieve the highest current submitted_id for this activity
            $maxSubmittedId = ActivityFormResponses::where('activity_id', $activity->id)->max('submitted_id');
            $nextSubmittedId = $maxSubmittedId ? $maxSubmittedId + 1 : 1;


            // Loop through each form element and save the response
            foreach ($validatedData['form_elements'] as $formElementId => $response) {
                $formElement = ActivityFormElement::findOrFail($formElementId);

                // Handle checkbox inputs which are arrays
                if ($formElement->type === 'checkbox' && is_array($response)) {
                    foreach ($response as $checkboxValue) {
                        ActivityFormResponses::create([
                            'activity_id' => $activity->id,
                            'activity_form_element_id' => $formElementId,
                            'response' => $checkboxValue,
                            'submitted_id' => $nextSubmittedId,
                        ]);
                    }
                } else {
                    // For other input types, store the response directly
                    ActivityFormResponses::create([
                        'activity_id' => $activity->id,
                        'activity_form_element_id' => $formElementId,
                        'response' => $response,
                        'submitted_id' => $nextSubmittedId,
                    ]);
                }
            }


            $notification = new Notification();
            $notification->sendNotification(null, [$activity->user_id], 'Er heeft zich iemand ingeschreven op '.$activity->title, '/agenda/inschrijvingen/' .$activity->id, '', 'new_activity_registration', $activity->id);


            return redirect()->back()->with('success', 'Je inzending was succesvol!!');
        } catch (\Exception $e) {
            return redirect()->back()->with('success', 'Je inzending was succesvol!!');
        }
    }
}
