<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Order;
use App\Models\ActivityFormResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function list(Request $request)
    {
        $query = Booking::with(['user', 'accommodatie', 'order']);

        // Sort: Closer to today is higher.
        // We use SQL's ABS(DATEDIFF(start, NOW())) to sort by proximity to current date.
        // This puts today/tomorrow/yesterday at the top, and distant past/future at the bottom.
        $query->orderByRaw("ABS(DATEDIFF(start, NOW())) ASC")
            ->orderBy('start', 'asc'); // Tie-breaker: Chronological for same-day distance

        // Search logic: User name/email, Booking ID, or Accommodation name
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('user', function($sub) use ($search){
                        $sub->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('accommodatie', function($sub) use ($search){
                        $sub->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if ($request->has('status') && $request->status != '' && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        $bookings = $query->paginate(20)->withQueryString();

        return view('admin.bookings.list', [
            'bookings' => $bookings,
            'search' => $request->search,
            'status' => $request->status
        ]);
    }

    public function details(Request $request, $id)
    {
        $location = $request->input('location', 'bookings');

        $booking = Booking::with(['accommodatie', 'user', 'order.items'])->findOrFail($id);

        // Fetch form responses associated with the linked order (for supplements etc)
        $formResponses = collect();
        if($booking->order_id) {
            $formResponses = ActivityFormResponses::with(['formElement'])
                ->where('order_id', $booking->order_id)
                ->get();
        }


        return view('admin.bookings.details', compact('booking', 'formResponses', 'location'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:confirmed,cancelled,completed,pending,reserved,lunch_later'
        ]);

        $booking = Booking::findOrFail($id);
        $booking->status = $request->status;
        $booking->save();

        return redirect()->back()->with('success', 'Boeking status succesvol aangepast naar ' . ucfirst($request->status));
    }
}
