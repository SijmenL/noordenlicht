<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accommodatie extends Model
{
    protected $fillable = [
        'name',
        'user_id',
        'type',
        'description',
        'image',
        'min_check_in',
        'max_check_in',
        'min_duration_minutes'
    ];

    public function images()
    {
        return $this->hasMany(AccommodatieImage::class, 'accommodatie_id');
    }

    public function icons()
    {
        return $this->hasMany(AccommodatieIcon::class, 'accommodatie_id');
    }

    public function prices()
    {
        return $this->hasMany(AccommodatiePrice::class, 'accommodatie_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
