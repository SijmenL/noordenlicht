<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Product extends Model
{
    protected $fillable = ['name', 'type', 'description', 'image', 'price', 'user_id'];

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function prices()
    {
        return $this->hasMany(ProductPrice::class);
    }

    // Base - Discounts + VAT (No Extras)
    public function getCalculatedPriceAttribute()
    {
        $allPrices = $this->relationLoaded('prices')
            ? $this->prices->map(fn($pp) => $pp->price)
            : $this->prices()->with('price')->get()->map(fn($pp) => $pp->price);

        $basePrices = $allPrices->where('type', 0);
        $percentageAdditions = $allPrices->where('type', 1); // VAT
        $fixedDiscounts = $allPrices->where('type', 2);      // Fixed Discounts
        $percentageDiscounts = $allPrices->where('type', 4); // % Discounts

        $totalBasePrice = $basePrices->sum('amount');

        // Check for User Discount (Shop Discount)
        $user = Auth::user();
        $userDiscountPercent = 0;

        // Verify if 'Ledenkorting' is already injected to prevent double counting
        // This handles cases where the Controller has already injected the discount for display
        $hasInjectedDiscount = $percentageDiscounts->contains(function ($price) {
            return $price->name === 'Ledenkorting';
        });

        if (!$hasInjectedDiscount && $user && $user->shop_discount > 0) {
            $userDiscountPercent = $user->shop_discount;
        }

        // 1. Discounts
        $discountAmount = 0;
        foreach ($percentageDiscounts as $percentage) {
            $discountAmount += $totalBasePrice * ($percentage->amount / 100);
        }

        // Apply User Discount if active
        if ($userDiscountPercent > 0) {
            $discountAmount += $totalBasePrice * ($userDiscountPercent / 100);
        }

        $discountAmount += $fixedDiscounts->sum('amount');

        $taxableAmount = max($totalBasePrice - $discountAmount, 0);

        // 2. VAT (Additions) on Taxable Amount
        $vatAmount = 0;
        foreach ($percentageAdditions as $percentage) {
            $vatAmount += $taxableAmount * ($percentage->amount / 100);
        }

        return max($taxableAmount + $vatAmount, 0);
    }

    // Base - Discounts + VAT + Extras
    public function getCalculatedFullPriceAttribute()
    {
        $allPrices = $this->relationLoaded('prices')
            ? $this->prices->map(fn($pp) => $pp->price)
            : $this->prices()->with('price')->get()->map(fn($pp) => $pp->price);

        $basePrices = $allPrices->where('type', 0);
        $percentageAdditions = $allPrices->where('type', 1); // VAT
        $fixedDiscounts = $allPrices->where('type', 2);      // Fixed Discounts
        $extraCosts = $allPrices->where('type', 3);          // Extras
        $percentageDiscounts = $allPrices->where('type', 4); // % Discounts

        $totalBasePrice = $basePrices->sum('amount');

        // Check for User Discount (Shop Discount)
        $user = Auth::user();
        $userDiscountPercent = 0;

        // Verify if 'Ledenkorting' is already injected to prevent double counting
        $hasInjectedDiscount = $percentageDiscounts->contains(function ($price) {
            return $price->name === 'Ledenkorting';
        });

        if (!$hasInjectedDiscount && $user && $user->shop_discount > 0) {
            $userDiscountPercent = $user->shop_discount;
        }

        // 1. Discounts
        $discountAmount = 0;
        foreach ($percentageDiscounts as $percentage) {
            $discountAmount += $totalBasePrice * ($percentage->amount / 100);
        }

        // Apply User Discount if active
        if ($userDiscountPercent > 0) {
            $discountAmount += $totalBasePrice * ($userDiscountPercent / 100);
        }

        $discountAmount += $fixedDiscounts->sum('amount');

        $taxableAmount = max($totalBasePrice - $discountAmount, 0);

        // 2. VAT (Additions) on Taxable Amount
        $vatAmount = 0;
        foreach ($percentageAdditions as $percentage) {
            $vatAmount += $taxableAmount * ($percentage->amount / 100);
        }

        // 3. Extras
        $extrasAmount = $extraCosts->sum('amount');

        return max($taxableAmount + $vatAmount + $extrasAmount, 0);
    }

    public function formElements()
    {
        return $this->hasMany(ActivityFormElement::class);
    }

    public function activityFormElements()
    {
        return $this->hasMany(ActivityFormElement::class, 'product_id');
    }
}
