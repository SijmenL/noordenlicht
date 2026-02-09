<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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
        'min_duration_minutes',
        'order',
        'color'
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

    // Base - Discounts + VAT
    public function getCalculatedPriceAttribute()
    {
        $allPrices = $this->relationLoaded('prices')
            ? $this->prices->map(fn($pp) => $pp->price)
            : $this->prices()->with('price')->get()->map(fn($pp) => $pp->price);

        $basePrices = $allPrices->where('type', 0);
        $percentageAdditions = $allPrices->where('type', 1);
        $fixedDiscounts = $allPrices->where('type', 2);
        $percentageDiscounts = $allPrices->where('type', 4);

        $totalBasePrice = $basePrices->sum('amount');

        // Check for User Discount (Booking Discount)
        $user = Auth::user();
        $userDiscountPercent = 0;

        // Verify if 'Ledenkorting' is already injected to prevent double counting
        $hasInjectedDiscount = $percentageDiscounts->contains(function ($price) {
            return $price->name === 'Ledenkorting';
        });

        if (!$hasInjectedDiscount && $user && $user->booking_discount > 0) {
            $userDiscountPercent = $user->booking_discount;
        }

        // 1. Discounts
        $discountAmount = 0;
        foreach ($percentageDiscounts as $percentage) {
            $discountAmount += $totalBasePrice * ($percentage->amount / 100);
        }

        if ($userDiscountPercent > 0) {
            $discountAmount += $totalBasePrice * ($userDiscountPercent / 100);
        }

        $discountAmount += $fixedDiscounts->sum('amount');

        $taxableAmount = max($totalBasePrice - $discountAmount, 0);

        // 2. VAT
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
        $percentageAdditions = $allPrices->where('type', 1);
        $fixedDiscounts = $allPrices->where('type', 2);
        $extraCosts = $allPrices->where('type', 3);
        $percentageDiscounts = $allPrices->where('type', 4);

        $totalBasePrice = $basePrices->sum('amount');

        // Check for User Discount (Booking Discount)
        $user = Auth::user();
        $userDiscountPercent = 0;

        // Verify if 'Ledenkorting' is already injected to prevent double counting
        $hasInjectedDiscount = $percentageDiscounts->contains(function ($price) {
            return $price->name === 'Ledenkorting';
        });

        if (!$hasInjectedDiscount && $user && $user->booking_discount > 0) {
            $userDiscountPercent = $user->booking_discount;
        }

        // 1. Discounts
        $discountAmount = 0;
        foreach ($percentageDiscounts as $percentage) {
            $discountAmount += $totalBasePrice * ($percentage->amount / 100);
        }

        if ($userDiscountPercent > 0) {
            $discountAmount += $totalBasePrice * ($userDiscountPercent / 100);
        }

        $discountAmount += $fixedDiscounts->sum('amount');

        $taxableAmount = max($totalBasePrice - $discountAmount, 0);

        // 2. VAT
        $vatAmount = 0;
        foreach ($percentageAdditions as $percentage) {
            $vatAmount += $taxableAmount * ($percentage->amount / 100);
        }

        // 3. Extras
        $extrasAmount = $extraCosts->sum('amount');

        return max($taxableAmount + $vatAmount + $extrasAmount, 0);
    }
}
