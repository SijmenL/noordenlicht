<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presence extends Model
{
    protected $fillable = ['user_id', 'activity_id', 'presence', 'date_occurrence'];

    // A response belongs to an event
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function activityUsers()
    {
        return $this->hasMany(ActivityUser::class);
    }

}
