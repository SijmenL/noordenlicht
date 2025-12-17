<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccommodatiePrice extends Model
{
    protected $fillable = [
        'accommodatie_id',
        'price_id',
    ];

    public function accommodatie()
    {
        return $this->belongsTo(Accommodatie::class);
    }

    /**
     * Define a relationship to the price.
     */
    public function price()
    {
        // Corrected the foreign key from 'competence_id' to 'price_id'
        return $this->belongsTo(Price::class, 'price_id');
    }
}
