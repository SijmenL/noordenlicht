<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'type', 'description', 'image', 'price', 'user_id'];

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function prices()
    {
        // Assuming you are using the pivot table 'product_prices' created earlier
        // If you are using a polymorphic relationship, adjust accordingly.
        return $this->hasMany(ProductPrice::class);
    }

    /**
     * Centralized Price Calculation Accessor.
     * Use as: $product->calculated_price
     */
    public function getCalculatedPriceAttribute()
    {
        // Eager load prices if not already loaded to prevent N+1 queries
        $allPrices = $this->relationLoaded('prices')
            ? $this->prices->map(fn($pp) => $pp->price)
            : $this->prices()->with('price')->get()->map(fn($pp) => $pp->price);

        $basePrices = $allPrices->where('type', 0);
        $percentageAdditions = $allPrices->where('type', 1);
        $fixedDiscounts = $allPrices->where('type', 2);
        $percentageDiscounts = $allPrices->where('type', 4);

        $totalBasePrice = $basePrices->sum('amount');
        $preDiscountPrice = $totalBasePrice;

        // 1. Additions
        foreach ($percentageAdditions as $percentage) {
            $preDiscountPrice += $totalBasePrice * ($percentage->amount / 100);
        }

        $calculatedPrice = $preDiscountPrice;

        // 2. Percentage Discounts
        foreach ($percentageDiscounts as $percentage) {
            $calculatedPrice -= $preDiscountPrice * ($percentage->amount / 100);
        }

        // 3. Fixed Discounts
        $calculatedPrice -= $fixedDiscounts->sum('amount');

        return max($calculatedPrice, 0);
    }

}
