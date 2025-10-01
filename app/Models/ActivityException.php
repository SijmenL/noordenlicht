<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityException extends Model
{
    protected $fillable = ['activity_id', 'date'];

    // A form element belongs to an event
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

}
