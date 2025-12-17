<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'type',
        'name',
    ];

    /**
     * The accommodations that have this price.
     */
    public function accommodatiePrices()
    {
        return $this->hasMany(AccommodatiePrice::class, 'price_id');
    }
}
