<?php

namespace App\Http\Controllers;

use App\Models\Accommodatie;
use App\Models\Activity;
use App\Models\ActivityPrice;
use App\Models\Price;
use App\Models\AccommodatiePrice;
use App\Models\Product;
use App\Models\ProductPrice;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PriceController extends Controller
{
    // ... (linkPrice, unlinkPrice, pricePage, downloadPdf, getPriceListData unchanged)
    public function linkPrice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'model_id' => 'required|integer',
            'model_type' => 'required|string|in:accommodatie,product,activity',
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'type' => 'required|integer|in:0,1,2,3,4',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            $price = Price::create([
                'name' => $request->input('name'),
                'amount' => $request->input('amount'),
                'type' => $request->input('type'),
            ]);

            $priceLink = null;

            if ($request->input('model_type') === 'accommodatie') {
                $priceLink = AccommodatiePrice::create([
                    'accommodatie_id' => $request->input('model_id'),
                    'price_id' => $price->id,
                ]);
            } elseif ($request->input('model_type') === 'product') {
                $priceLink = ProductPrice::create([
                    'product_id' => $request->input('model_id'),
                    'price_id' => $price->id,
                ]);
            } elseif ($request->input('model_type') === 'activity') {
                $priceLink = ActivityPrice::create([
                    'activity_id' => $request->input('model_id'),
                    'price_id' => $price->id,
                ]);
            } else {
                return response()->json(['success' => false, 'message' => 'Invalid model type provided.'], 400);
            }

            $priceLink->load('price');

            return response()->json(['success' => true, 'data' => $priceLink]);

        } catch (\Exception $e) {
            \Log::error('Error linking price: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An unexpected error occurred.'], 500);
        }
    }

    public function unlinkPrice($id, Request $request)
    {
        try {
            $priceLink = null;

            if ($request->input('model_type') === 'accommodatie') {
                $priceLink = AccommodatiePrice::findOrFail($id);
            } elseif ($request->input('model_type') === 'product') {
                $priceLink = ProductPrice::findOrFail($id);
            } elseif ($request->input('model_type') === 'activity') {
                $priceLink = ActivityPrice::findOrFail($id);
            } else {
                return response()->json(['success' => false, 'message' => 'Invalid model type provided.'], 400);
            }

            $priceLink->delete();
            return response()->json(['success' => true, 'message' => 'Price unlinked successfully.']);

        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Price link not found.'], 404);
        } catch (\Exception $e) {
            \Log::error('Error unlinking price: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }

    public function pricePage()
    {
        $data = $this->getPriceListData();
        return view('prices.list', $data);
    }

    public function downloadPdf()
    {
        $data = $this->getPriceListData();
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('pdf.pricelist', $data);
        return $pdf->download('Prijslijst-NoordenLicht-' . date('Y') . '.pdf');
    }

    private function getPriceListData()
    {
        $accommodaties = Accommodatie::with('prices.price')->get()->map(function ($item) {
            return $this->calculatePriceDetails($item);
        });

        $activities = Activity::with('prices.price')
            ->where(function ($query) {
                $query->where('date_start', '>=', now())
                    ->orWhere(function ($q) {
                        $q->whereNotNull('recurrence_rule')
                            ->where('recurrence_rule', '!=', 'never')
                            ->where(function ($q2) {
                                $q2->whereNull('end_recurrence')
                                    ->orWhere('end_recurrence', '>=', now());
                            });
                    });
            })
            ->orderBy('title', 'asc')
            ->get()
            ->map(function ($item) {
                return $this->calculatePriceDetails($item);
            });

        $productsRaw = Product::with(['prices.price'])->get();
        $productsRaw = $productsRaw->map(function ($item) {
            return $this->calculatePriceDetails($item);
        });

        $categoryNames = [
            '0' => 'Alles',
            '1' => 'Extra\'s',
            '2' => 'Overnachtingen',
        ];

        $products = $productsRaw->groupBy(function ($item) use ($categoryNames) {
            return $categoryNames[$item->type] ?? 'Overige';
        });

        return compact('accommodaties', 'activities', 'products');
    }

    private function calculatePriceDetails($model)
    {
        if ($model->prices->isEmpty()) {
            $model->calculated_price = 0;
            $model->has_discount = false;
            $model->price_formatted = 'Op aanvraag';
            return $model;
        }

        $allPrices = $model->prices->map(fn($p) => $p->price);

        $basePrices = $allPrices->where('type', 0);
        $percentageAdditions = $allPrices->where('type', 1); // VAT
        $fixedDiscounts = $allPrices->where('type', 2);
        $percentageDiscounts = $allPrices->where('type', 4);

        $totalBasePrice = $basePrices->sum('amount');

        // 1. Discounts
        $currentPrice = $totalBasePrice;
        foreach ($percentageDiscounts as $percentage) {
            $currentPrice -= $totalBasePrice * ($percentage->amount / 100);
        }
        $currentPrice -= $fixedDiscounts->sum('amount');

        $taxableAmount = max($currentPrice, 0);

        // 2. VAT
        $vatAmount = 0;
        foreach ($percentageAdditions as $percentage) {
            $vatAmount += $taxableAmount * ($percentage->amount / 100);
        }

        $calculatedPrice = $taxableAmount + $vatAmount;

        $model->calculated_price = max(0, $calculatedPrice);
        $model->base_price = $totalBasePrice;
        $model->has_discount = ($fixedDiscounts->isNotEmpty() || $percentageDiscounts->isNotEmpty());

        $model->price_formatted = '€ ' . number_format($model->calculated_price, 2, ',', '.');

        return $model;
    }
}
