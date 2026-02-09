<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/accommodaties/aanvraagformulier/success';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'sex' => 'string|nullable',
            'birth_date' => 'date|nullable',
            'street' => 'string',
            'postal_code' => 'string',
            'city' => 'string',
            'phone' => 'string',
            'website' => 'string',
            'praktijknaam' => 'string',
            'activity_details' => 'string'
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'sex' => $data['sex'],
            'birth_date' => $data['birth_date'],
            'street' => $data['street'],
            'postal_code' => $data['postal_code'],
            'city' => $data['city'],
            'phone' => $data['phone'],
            'website' => $data['website'],
            'praktijknaam' => $data['praktijknaam'],
            'activity_details' => $data['activity_details'],
        ]);
    }
}
