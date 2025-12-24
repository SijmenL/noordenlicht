<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityPrice extends Model
{
    protected $fillable = [
        'activity_id',
        'price_id',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function price()
    {
        return $this->belongsTo(Price::class, 'price_id');
    }
}
