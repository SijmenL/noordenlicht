<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Activity;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Mollie\Laravel\Facades\Mollie;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function checkout()
    {
        $cart = Session::get('cart_mixed', [
            'products' => Session::get('cart', []),
            'activities' => []
        ]);

        if (empty($cart['products']) && empty($cart['activities'])) {
            return redirect()->route('shop')->with('error', 'Je winkelmandje is leeg.');
        }

        $items = collect();
        $total = 0;

        // Process Products
        if (!empty($cart['products'])) {
            $products = Product::with(['prices.price'])->whereIn('id', array_keys($cart['products']))->get();
            foreach ($products as $product) {
                $qty = $cart['products'][$product->id];
                $price = $product->calculated_price;
                $total += $price * $qty;

                $items->push((object)[
                    'id' => $product->id,
                    'type' => 'product',
                    'name' => $product->name,
                    'image' => 'files/products/images/' . $product->image,
                    'quantity' => $qty,
                    'price' => $price,
                    'model' => $product,
                    'details' => null
                ]);
            }
        }

        // Process Activities
        if (!empty($cart['activities'])) {
            $activityIds = array_map(fn($item) => $item['id'], $cart['activities']);
            $activities = Activity::with(['prices.price'])->whereIn('id', array_unique($activityIds))->get()->keyBy('id');

            foreach ($cart['activities'] as $key => $cartItem) {
                $activity = $activities->get($cartItem['id']);
                if (!$activity) continue;

                $qty = $cartItem['quantity'];
                $startDate = $cartItem['start_date'];
                $price = $this->calculateActivityPrice($activity);
                $total += $price * $qty;

                $items->push((object)[
                    'id' => $key,
                    'type' => 'activity',
                    'name' => $activity->title,
                    'image' => 'files/agenda/agenda_images/' . $activity->image,
                    'quantity' => $qty,
                    'price' => $price,
                    'model' => $activity,
                    'details' => Carbon::parse($startDate)->format('d-m-Y H:i')
                ]);
            }
        }

        return view('shop.checkout', compact('items', 'total'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'address' => 'required|string',
            'zipcode' => 'required|string',
            'city' => 'required|string',
            'create_account' => 'nullable|boolean', // Toggle validation
        ]);

        $cart = Session::get('cart_mixed', [
            'products' => Session::get('cart', []),
            'activities' => []
        ]);

        if (empty($cart['products']) && empty($cart['activities'])) {
            return redirect()->route('shop');
        }

        DB::beginTransaction();
        try {
            // --- Account Creation Logic ---
            $userId = Auth::id();
            if (!$userId && $request->filled('create_account') && $request->create_account == 1) {
                // Check if user exists
                $existingUser = User::where('email', $request->email)->first();

                if (!$existingUser) {
                    $randomPassword = Str::random(10);
                    $newUser = User::create([
                        'email' => $request->email,
                        'name' => $request->first_name, // Using First Name as Name
                        'last_name' => $request->last_name,
                        'street' => $request->address,
                        'postal_code' => $request->zipcode,
                        'city' => $request->city,
                        'password' => Hash::make($randomPassword),
                        // Add other default fields or make them nullable in DB
                    ]);

                    $userId = $newUser->id;
                    Auth::login($newUser); // Auto login the new user

                    // Ideally, send an email here with the $randomPassword
                } else {
                    $userId = $existingUser->id;
                    // Optional: Link order to existing user even if not logged in
                }
            }

            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'user_id' => $userId,
                'status' => 'open',
                'payment_status' => 'pending', // Will be set to paid immediately for test
                'email' => $request->email,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'address' => $request->address,
                'zipcode' => $request->zipcode,
                'city' => $request->city,
                'country' => 'NL',
                'total_amount' => 0,
            ]);

            $grandTotal = 0;

            // Process Products
            if (!empty($cart['products'])) {
                $products = Product::with('prices.price')->whereIn('id', array_keys($cart['products']))->get();
                foreach ($products as $product) {
                    $quantity = $cart['products'][$product->id];
                    $unitPrice = $product->calculated_price;
                    $lineTotal = $unitPrice * $quantity;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $lineTotal,
                    ]);

                    $grandTotal += $lineTotal;
                }
            }

            // Process Activities (Tickets)
            if (!empty($cart['activities'])) {
                $activityIds = array_map(fn($item) => $item['id'], $cart['activities']);
                $activities = Activity::with('prices.price')->whereIn('id', array_unique($activityIds))->get()->keyBy('id');

                foreach ($cart['activities'] as $cartItem) {
                    $activity = $activities->get($cartItem['id']);

                    // Helper logic we added earlier
                    if ($activity && method_exists($activity, 'hasTicketsAvailable') && !$activity->hasTicketsAvailable($cartItem['quantity'])) {
                        abort(422, 'Het evenement ' . $activity->title . ' is uitverkocht.');
                    }

                    if (!$activity) continue;

                    $quantity = $cartItem['quantity'];
                    $startDate = $cartItem['start_date'];
                    $unitPrice = $this->calculateActivityPrice($activity);
                    $lineTotal = $unitPrice * $quantity;

                    // Order Item for Receipt
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => null,
                        'product_name' => 'Ticket: ' . $activity->title . ' (' . Carbon::parse($startDate)->format('d-m-Y') . ')',
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $lineTotal,
                    ]);

                    // Generate Tickets
                    for ($i = 0; $i < $quantity; $i++) {
                        Ticket::create([
                            'uuid' => (string) Str::uuid(),
                            'user_id' => $userId,
                            'order_id' => $order->id,
                            'activity_id' => $activity->id,
                            'start_date' => $startDate,
                            'status' => 'valid'
                        ]);
                    }

                    $grandTotal += $lineTotal;
                }
            }

            $order->update(['total_amount' => $grandTotal]);


            $payment = Mollie::api()->payments->create([
                "amount" => [
                    "currency" => "EUR",
                    "value" => number_format($grandTotal, 2, '.', '')
                ],
                "description" => "Order " . $order->order_number,
                "redirectUrl" => route('order.success', ['order_id' => $order->id]),
//                "webhookUrl" => route('webhooks.mollie'),
                "metadata" => [
                    "order_id" => $order->id,
                ],
            ]);

            Session::forget('cart');
            Session::forget('cart_mixed');

            $order->update(['mollie_payment_id' => $payment->id]);
            DB::commit();
            return redirect($payment->getCheckoutUrl(), 303);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Fout bij verwerken: ' . $e->getMessage());
        }
    }

    private function calculateActivityPrice($activity)
    {
        $allPrices = $activity->prices->map(fn($p) => $p->price);
        $basePrices = $allPrices->where('type', 0);
        $percentageAdditions = $allPrices->where('type', 1);
        $fixedDiscounts = $allPrices->where('type', 2);
        $extraCosts = $allPrices->where('type', 3);
        $percentageDiscounts = $allPrices->where('type', 4);

        $totalBasePrice = $basePrices->sum('amount');
        $preDiscountPrice = $totalBasePrice;

        foreach ($percentageAdditions as $percentage) {
            $preDiscountPrice += $totalBasePrice * ($percentage->amount / 100);
        }

        $calculatedPrice = $preDiscountPrice;

        foreach ($percentageDiscounts as $percentage) {
            $calculatedPrice -= $preDiscountPrice * ($percentage->amount / 100);
        }

        $calculatedPrice -= $fixedDiscounts->sum('amount');
        $calculatedPrice += $extraCosts->sum('amount');

        return max(0, $calculatedPrice);
    }


    public function success($order_id)
    {
        $order = Order::with(['tickets.activity', 'items'])->findOrFail($order_id);
        return view('shop.success', compact('order'));
    }

    public function adminList()
    {
        $orders = Order::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    public function adminDetails($id)
    {
        $order = Order::with(['orderItems', 'tickets', 'user'])->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }
}
