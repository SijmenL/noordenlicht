<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityException;
use App\Models\ActivityFormResponses;
use App\Models\Booking;
use App\Models\Log;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Models\UserNotificationSettings;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SettingsController extends Controller
{

    public function account()
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();


        return view('settings.settings', ['user' => $user, 'roles' => $roles]);
    }

    public function editAccount()
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();


        return view('settings.account', ['user' => $user, 'roles' => $roles]);
    }

    public function editAccountSave(Request $request)
    {
        $request->validate([
            'name' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users,email,' . Auth::user()->id,
            'sex' => 'string|nullable',
            'birth_date' => 'date|nullable',
            'street' => 'string',
            'postal_code' => 'string',
            'city' => 'string',
            'phone' => 'string',
            'website' => 'string',
            'praktijknaam' => 'string',
            'profile_picture' => 'nullable|mimes:jpeg,png,jpg,gif,webp|max:6000',
        ]);

        $user = Auth::user();

        if (isset($request->profile_picture)) {
            // Process and save the uploaded image
            $newPictureName = time() . '-' . $request->name . '.' . $request->profile_picture->extension();
            $destinationPath = 'profile_pictures';
            $request->profile_picture->move($destinationPath, $newPictureName);
            $user->profile_picture = $newPictureName;
        }

        // Update user fields
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->sex = $request->input('sex');
        $user->birth_date = $request->input('birth_date');
        $user->street = $request->input('street');
        $user->postal_code = $request->input('postal_code');
        $user->city = $request->input('city');
        $user->phone = $request->input('phone');
        $user->website = $request->input('website');
        $user->praktijknaam = $request->input('praktijknaam');

        $user->save();

        $log = new Log();
        $log->createLog(auth()->user()->id, 2, 'Edit account', 'Settings', '', '');


        return redirect()->route('user.settings.account.edit')->with('success', 'Account succesvol bijgewerkt');
    }


    public function changePassword()
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();


        return view('settings.change_password', ['user' => $user, 'roles' => $roles]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|confirmed|min:8',
        ]);


        if (!Hash::check($request->old_password, auth()->user()->password)) {
            return redirect()->back()->withErrors(['old_password' => 'Wachtwoord klopt niet']);
        }


        User::whereId(auth()->user()->id)->update([
            'password' => Hash::make($request->new_password)
        ]);

        $log = new Log();
        $log->createLog(auth()->user()->id, 2, 'Edit password', 'Settings', '', '');


        return back()->with("success", "Wachtwoord succesvol opgeslagen!");
    }


    public function notifications()
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        $notification_settings = UserNotificationSettings::all()->where('user_id', $user->id)->pluck('on_status', 'type')->toArray();

        return view('settings.edit_notifications', compact('user', 'roles', 'notification_settings'));
    }

    public function notificationsSave(Request $request)
    {
        $request->validate([
            'hidden_form_field' => 'required',
        ]);

        // check if the notification setting already exists
        $notification_setting = UserNotificationSettings::where('user_id', Auth::user()->id)->where('type', $request->hidden_form_field)->first();

        // if exists, delete
        if ($notification_setting) {
            $notification_setting->delete();
            return redirect()->route('settings.edit-notifications')->with("success", "Notificatie instelling succesvol opgeslagen.");
        }

        UserNotificationSettings::create([
            'user_id' => Auth::user()->id,
            'type' => $request->hidden_form_field,
            'on_status' => 0
        ]);

        return redirect()->route('user.settings.edit-notifications')->with("success", "Notificatie instelling succesvol opgeslagen.");
    }

    public function showOrders(Request $request)
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        $query = Order::with('user')->where('user_id', $user->id)->orderBy('updated_at', 'desc');

        if ($request->has('status') && $request->status != '' && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('settings.orders.list', [
            'orders' => $orders,
            'status' => $request->status
        ]);
    }

    public function orderDetails($id)
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        // Added 'tickets.activity' to eager load data for matching
        $order = Order::with(['items.product', 'tickets.activity', 'user'])->findOrFail($id);

        if ($order->user_id !== $user->id) {
            return redirect()->back()->withErrors(["Je mag deze order niet bekijken"]);
        }

        // Fetch all form responses associated with this order
        $formResponses = ActivityFormResponses::with(['formElement', 'activity'])
            ->where('order_id', $order->id)
            ->get();

        return view('settings.orders.details', compact('order', 'formResponses'));
    }

    public function bookingDetails(Request $request, $id)
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        $booking = Booking::with(['accommodatie', 'user', 'order.items'])->findOrFail($id);

        if ($booking->user_id !== $user->id) {
            return redirect()->back()->withErrors(["Je mag deze boeking niet bekijken"]);
        }

        // Fetch form responses associated with the linked order (for supplements etc)
        $formResponses = collect();
        if ($booking->order_id) {
            $formResponses = ActivityFormResponses::with(['formElement'])
                ->where('order_id', $booking->order_id)
                ->get();
        }

        $supplements = Product::where('type', '0')->with('formElements')->get();

        return view('settings.bookings.details', compact('booking', 'formResponses', 'supplements'));
    }
}

