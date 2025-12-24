<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotificationSettings extends Model
{
    protected $table = 'user_notification_settings';

    protected $fillable = [
        'user_id',
        'type',
        'on_status'
    ];


}
