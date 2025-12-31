<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityFormElement extends Model
{
    // Added 'product_id' and 'location'
    protected $fillable = [
        'activity_id',
        'product_id',
        'location',
        'label',
        'type',
        'is_required',
        'option_value'
    ];

    public function event()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    // New relationship for Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function activityFormResponses()
    {
        return $this->hasMany(ActivityFormResponses::class);
    }
}
