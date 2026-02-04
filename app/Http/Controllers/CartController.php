<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class CartController extends Controller
{
    public function add(Request $request, $id)
    {
        $type = $request->input('type', 'product');
        $quantity = (int) $request->input('quantity', 1);
        $startDate = $request->input('start_date'); // Expecting Y-m-d H:i:s
        $formElements = $request->input('form_elements'); // Capture form data

        if ($quantity < 1) {
            return redirect()->back()->with('error', 'Aantal moet minimaal 1 zijn.');
        }

        $cart = Session::get('cart_mixed', [
            'products' => [],
            'activities' => []
        ]);

        // Retrieve existing form data session
        $cartFormData = Session::get('cart_form_data', [
            'products' => [],
            'activities' => []
        ]);

        // Sync legacy cart if exists
        if (Session::has('cart') && empty($cart['products'])) {
            $cart['products'] = Session::get('cart');
        }

        if ($type === 'activity') {
            $activity = Activity::find($id);
            if (!$activity) return redirect()->back()->with('error', 'Activiteit niet gevonden.');

            if (!$activity->hasTicketsAvailable($quantity, $startDate)) {
                return redirect()->back()->with('error', 'Er zijn geen tickets meer beschikbaar voor dit evenement.');
            }
            // Create a unique key using ID and Timestamp to separate occurrences
            $dateKey = $startDate ? Carbon::parse($startDate)->timestamp : '0';
            $cartKey = "{$id}_{$dateKey}";

            if (isset($cart['activities'][$cartKey])) {
                $cart['activities'][$cartKey]['quantity'] += $quantity;
            } else {
                $cart['activities'][$cartKey] = [
                    'id' => $id,
                    'quantity' => $quantity,
                    'start_date' => $startDate ?? $activity->date_start
                ];
            }

            // Store activity form data if present (keyed by cartKey)
            if ($formElements) {
                $cartFormData['activities'][$cartKey] = $formElements;
            }

        } else {
            $product = Product::find($id);
            if (!$product) return redirect()->back()->with('error', 'Product niet gevonden.');

            if (isset($cart['products'][$id])) {
                $cart['products'][$id] += $quantity;
            } else {
                $cart['products'][$id] = $quantity;
            }

            // Store product form data if present
            if ($formElements) {
                $cartFormData['products'][$id] = $formElements;
            }
        }

        Session::put('cart_mixed', $cart);
        Session::put('cart', $cart['products']);
        Session::put('cart_form_data', $cartFormData); // Save form data

        return redirect()->back()->with('success', 'CardAdded');
    }

    public function update(Request $request)
    {
        $id = $request->input('id'); // This is the composite key for activities
        $type = $request->input('type');
        $quantity = (int) $request->input('quantity');

        $cart = Session::get('cart_mixed', ['products' => [], 'activities' => []]);

        if ($quantity > 0) {
            if ($type === 'activity') {
                if (isset($cart['activities'][$id])) {
                    $cart['activities'][$id]['quantity'] = $quantity;
                }
            } else {
                $cart['products'][$id] = $quantity;
            }
        } else {
            return $this->remove($request);
        }

        Session::put('cart_mixed', $cart);
        Session::put('cart', $cart['products']);

        return redirect()->route('checkout')->with('success', 'Winkelwagen bijgewerkt.');
    }

    public function remove(Request $request)
    {
        $id = $request->input('id');
        $type = $request->input('type');

        $cart = Session::get('cart_mixed', ['products' => [], 'activities' => []]);
        $cartFormData = Session::get('cart_form_data', ['products' => [], 'activities' => []]);

        if ($type === 'activity') {
            unset($cart['activities'][$id]);
            unset($cartFormData['activities'][$id]);
        } else {
            unset($cart['products'][$id]);
            unset($cartFormData['products'][$id]);
        }

        Session::put('cart_mixed', $cart);
        Session::put('cart', $cart['products']);
        Session::put('cart_form_data', $cartFormData);

        return redirect()->route('checkout')->with('success', 'Item verwijderd uit winkelwagen.');
    }
}
