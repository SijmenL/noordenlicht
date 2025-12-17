<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    /**
     * Display the shopping cart.
     */
    public function index()
    {
        $cart = Session::get('cart', []);
        $cartItems = [];
        $totalPrice = 0;

        if (!empty($cart)) {
            $products = Product::with(['prices.price'])->whereIn('id', array_keys($cart))->get();

            foreach ($products as $product) {
                $quantity = $cart[$product->id];
                $unitPrice = $product->calculated_price;
                $lineTotal = $unitPrice * $quantity;

                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal
                ];

                $totalPrice += $lineTotal;
            }
        }

        return view('shop.cart', compact('cartItems', 'totalPrice'));
    }

    /**
     * Add a product to the cart.
     */
    public function add(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $cart = Session::get('cart', []);

        if (isset($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        Session::put('cart', $cart);

        return redirect()->back()->with('success', 'Product toegevoegd aan winkelmandje!');
    }

    /**
     * Update cart quantity.
     */
    public function update(Request $request, $id)
    {
        $quantity = max(1, intval($request->quantity));
        $cart = Session::get('cart', []);

        if (isset($cart[$id])) {
            $cart[$id] = $quantity;
            Session::put('cart', $cart);
        }

        return redirect()->route('cart.index')->with('success', 'Winkelmandje bijgewerkt.');
    }

    /**
     * Remove item from cart.
     */
    public function remove($id)
    {
        $cart = Session::get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            Session::put('cart', $cart);
        }

        return redirect()->route('cart.index')->with('success', 'Product verwijderd uit winkelmandje.');
    }
}
