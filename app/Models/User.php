<?php

namespace App\Models;

use App\Models\Role;
use App\Models\UserNotificationSettings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * @var \Illuminate\Support\HigherOrderCollectionProxy|mixed|true
     */

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    public function notificationSettings()
    {
        return $this->hasMany(UserNotificationSettings::class);
    }


// Custom method to get notification preference by type
    public function getNotificationSetting($type)
    {
        $setting = $this->notificationSettings()->where('type', $type)->first();
        return $setting ? false : true;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'sex',
        'name',
        'infix',
        'last_name',
        'birth_date',
        'speltak',
        'street',
        'postal_code',
        'city',
        'phone',
        'avg',
        'profile_picture',
        'member_date',
        'member_date_end',
        'dolfijnen_name',
        'calendar_token',
        'is_associate',
        'praktijknaam',
        'allow_booking',
        'activity_details',
        'shop_discount',
        'booking_discount',
        'notes',
        'website'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
