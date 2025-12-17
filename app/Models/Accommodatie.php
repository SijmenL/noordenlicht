<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accommodatie extends Model
{
    protected $fillable = [
        'name',
        'user_id',
        'type',
        'description',
        'image'
    ];

    public function images()
    {
        return $this->hasMany(AccommodatieImage::class, 'accommodatie_id');
    }

    public function icons()
    {
        return $this->hasMany(AccommodatieIcon::class, 'accommodatie_id');
    }

    /**
     * Defines the relationship to the prices via the pivot model.
     */
    public function prices()
    {
        return $this->hasMany(AccommodatiePrice::class, 'accommodatie_id');
    }
}
