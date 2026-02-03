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
use Barryvdh\DomPDF\Facade\Pdf;
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

    public function bulkAdd(Request $request)
    {
        $items = json_decode($request->input('items'), true);

        // 1. Prepare Cart session (standard logic)
        $cart = [];
        if (is_array($items)) {
            foreach ($items as $item) {
                $id = $item['id'];
                $qty = (int)$item['qty'];
                if ($qty > 0) {
                    $cart[$id] = $qty;
                }
            }
        }

        Session::put('cart', $cart);
        Session::put('cart_mixed', [
            'products' => $cart,
            'activities' => []
        ]);

        // 2. Target the existing order
        if ($request->has('existing_order_id')) {
            Session::put('target_order_id', $request->input('existing_order_id'));
        }

        // 3. Process Form Data
        if ($request->has('supplement_forms')) {
            $submittedForms = $request->input('supplement_forms');
            $cartFormData = ['products' => [], 'activities' => []];
            foreach ($submittedForms as $productId => $forms) {
                $cartFormData['products'][$productId] = $forms;
            }
            Session::put('cart_form_data', $cartFormData);
        }

        return $this->processImmediateOrder($request->input('existing_order_id'));
    }

    protected function processImmediateOrder($orderId)
    {
        $order = \App\Models\Order::findOrFail($orderId);

        $request = new Request([
            'email'      => $order->email,
            'first_name' => $order->first_name,
            'last_name'  => $order->last_name,
            'address'    => $order->address,
            'zipcode'    => $order->zipcode,
            'city'       => $order->city,
        ]);

        return $this->store($request);
    }

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
                $price = $product->calculated_full_price;
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
        $targetOrderId = Session::get('target_order_id');

        if (!$targetOrderId) {
            $request->validate([
                'email' => 'required|email',
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'address' => 'required|string',
                'zipcode' => 'required|string',
                'city' => 'required|string',
            ]);
        }


        $cart = Session::get('cart_mixed', [
            'products' => Session::get('cart', []),
            'activities' => []
        ]);

        $cartFormData = Session::get('cart_form_data', [
            'products' => [],
            'activities' => []
        ]);

        if (empty($cart['products']) && empty($cart['activities'])) {
            return redirect()->route('shop');
        }

        DB::beginTransaction();
        try {
            $targetOrderId = Session::get('target_order_id');
            $order = null;
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

            if ($targetOrderId) {
                $order = Order::find($targetOrderId);
                $order->update([
                    'email' => $request->email,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'status' => 'updated'
                ]);
            } else {
                $order = Order::create([
                    'order_number' => 'ORD-' . strtoupper(uniqid()),
                    'user_id' => $userId,
                    'status' => 'open',
                    'payment_status' => 'open',
                    'email' => $request->email,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'address' => $request->address,
                    'zipcode' => $request->zipcode,
                    'city' => $request->city,
                    'country' => 'NL',
                    'total_amount' => 0,
                ]);
            }

            $currentTransactionTotal = 0;

            // Process Products
            if (!empty($cart['products'])) {
                $products = Product::with('prices.price')->whereIn('id', array_keys($cart['products']))->get();
                foreach ($products as $product) {
                    $quantity = $cart['products'][$product->id];

                    // --- CALCULATION LOGIC START (Dutch) ---
                    $allPrices = $product->prices->map(fn($p) => $p->price);

                    $basePrices = $allPrices->where('type', 0);
                    $percentageAdditions = $allPrices->where('type', 1); // VAT
                    $fixedDiscounts = $allPrices->where('type', 2);
                    $extraCosts = $allPrices->where('type', 3);
                    $percentageDiscounts = $allPrices->where('type', 4);

                    $totalBasePrice = $basePrices->sum('amount');

                    // 1. Discounts
                    $currentPrice = $totalBasePrice;
                    $totalPercentageValue = 0;

                    foreach ($percentageDiscounts as $percentage) {
                        $amount = $totalBasePrice * ($percentage->amount / 100);
                        $currentPrice -= $amount;
                        $totalPercentageValue += $percentage->amount;
                    }

                    $totalFixedDiscount = $fixedDiscounts->sum('amount');
                    $currentPrice -= $totalFixedDiscount;

                    // Taxable
                    $taxableAmount = max($currentPrice, 0);

                    // 2. VAT
                    $totalVatAmount = 0;
                    $additionsMetadata = [];

                    foreach ($percentageAdditions as $percentage) {
                        $amount = $taxableAmount * ($percentage->amount / 100);
                        $totalVatAmount += $amount;
                        $additionsMetadata[] = [
                            'name' => $percentage->name,
                            'amount' => $percentage->amount,
                            'calculated_amount' => $amount
                        ];
                    }

                    $priceInclVat = $taxableAmount + $totalVatAmount;

                    // 3. Extras
                    $totalExtraCost = $extraCosts->sum('amount');
                    $extrasMetadata = [];
                    foreach($extraCosts as $extra) {
                        $extrasMetadata[] = [
                            'name' => $extra->name,
                            'amount' => $extra->amount
                        ];
                    }

                    $unitPrice = max($priceInclVat + $totalExtraCost, 0);
                    $lineTotal = $unitPrice * $quantity;
                    // --- CALCULATION LOGIC END ---

                    $orderItem = OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $lineTotal,
                        // Metadata
                        'unit_base_price' => $totalBasePrice,
                        'unit_vat' => $totalVatAmount,
                        'unit_discount_percentage' => $totalPercentageValue,
                        'unit_discount_amount' => $totalFixedDiscount,
                        'unit_extra' => $totalExtraCost,
                        'price_metadata' => [
                            'additions' => $additionsMetadata,
                            'extras' => $extrasMetadata,
                            'pre_discount_price' => $totalBasePrice
                        ]
                    ]);

                    $currentTransactionTotal += $lineTotal;

                    // Form Data (omitted)
                }
            }

            $order->total_amount += $currentTransactionTotal;
            $order->save();

            if ($currentTransactionTotal == 0) {
                if (!$targetOrderId) {
                    $order->update([
                        'status' => 'paid',
                        'payment_status' => 'paid',
                        'mollie_payment_id' => 'free'
                    ]);
                } else {
                    $order->update(['status' => 'updated']);
                }

                Ticket::where('order_id', $order->id)->update(['status' => 'valid']);

                Session::forget(['cart', 'cart_mixed', 'cart_form_data', 'target_order_id']);

                DB::commit();
                return redirect()->route('order.success', ['order_number' => $order->order_number]);
            }

            $payment = Mollie::api()->payments->create([
                "amount" => [
                    "currency" => "EUR",
                    "value" => number_format($currentTransactionTotal, 2, '.', '')
                ],
                "description" => ($targetOrderId ? "Update Order " : "Order ") . $order->order_number,
                "redirectUrl" => route('order.success', ['order_number' => $order->order_number]),
                "metadata" => ["order_id" => $order->id],
            ]);

            Session::forget(['cart', 'cart_mixed', 'cart_form_data', 'target_order_id']);

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
        $percentageAdditions = $allPrices->where('type', 1); // VAT
        $fixedDiscounts = $allPrices->where('type', 2);
        $extraCosts = $allPrices->where('type', 3);
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

        // 3. Extras
        $totalExtras = $extraCosts->sum('amount');

        return max($taxableAmount + $vatAmount + $totalExtras, 0);
    }

    // ... (success, downloadInvoice, retry, list, details, updateStatus unchanged)
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
            case 'updated':
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

    public function downloadInvoice($order_number)
    {
        $order = Order::with(['tickets.activity', 'items'])->where('order_number', $order_number)->limit(1)->first();

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('pdf.invoice', compact('order'));

        return $pdf->download('Factuur-' . $order->order_number . '.pdf');
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
        $query = Order::with('user')->orderBy('updated_at', 'desc');

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
        $order = Order::with(['items.product', 'tickets.activity', 'user'])->findOrFail($id);

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

    public function streamInvoicePdf($order_number)
    {
        $order = Order::with(['tickets.activity', 'items'])->where('order_number', $order_number)->limit(1)->first();
        $pdf = Pdf::loadView('pdf.invoice', compact('order'));
        return $pdf->stream();
    }
}
