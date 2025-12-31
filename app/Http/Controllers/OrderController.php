<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Activity;
use App\Models\ActivityFormElement;
use App\Models\ActivityFormResponses;
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
    // ... checkout() function (unchanged) ...
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
            'create_account' => 'nullable|boolean',
        ]);

        $cart = Session::get('cart_mixed', [
            'products' => Session::get('cart', []),
            'activities' => []
        ]);

        // Retrieve the form data we saved in CartController
        $cartFormData = Session::get('cart_form_data', [
            'products' => [],
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
                $existingUser = User::where('email', $request->email)->first();

                if (!$existingUser) {
                    $randomPassword = Str::random(10);
                    $newUser = User::create([
                        'email' => $request->email,
                        'name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'street' => $request->address,
                        'postal_code' => $request->zipcode,
                        'city' => $request->city,
                        'password' => Hash::make($randomPassword),
                    ]);

                    $userId = $newUser->id;
                    Auth::login($newUser);
                } else {
                    $userId = $existingUser->id;
                }
            }

            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'user_id' => $userId,
                'status' => 'open',
                'payment_status' => 'open', // Initial status
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

                    // --- SAVE FORM DATA FOR PRODUCT ---
                    if (isset($cartFormData['products'][$product->id])) {
                        $formData = $cartFormData['products'][$product->id];

                        // Calculate next submitted_id for this product context
                        $maxSubmittedId = ActivityFormResponses::where('product_id', $product->id)->max('submitted_id');
                        $nextSubmittedId = $maxSubmittedId ? $maxSubmittedId + 1 : 1;

                        foreach ($formData as $formElementId => $response) {
                            $element = ActivityFormElement::find($formElementId);
                            if(!$element) continue;

                            if (is_array($response)) {
                                foreach ($response as $checkboxValue) {
                                    ActivityFormResponses::create([
                                        'product_id' => $product->id,
                                        'order_id' => $order->id, // Linked to Order
                                        'location' => 'product',
                                        'activity_form_element_id' => $formElementId,
                                        'response' => $checkboxValue,
                                        'submitted_id' => $nextSubmittedId,
                                    ]);
                                }
                            } else {
                                ActivityFormResponses::create([
                                    'product_id' => $product->id,
                                    'order_id' => $order->id, // Linked to Order
                                    'location' => 'product',
                                    'activity_form_element_id' => $formElementId,
                                    'response' => $response,
                                    'submitted_id' => $nextSubmittedId,
                                ]);
                            }
                        }
                    }
                    // --- END FORM DATA ---
                }
            }

            // Process Activities (Tickets)
            if (!empty($cart['activities'])) {
                $activityIds = array_map(fn($item) => $item['id'], $cart['activities']);
                $activities = Activity::with('prices.price')->whereIn('id', array_unique($activityIds))->get()->keyBy('id');

                foreach ($cart['activities'] as $key => $cartItem) {
                    $activity = $activities->get($cartItem['id']);

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
                            'status' => 'pending'
                        ]);
                    }

                    $grandTotal += $lineTotal;

                    // --- SAVE FORM DATA FOR ACTIVITY (if applicable in future) ---
                    if (isset($cartFormData['activities'][$key])) {
                        $formData = $cartFormData['activities'][$key];
                        $maxSubmittedId = ActivityFormResponses::where('activity_id', $activity->id)->max('submitted_id');
                        $nextSubmittedId = $maxSubmittedId ? $maxSubmittedId + 1 : 1;

                        foreach ($formData as $formElementId => $response) {
                            if (is_array($response)) {
                                foreach ($response as $val) {
                                    ActivityFormResponses::create([
                                        'activity_id' => $activity->id,
                                        'order_id' => $order->id,
                                        'location' => 'activity',
                                        'activity_form_element_id' => $formElementId,
                                        'response' => $val,
                                        'submitted_id' => $nextSubmittedId,
                                    ]);
                                }
                            } else {
                                ActivityFormResponses::create([
                                    'activity_id' => $activity->id,
                                    'order_id' => $order->id,
                                    'location' => 'activity',
                                    'activity_form_element_id' => $formElementId,
                                    'response' => $response,
                                    'submitted_id' => $nextSubmittedId,
                                ]);
                            }
                        }
                    }
                    // --- END ACTIVITY FORM DATA ---
                }
            }

            $order->update(['total_amount' => $grandTotal]);

            // --- HANDLE FREE ORDERS ---
            if ($grandTotal == 0) {
                $order->update([
                    'status' => 'paid',
                    'payment_status' => 'paid',
                    'mollie_payment_id' => 'free'
                ]);

                Ticket::where('order_id', $order->id)->update(['status' => 'valid']);

                Session::forget('cart');
                Session::forget('cart_mixed');
                Session::forget('cart_form_data'); // Clear Form Data

                DB::commit();
                return redirect()->route('order.success', ['order_number' => $order->order_number]);
            }

            $payment = Mollie::api()->payments->create([
                "amount" => [
                    "currency" => "EUR",
                    "value" => number_format($grandTotal, 2, '.', '')
                ],
                "description" => "Order " . $order->order_number,
                "redirectUrl" => route('order.success', ['order_number' => $order->order_number]),
                "metadata" => [
                    "order_id" => $order->id,
                ],
            ]);

            Session::forget('cart');
            Session::forget('cart_mixed');
            Session::forget('cart_form_data'); // Clear Form Data

            $order->update(['mollie_payment_id' => $payment->id]);
            DB::commit();
            return redirect($payment->getCheckoutUrl(), 303);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Fout bij verwerken: ' . $e->getMessage());
        }
    }

    // ... calculateActivityPrice, success, retry, list, details, updateStatus ...
    // (Include the rest of the existing methods here unchanged)

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

    public function success($order_number)
    {
        $order = Order::with(['tickets.activity', 'items'])->where('order_number', $order_number)->limit(1)->first();

        if ($order->total_amount == 0 && $order->payment_status == 'paid') {
            return view('shop.success', compact('order'));
        }

        try {
            $payment = Mollie::api()->payments->get($order->mollie_payment_id);
            $paymentStatus = $payment->status;
        } catch (\Exception $e) {
            return redirect()->route('shop')->with('error', 'Kon betalingsstatus niet ophalen.');
        }

        $order->payment_status = $paymentStatus;
        $order->save();

        switch ($paymentStatus) {
            case 'paid':
                $order->update(['status' => 'paid']);
                foreach ($order->tickets as $ticket) {
                    $ticket->update(['status' => 'valid']);
                }
                return view('shop.success', compact('order'));

            case 'canceled':
            case 'failed':
            case 'expired':
                $order->update(['status' => $paymentStatus]);
                foreach ($order->tickets as $ticket) {
                    $ticket->update(['status' => 'cancelled']);
                }
                return view('shop.payment_failed', [
                    'order' => $order,
                    'status' => $paymentStatus
                ]);

            case 'open':
            case 'pending':
                return view('shop.payment_open', compact('order'));

            default:
                return view('shop.payment_failed', [
                    'order' => $order,
                    'status' => 'unknown'
                ]);
        }
    }

    public function retry($order_id)
    {
        $order = Order::findOrFail($order_id);

        if ($order->status == 'paid' || $order->payment_status == 'paid') {
            return redirect()->route('order.success', ['order_number' => $order->order_number]);
        }

        if ($order->total_amount == 0) {
            $order->update([
                'status' => 'paid',
                'payment_status' => 'paid',
                'mollie_payment_id' => 'free'
            ]);

            Ticket::where('order_id', $order->id)->update(['status' => 'valid']);

            return redirect()->route('order.success', ['order_number' => $order->order_number]);
        }

        try {
            $payment = Mollie::api()->payments->create([
                "amount" => [
                    "currency" => "EUR",
                    "value" => number_format($order->total_amount, 2, '.', '')
                ],
                "description" => "Retry Order " . $order->order_number,
                "redirectUrl" => route('order.success', ['order_number' => $order->order_number]),
                "metadata" => [
                    "order_id" => $order->id,
                ],
            ]);

            $order->update([
                'mollie_payment_id' => $payment->id,
                'status' => 'open',
                'payment_status' => 'open'
            ]);

            return redirect($payment->getCheckoutUrl(), 303);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Kon betaling niet opnieuw starten: ' . $e->getMessage());
        }
    }

    public function list(Request $request)
    {
        $query = Order::with('user')->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status != '' && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('admin.orders.list', [
            'orders' => $orders,
            'search' => $request->search,
            'status' => $request->status
        ]);
    }

    public function details($id)
    {
        // Added 'tickets.activity' to eager load data for matching
        $order = Order::with(['items.product', 'tickets.activity', 'user'])->findOrFail($id);

        // Fetch all form responses associated with this order
        $formResponses = ActivityFormResponses::with(['formElement', 'activity'])
            ->where('order_id', $order->id)
            ->get();

        return view('admin.orders.details', compact('order', 'formResponses'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:open,paid,shipped,completed,cancelled'
        ]);

        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return redirect()->back()->with('success', 'Orderstatus succesvol aangepast naar ' . ucfirst($request->status));
    }
}
