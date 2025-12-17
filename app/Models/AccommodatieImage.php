<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccommodatieImage extends Model
{
    protected $fillable = [
        'accommodatie_id',
        'temp_id',
        'image',
        'alt',
        'url',
    ];

}
