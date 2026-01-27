<?php

namespace App\Http\Controllers;

use App\Models\Accommodatie;
use App\Models\AccommodatieIcon;
use App\Models\AccommodatieImage;
use App\Models\AccommodatiePrice;
use App\Models\Booking;
use App\Models\Price;
use App\Models\Product;
use App\Models\Activity;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Log;
use App\Models\ActivityFormResponses; // Added for saving form responses
use App\Models\ActivityFormElement;   // Added for verifying form elements
use http\Client\Curl\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Mollie\Laravel\Facades\Mollie;

class AccommodatieController extends Controller
{
    public function home()
    {
        $search = request('search');

        if (request('search')) {
            $all_accommodatie = Accommodatie::where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('type', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            })->orderBy('name')->paginate(25);
        } else {
            $all_accommodatie = Accommodatie::orderBy('name')->paginate(25);
        }

        return view('accommodaties.list', ['all_accommodaties' => $all_accommodatie, 'search' => $search]);
    }

    public function form()
    {
        return view('accommodaties.form');
    }

    public function formSuccess()
    {
        return view('accommodaties.form_succes');
    }

    public function details($id)
    {
        $user = Auth::user();

        try {
            $accommodatie = Accommodatie::findOrFail($id);
        } catch (ModelNotFoundException $exception) {
            return redirect()->route('accommodaties')->with('error', 'Deze accommodatie bestaat niet.');
        }

        return view('accommodaties.details', ['accommodatie' => $accommodatie, 'user' => $user]);
    }

    // --- Booking Flow Methods ---

    public function book($id)
    {
        try {
            // Eager load prices to avoid N+1 and ensure data is available
            $accommodatie = Accommodatie::with('prices.price')->findOrFail($id);
            // Eager load formElements for supplements to render them in the booking flow
            $supplements = Product::where('type', '0')->with('formElements')->get();

            // --- Price Calculation Logic ---
            $allPrices = $accommodatie->prices->map(fn($p) => $p->price);

            $basePrices = $allPrices->where('type', 0);
            $percentageAdditions = $allPrices->where('type', 1);
            $fixedDiscounts = $allPrices->where('type', 2);
            $percentageDiscounts = $allPrices->where('type', 4);

            $totalBasePrice = $basePrices->sum('amount');
            $preDiscountPrice = $totalBasePrice;

            // 1. Apply percentage additions
            foreach ($percentageAdditions as $percentage) {
                $preDiscountPrice += $totalBasePrice * ($percentage->amount / 100);
            }

            $calculatedPrice = $preDiscountPrice;

            // 2. Apply percentage discounts
            foreach ($percentageDiscounts as $percentage) {
                $calculatedPrice -= $preDiscountPrice * ($percentage->amount / 100);
            }

            // 3. Apply fixed amount discounts
            $calculatedPrice -= $fixedDiscounts->sum('amount');

            // Ensure price isn't negative
            $calculatedPrice = max(0, $calculatedPrice);

        } catch (ModelNotFoundException $exception) {
            return redirect()->route('accommodaties')->with('error', 'Deze accommodatie bestaat niet.');
        }

        return view('accommodaties.book', [
            'accommodatie' => $accommodatie,
            'supplements' => $supplements,
            'calculatedPrice' => $calculatedPrice
        ]);
    }

    public function getMonthlyAvailability(Request $request, $id)
    {
        $accommodatie = Accommodatie::findOrFail($id);
        $month = $request->input('month');
        $year = $request->input('year');

        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $bookings = Booking::where('accommodatie_id', $id)
            ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('start', [$startOfMonth, $endOfMonth])
                    ->orWhereBetween('end', [$startOfMonth, $endOfMonth])
                    ->orWhere(function($sub) use ($startOfMonth, $endOfMonth) {
                        $sub->where('start', '<', $startOfMonth)
                            ->where('end', '>', $endOfMonth);
                    });
            })
            ->get();

        $events = [];
        foreach ($bookings as $b) {
            $events[] = [
                'start' => Carbon::parse($b->start)->toIso8601String(),
                'end' => Carbon::parse($b->end)->toIso8601String(),
            ];
        }

        return response()->json([
            'events' => $events,
            'settings' => [
                'min_check_in' => $accommodatie->min_check_in ?? '00:00',
                'max_check_in' => $accommodatie->max_check_in ?? '23:59',
                'min_duration' => $accommodatie->min_duration_minutes ?? 120
            ]
        ]);
    }

    public function checkAvailability(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'start_time' => 'required',
            'end_date' => 'required|date',
            'end_time' => 'required',
        ]);

        $start = Carbon::parse($request->start_date . ' ' . $request->start_time);
        $end = Carbon::parse($request->end_date . ' ' . $request->end_time);

        if ($end->lte($start)) {
            return response()->json(['available' => false, 'message' => 'Eindtijd moet na starttijd liggen.']);
        }

        // Check for overlaps
        $overlap = Activity::where('title', 'like', 'Verhuur: ' . Accommodatie::find($request->accommodatie_id)->name . '%')
            ->where(function ($query) use ($start, $end) {
                $query->where('date_start', '<', $end)
                    ->where('date_end', '>', $start);
            })->exists();

        if ($overlap) {
            return response()->json(['available' => false, 'message' => 'De gekozen periode is niet beschikbaar.']);
        }

        return response()->json(['available' => true]);
    }

    public function storeBooking(Request $request)
    {
        $request->validate([
            'accommodatie_id' => 'required|exists:accommodaties,id',
            'start_date' => 'required|date',
            'start_time' => 'required',
            'end_date' => 'required|date',
            'end_time' => 'required',
            'comment' => 'nullable|string',
            'public' => 'nullable|boolean',
            'activity_description' => 'string|max:65535|nullable',
            'external_link' => 'nullable|url',
        ]);

        DB::beginTransaction();
        try {
            $start = Carbon::parse($request->start_date . ' ' . $request->start_time);
            $end = Carbon::parse($request->end_date . ' ' . $request->end_time);
            $accommodatie = Accommodatie::findOrFail($request->accommodatie_id);
            $user = Auth::user();

            // Double check availability (Race condition protection)
            $overlap = Booking::where('accommodatie_id', $accommodatie->id)
                ->where('status', '!=', 'cancelled')
                ->where(function ($query) use ($start, $end) {
                    $query->where('start', '<', $end)
                        ->where('end', '>', $start);
                })->lockForUpdate()->exists();

            if ($overlap) {
                throw new \Exception('Deze periode is zojuist geboekt door iemand anders.');
            }

            if (!ForumController::validatePostData($request->input('activity_description'))) {
                throw new \Exception('Je beschrijving bevat foute HTML elementen.');
            }

            $nameParts = explode(' ', $user->name, 2);

            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';

            // Create Order
            $order = Order::create([
                'order_number' => 'B-' . strtoupper(uniqid()),
                'user_id' => Auth::id(),
                'status' => 'open',
                'payment_status' => 'pending',
                'email' => $user->email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'address' => $user->street,
                'zipcode' => $user->postal_code,
                'city' => $user->city,
                'country' => 'NL',
                'total_amount' => 0,
            ]);

            // Calculate Price (Same logic as before)
            $allPrices = $accommodatie->prices->map(fn($p) => $p->price);
            $calculatedPrice = $this->calculateBasePrice($allPrices);

            $hours = $start->diffInMinutes($end) / 60;
            $accommodationTotal = $calculatedPrice * $hours;

            // Add Order Item for Accommodation
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => null,
                'product_name' => $accommodatie->name . ' (' . $start->format('d-m H:i') . ' tot ' . $end->format('d-m H:i') . ')',
                'quantity' => $hours,
                'unit_price' => $calculatedPrice,
                'total_price' => $accommodationTotal,
            ]);
            $grandTotal = $accommodationTotal;

            // Process Supplements
            $supplementsData = json_decode($request->input('supplements_data', '[]'), true);
            $supplementForms = $request->input('supplement_forms', []);

            if (is_array($supplementsData)) {
                foreach ($supplementsData as $item) {
                    $product = Product::find($item['id']);
                    if ($product && $item['qty'] > 0) {
                        $price = $product->calculated_price ?? 0;
                        $lineTotal = $price * $item['qty'];

                        // Create Order Item for Product
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'quantity' => $item['qty'],
                            'unit_price' => $price,
                            'total_price' => $lineTotal,
                        ]);
                        $grandTotal += $lineTotal;

                        // --- SAVE FORM DATA FOR PRODUCT ---
                        // We use the submitted form data which is keyed by Product ID and then FormElement ID
                        if (isset($supplementForms[$product->id])) {
                            $formData = $supplementForms[$product->id];

                            // Calculate next submitted_id for this product context
                            $maxSubmittedId = ActivityFormResponses::where('product_id', $product->id)->max('submitted_id');
                            $nextSubmittedId = $maxSubmittedId ? $maxSubmittedId + 1 : 1;

                            foreach ($formData as $formElementId => $response) {
                                // Although the key should be the ID from Blade, we can verify existence if needed
                                // but we skip strict verification for performance similar to your other controller

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
                                    // Only save if not empty
                                    if(!empty($response)) {
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
                        }
                        // --- END FORM DATA ---
                    }
                }
            }

            $order->update(['total_amount' => $grandTotal]);
            $isPublic = $request->has('public');

            // Create the Booking record
            Booking::create([
                'accommodatie_id' => $accommodatie->id,
                'user_id' => Auth::id(),
                'order_id' => $order->id,
                'start' => $start,
                'end' => $end,
                'status' => 'confirmed',
                'comment' => $request->comment,
                'public' => $isPublic,
                'activity_description' => $isPublic ? $request->activity_description : null,
                'external_link' => $isPublic ? $request->external_link : null,
            ]);

            if ($grandTotal == 0) {
                $order->update([
                    'status' => 'paid',
                    'payment_status' => 'paid',
                    'mollie_payment_id' => 'free'
                ]);

                DB::commit();
                return redirect()->route('order.success', ['order_number' => $order->order_number]);
            }

            // Payment
            $payment = Mollie::api()->payments->create([
                "amount" => [
                    "currency" => "EUR",
                    "value" => number_format($grandTotal, 2, '.', '')
                ],
                "description" => "Booking " . $order->order_number,
                "redirectUrl" => route('order.success', ['order_number' => $order->order_number]),
//                "webhookUrl" => route('webhooks.mollie'),
                "metadata" => ["order_id" => $order->id],
            ]);

            $order->update(['mollie_payment_id' => $payment->id]);

            DB::commit();
            return redirect($payment->getCheckoutUrl(), 303);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Fout: ' . $e->getMessage())->withInput();
        }
    }

    private function calculateBasePrice($allPrices) {
        $basePrices = $allPrices->where('type', 0);
        $percentageAdditions = $allPrices->where('type', 1);
        $fixedDiscounts = $allPrices->where('type', 2);
        $percentageDiscounts = $allPrices->where('type', 4);

        $price = $basePrices->sum('amount');

        foreach ($percentageAdditions as $p) {
            $price += $basePrices->sum('amount') * ($p->amount / 100);
        }
        foreach ($percentageDiscounts as $p) {
            $price -= $price * ($p->amount / 100);
        }
        $price -= $fixedDiscounts->sum('amount');

        return $price;
    }

    // Admin functions
    public function accommodaties()
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();
        $search = request('search');

        if (request('search')) {
            $all_accommodatie = Accommodatie::where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('type', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            })->orderBy('name')->paginate(25);
        } else {
            $all_accommodatie = Accommodatie::orderBy('name')->paginate(25);
        }

        return view('admin.accommodaties.list', ['user' => $user, 'roles' => $roles, 'all_accommodaties' => $all_accommodatie, 'search' => $search]);
    }

    public function createAccommodatie()
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();
        $tempImages = AccommodatieImage::whereNull('accommodatie_id')->get();
        $tempIcons = AccommodatieIcon::whereNull('accommodatie_id')->get();

        return view('admin.accommodaties.new', [
            'user' => $user,
            'roles' => $roles,
            'tempImages' => $tempImages,
            'tempIcons' => $tempIcons,
        ]);
    }

    public function createAccommodatieSave(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'required|string|max:65535',
            'image' => 'required|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'icon_data' => 'nullable|string',
            'temp_image_ids' => 'nullable|string',
            'temp_icon_data' => 'nullable|string',
            'temp_icon_ids' => 'nullable|string',
            'prices_to_add' => 'nullable|string',

            'min_check_in' => 'nullable|date_format:H:i',
            'max_check_in' => 'nullable|date_format:H:i',
            'min_duration_minutes' => 'nullable|integer',
        ]);


        $tempImageIds = array_filter(explode(',', $request->input('temp_image_ids', '')));

        DB::beginTransaction();
        try {
            $mainImageFile = $request->file('image');
            $mainImageName = time() . '_main.' . $mainImageFile->extension();
            $mainImagePath = public_path('files/accommodaties/images');
            $mainImageFile->move($mainImagePath, $mainImageName);

            $accommodatie = Accommodatie::create([
                'name' => $request->input('name'),
                'type' => $request->input('type'),
                'price' => 0,
                'description' => $request->input('description'),
                'image' => $mainImageName,
                'user_id' => Auth::id(),
                'min_check_in' => $request->input('min_check_in', '08:00'),
                'max_check_in' => $request->input('max_check_in', '23:00'),
                'min_duration_minutes' => $request->input('min_duration_minutes', 120),
            ]);

            // 1. Process Prices (Create Price model first, then link)
            if ($request->input('prices_to_add')) {
                $prices = json_decode($request->input('prices_to_add'), true);
                if (is_array($prices)) {
                    foreach ($prices as $priceData) {
                        $price = Price::create([
                            'name' => $priceData['name'],
                            'amount' => $priceData['amount'],
                            'type' => $priceData['type'],
                        ]);

                        AccommodatiePrice::create([
                            'accommodatie_id' => $accommodatie->id,
                            'price_id' => $price->id,
                        ]);
                    }
                }
            }

            // 2. Move and link temporary carousel images
            if (!empty($tempImageIds)) {
                $tempImages = AccommodatieImage::whereIn('id', $tempImageIds)->whereNull('accommodatie_id')->get();
                foreach ($tempImages as $tempImage) {
                    $oldPath = public_path('uploads/accommodaties/temp/images/' . $tempImage->image);
                    $newPathDir = public_path('files/accommodaties/carousel');
                    File::ensureDirectoryExists($newPathDir);
                    $newPath = $newPathDir . '/' . $tempImage->image;

                    if (File::exists($oldPath)) {
                        File::move($oldPath, $newPath);
                    }
                    $tempImage->accommodatie_id = $accommodatie->id;
                    $tempImage->save();
                }
            }

            // 3. Move and link temporary icons
            if ($request->input('temp_icon_data')) {
                $iconDataList = json_decode($request->input('temp_icon_data'), true);

                if (is_array($iconDataList) && !empty($iconDataList)) {
                    $iconIds = array_column($iconDataList, 'id');
                    $tempIcons = AccommodatieIcon::whereIn('id', $iconIds)->whereNull('accommodatie_id')->get()->keyBy('id');

                    foreach ($iconDataList as $iconItem) {
                        $id = $iconItem['id'] ?? null;
                        $text = $iconItem['text'] ?? '';

                        if ($id && isset($tempIcons[$id])) {
                            $tempIcon = $tempIcons[$id];

                            $oldPath = public_path('uploads/accommodaties/temp/icons/' . $tempIcon->icon);
                            $newPathDir = public_path('files/accommodaties/icons');
                            File::ensureDirectoryExists($newPathDir);
                            $newPath = $newPathDir . '/' . $tempIcon->icon;

                            if (File::exists($oldPath)) {
                                File::move($oldPath, $newPath);
                            }

                            $tempIcon->accommodatie_id = $accommodatie->id;
                            $tempIcon->text = $text;
                            $tempIcon->save();
                        }
                    }
                }
            }
            elseif ($request->input('temp_icon_ids')) {
                $tempIconIds = array_filter(explode(',', $request->input('temp_icon_ids', '')));
                if (!empty($tempIconIds)) {
                    $tempIcons = AccommodatieIcon::whereIn('id', $tempIconIds)->whereNull('accommodatie_id')->get();
                    foreach ($tempIcons as $tempIcon) {
                        $oldPath = public_path('uploads/accommodaties/temp/icons/' . $tempIcon->icon);
                        $newPathDir = public_path('files/accommodaties/icons');
                        File::ensureDirectoryExists($newPathDir);
                        $newPath = $newPathDir . '/' . $tempIcon->icon;

                        if (File::exists($oldPath)) {
                            File::move($oldPath, $newPath);
                        }

                        $tempIcon->accommodatie_id = $accommodatie->id;
                        $tempIcon->save();
                    }
                }
            }

            DB::commit();

            $log = new Log();
            $log->createLog(auth()->user()->id, 2, 'Create accommodatie', 'accommodatie', 'Accommodatie id: ' . $accommodatie->id, '');

            return redirect()->route('admin.accommodaties')->with('success', 'De accommodatie is succesvol opgeslagen!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Accommodatie save error: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', 'Er is een fout opgetreden bij het opslaan: ' . $e->getMessage())->withInput();
        }
    }

    public function editAccommodatie($id)
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        try {
            $accommodatie = Accommodatie::with(['images', 'icons'])->findOrFail($id);
        } catch (ModelNotFoundException $exception) {
            return redirect()->route('admin.accommodaties')->with('error', 'Deze accommodatie kon niet worden gevonden.');
        }

        return view('admin.accommodaties.edit', compact('user', 'roles', 'accommodatie'));
    }

    public function editAccommodatieSave(Request $request, $id)
    {
        $accommodatie = Accommodatie::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'required|string|max:65535',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'temp_image_ids' => 'nullable|string',
            'temp_icon_data' => 'nullable|string',
            'images_to_remove' => 'nullable|string',
            'icons_to_remove' => 'nullable|string',
            'updated_icon_data' => 'nullable|string',

            'min_check_in' => 'nullable|date_format:H:i',
            'max_check_in' => 'nullable|date_format:H:i',
            'min_duration_minutes' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $accommodatie->update($request->only('name', 'type', 'description', 'min_check_in', 'max_check_in', 'min_duration_minutes'));

            if ($request->hasFile('image')) {
                File::delete(public_path('files/accommodaties/images/' . $accommodatie->image));
                $mainImageFile = $request->file('image');
                $mainImageName = time() . '_main.' . $mainImageFile->extension();
                $mainImageFile->move(public_path('files/accommodaties/images'), $mainImageName);
                $accommodatie->image = $mainImageName;
                $accommodatie->save();
            }

            if ($request->input('updated_icon_data')) {
                $updatedIcons = json_decode($request->input('updated_icon_data'), true);
                if (is_array($updatedIcons)) {
                    foreach ($updatedIcons as $iconData) {
                        if (isset($iconData['id']) && isset($iconData['text'])) {
                            AccommodatieIcon::where('id', $iconData['id'])
                                ->where('accommodatie_id', $accommodatie->id)
                                ->update(['text' => $iconData['text']]);
                        }
                    }
                }
            }

            $imagesToRemoveIds = array_filter(explode(',', $request->input('images_to_remove', '')));
            if (!empty($imagesToRemoveIds)) {
                $imagesToRemove = AccommodatieImage::where('accommodatie_id', $accommodatie->id)->whereIn('id', $imagesToRemoveIds)->get();
                foreach ($imagesToRemove as $image) {
                    File::delete(public_path('files/accommodaties/carousel/' . $image->image));
                    $image->delete();
                }
            }
            $iconsToRemoveIds = array_filter(explode(',', $request->input('icons_to_remove', '')));
            if (!empty($iconsToRemoveIds)) {
                $iconsToRemove = AccommodatieIcon::where('accommodatie_id', $accommodatie->id)->whereIn('id', $iconsToRemoveIds)->get();
                foreach ($iconsToRemove as $icon) {
                    File::delete(public_path('files/accommodaties/icons/' . $icon->icon));
                    $icon->delete();
                }
            }

            $tempImageIds = array_filter(explode(',', $request->input('temp_image_ids', '')));
            if (!empty($tempImageIds)) {
                $tempImages = AccommodatieImage::whereIn('id', $tempImageIds)->whereNull('accommodatie_id')->get();
                foreach ($tempImages as $tempImage) {
                    $oldPath = public_path('uploads/accommodaties/temp/images/' . $tempImage->image);
                    $newPathDir = public_path('files/accommodaties/carousel');
                    File::ensureDirectoryExists($newPathDir);
                    $newPath = $newPathDir . '/' . $tempImage->image;

                    if (File::exists($oldPath)) {
                        File::move($oldPath, $newPath);
                    }
                    $tempImage->accommodatie_id = $accommodatie->id;
                    $tempImage->save();
                }
            }

            if ($request->input('temp_icon_data')) {
                $newIconsData = json_decode($request->input('temp_icon_data'), true);
                if (is_array($newIconsData) && !empty($newIconsData)) {
                    $newIconIds = array_column($newIconsData, 'id');
                    $tempIcons = AccommodatieIcon::whereIn('id', $newIconIds)->whereNull('accommodatie_id')->get()->keyBy('id');

                    foreach ($newIconsData as $iconData) {
                        if (isset($tempIcons[$iconData['id']])) {
                            $tempIcon = $tempIcons[$iconData['id']];

                            $oldPath = public_path('uploads/accommodaties/temp/icons/' . $tempIcon->icon);
                            $newPathDir = public_path('files/accommodaties/icons');
                            File::ensureDirectoryExists($newPathDir);
                            $newPath = $newPathDir . '/' . $tempIcon->icon;

                            if (File::exists($oldPath)) {
                                File::move($oldPath, $newPath);
                            }

                            $tempIcon->accommodatie_id = $accommodatie->id;
                            $tempIcon->text = $iconData['text'];
                            $tempIcon->save();
                        }
                    }
                }
            }

            DB::commit();

            $log = new Log();
            $log->createLog(auth()->user()->id, 2, 'Update accommodatie', 'accommodatie', 'Accommodatie id: ' . $accommodatie->id, '');

            return redirect()->route('admin.accommodaties.details', $accommodatie->id)->with('success', 'Accommodatie succesvol bijgewerkt!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Accommodatie update error: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', 'Er is een fout opgetreden bij het bijwerken. Probeer het opnieuw.')->withInput();
        }
    }

    public function deleteAccommodatie($id)
    {
        try {
            $accommodatie = Accommodatie::with(['images', 'icons'])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('admin.accommodaties')->with('error', 'Accommodatie niet gevonden.');
        }

        DB::beginTransaction();
        try {
            foreach ($accommodatie->images as $image) {
                File::delete(public_path('files/accommodaties/carousel/' . $image->image));
            }
            $accommodatie->images()->delete();

            foreach ($accommodatie->icons as $icon) {
                File::delete(public_path('files/accommodaties/icons/' . $icon->icon));
            }
            $accommodatie->icons()->delete();

            File::delete(public_path('files/accommodaties/images/' . $accommodatie->image));

            $accommodatie->delete();

            DB::commit();

            $log = new Log();
            $log->createLog(auth()->user()->id, 2, 'Delete accommodatie', 'accommodatie', 'Accommodatie: ' . $accommodatie->name, '');


            return redirect()->route('admin.accommodaties')->with('success', 'Accommodatie succesvol verwijderd.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Accommodatie delete error: " . $e->getMessage());
            return redirect()->route('admin.accommodaties')->with('error', 'Er is een fout opgetreden bij het verwijderen.');
        }
    }

    public function uploadTempImage(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'unique_id' => 'required|string',
        ]);

        $file = $request->file('file');
        $uniqueId = $request->input('unique_id');

        try {
            $tempPath = public_path('uploads/accommodaties/temp/images');
            File::ensureDirectoryExists($tempPath);
            $fileName = time() . '_' . $uniqueId . '.' . $file->extension();
            $file->move($tempPath, $fileName);

            $tempImage = AccommodatieImage::create([
                'accommodatie_id' => null,
                'image' => $fileName,
                'alt_text' => '',
                'temp_id' => $uniqueId,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $tempImage->id,
                    'path' => asset('uploads/accommodaties/temp/images/' . $fileName),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Uploadfout: ' . $e->getMessage()], 500);
        }
    }

    public function uploadTempIcon(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:svg|max:1024',
            'text' => 'required|string|max:255',
            'unique_id' => 'required|string',
        ]);

        $file = $request->file('file');
        $uniqueId = $request->input('unique_id');
        $text = $request->input('text');

        try {
            $tempPath = public_path('uploads/accommodaties/temp/icons');
            File::ensureDirectoryExists($tempPath);
            $fileName = time() . '_' . $uniqueId . '.' . $file->extension();
            $file->move($tempPath, $fileName);

            $tempIcon = AccommodatieIcon::create([
                'accommodatie_id' => null,
                'icon' => $fileName,
                'text' => $text,
                'temp_id' => $uniqueId,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $tempIcon->id,
                    'path' => asset('uploads/accommodaties/temp/icons/' . $fileName),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Uploadfout: ' . $e->getMessage()], 500);
        }
    }

    public function deleteTempImage(AccommodatieImage $image)
    {
        if ($image->accommodatie_id !== null) {
            return response()->json(['success' => false, 'message' => 'Afbeelding is reeds toegewezen.'], 403);
        }
        File::delete(public_path('uploads/accommodaties/temp/images/' . $image->image));
        $image->delete();
        return response()->json(['success' => true]);
    }

    public function deleteTempIcon(AccommodatieIcon $icon)
    {
        if ($icon->accommodatie_id !== null) {
            return response()->json(['success' => false, 'message' => 'Icoon is reeds toegewezen.'], 403);
        }
        File::delete(public_path('uploads/accommodaties/temp/icons/' . $icon->icon));
        $icon->delete();
        return response()->json(['success' => true]);
    }

    public function accommodatieDetails($id)
    {
        $user = Auth::user();
        $roles = $user->roles()->orderBy('role', 'asc')->get();

        try {
            $accommodatie = Accommodatie::findOrFail($id);
        } catch (ModelNotFoundException $exception) {
            return redirect()->route('admin.accommodaties')->with('error', 'Deze accommodatie bestaat niet.');
        }

        $log = new Log();
        $log->createLog(auth()->user()->id, 2, 'View accommodatie', 'Accommodaties dashboard', $accommodatie->name, '');

        return view('admin.accommodaties.details', ['user' => $user, 'roles' => $roles, 'accommodatie' => $accommodatie]);
    }
}
