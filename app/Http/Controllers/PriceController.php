<?php

namespace App\Http\Controllers;

use App\Models\ActivityPrice;
use App\Models\Price;
use App\Models\AccommodatiePrice;
use App\Models\ProductPrice; // FIX: Added Import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PriceController extends Controller
{
    /**
     * Links a new price component to a given model.
     */
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
            // Step 1: Create the new Price record
            $price = Price::create([
                'name' => $request->input('name'),
                'amount' => $request->input('amount'),
                'type' => $request->input('type'),
            ]);

            // Step 2: Create the link (pivot) record
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

            // Eager load the price data to return to the frontend
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
}
