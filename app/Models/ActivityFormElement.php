<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityFormElement extends Model
{

    protected $fillable = ['activity_id', 'label', 'type', 'is_required', 'option_value'];

    // A form element belongs to an event
    public function event()
    {
        return $this->belongsTo(Activity::class);
    }

    // A form element can have many responses
    public function activityFormResponses()
    {
        return $this->hasMany(ActivityFormResponses::class);
    }
}
