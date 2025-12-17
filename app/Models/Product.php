<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
        'type', // e.g. "Physical Good", "Digital", etc.
        'description',
        'image'
    ];

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    /**
     * Defines the relationship to the prices via the pivot model.
     */
    public function prices()
    {
        return $this->hasMany(ProductPrice::class, 'product_id');
    }

    /**
     * Helper to calculate the current price based on the pricing system rules
     */
    public function getCalculatedPriceAttribute()
    {
        $allPrices = $this->prices->map(fn($p) => $p->price);

        $basePrices = $allPrices->where('type', 0);
        $percentageAdditions = $allPrices->where('type', 1);
        $fixedDiscounts = $allPrices->where('type', 2);
        //$extraCosts = $allPrices->where('type', 3); // Usually excluded from base unit price
        $percentageDiscounts = $allPrices->where('type', 4);

        $totalBasePrice = $basePrices->sum('amount');
        $currentPrice = $totalBasePrice;

        // 1. Apply percentage additions
        foreach ($percentageAdditions as $percentage) {
            $currentPrice += $totalBasePrice * ($percentage->amount / 100);
        }

        // 2. Apply percentage discounts
        foreach ($percentageDiscounts as $percentage) {
            $currentPrice -= $currentPrice * ($percentage->amount / 100);
        }

        // 3. Apply fixed amount discounts
        $currentPrice -= $fixedDiscounts->sum('amount');

        return max(0, $currentPrice);
    }
}
