<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'quantity',
        'unit_price',
        'total_price',
        // New fields
        'unit_base_price',
        'unit_vat',
        'unit_discount_percentage',
        'unit_discount_amount',
        'unit_extra',
        'price_metadata'
    ];

    protected $casts = [
        'price_metadata' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
