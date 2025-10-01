<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityFormResponses extends Model
{

    protected $fillable = ['activity_id', 'activity_form_element_id', 'response', 'submitted_id'];

    // A response belongs to an event
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    // A response belongs to a form element
    public function activityFormElement()
    {
        return $this->belongsTo(ActivityFormElement::class);
    }

    public function formElement()
    {
        return $this->belongsTo(ActivityFormElement::class, 'activity_form_element_id');
    }

}
