<?php

namespace App\Http\Controllers;

use App\Models\Price;
use App\Models\AccommodatiePrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PriceController extends Controller
{
    /**
     * Links a new price component to a given model.
     * For now, it's hardcoded for Accommodatie, but can be adapted for polymorphism.
     */
    public function linkPrice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'model_id' => 'required|integer',
            'model_type' => 'required|string|in:accommodatie|in:products', // Can be expanded later
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'type' => 'required|integer|in:0,1,2,3,4', // Added '4' for percentage discount
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
            if ($request->input('model_type') === 'accommodatie') {
                $priceLink = AccommodatiePrice::create([
                    'accommodatie_id' => $request->input('model_id'),
                    'price_id' => $price->id,
                ]);

                // Eager load the price data to return to the frontend
                $priceLink->load('price');
            } else {
                return response()->json(['success' => false, 'message' => 'Invalid model type provided.'], 400);
            }

            return response()->json(['success' => true, 'data' => $priceLink]);

        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Error linking price: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An unexpected error occurred.'], 500);
        }
    }

    /**
     * Unlinks a price component from a model by deleting the pivot record.
     * Note: This does not delete the original Price record, allowing it to be reused.
     */
    public function unlinkPrice(AccommodatiePrice $priceLink)
    {
        try {
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

