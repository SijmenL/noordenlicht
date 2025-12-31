<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityException;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function download($uuid)
    {
        $ticket = Ticket::where('uuid', $uuid)->with('activity', 'user', 'order')->firstOrFail();

        $pdf = Pdf::loadView('pdf.ticket', compact('ticket'));

        return $pdf->download('ticket-' . $ticket->activity->title . '.pdf');
    }

    public function list(Request $request)
    {
        $query = Ticket::with(['activity', 'user', 'order'])->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('uuid', 'like', "%{$search}%")
                    ->orWhereHas('user', function($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('activity', function($a) use ($search) {
                        $a->where('title', 'like', "%{$search}%");
                    });
            });
        }

        $tickets = $query->paginate(20)->withQueryString();

        return view('admin.tickets.list', [
            'tickets' => $tickets,
            'search' => $request->search
        ]);
    }

    public function details($uuid)
    {
        $ticket = Ticket::where('uuid', $uuid)->with('activity', 'user', 'order')->firstOrFail();
        return view('admin.tickets.details', compact('ticket'));
    }

    public function streamPdf($id)
    {
        $ticket = Ticket::with('activity', 'user', 'order')->findOrFail($id);
        $pdf = Pdf::loadView('pdf.ticket', compact('ticket'));
        return $pdf->stream();
    }

    public function updateStatus(Request $request, $uuid)
    {
        $request->validate([
            'status' => 'required|in:pending,valid,used,cancelled'
        ]);

        $ticket = Ticket::where('uuid', $uuid)->with('activity', 'user', 'order')->firstOrFail();
        $ticket->status = $request->status;

        if ($request->status == 'used' && is_null($ticket->scanned_at)) {
            $ticket->scanned_at = now();
        } elseif ($request->status != 'used') {
            $ticket->scanned_at = null;
        }

        $ticket->save();

        return redirect()->back()->with('success', 'Ticketstatus succesvol aangepast naar ' . ucfirst($request->status));
    }

    // Cancel a ticket
    public function cancelTicket($id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->update(['status' => 'canceled']);

        return back()->with('success', 'Ticket is geannuleerd.');
    }

    // Show the scanning page
    private function getNextUpcomingActivities()
    {
        Carbon::setLocale('nl');

        // 1. Set Range: From TODAY (00:00) to 3 months in the future
        // We only need 5, so a 3-month window is usually enough to find them.
        $rangeStart = Carbon::today();
        $rangeEnd = Carbon::today()->addMonths(3)->endOfDay();

        // 2. Fetch Database Activities in Range (Parents)
        $fetchedActivities = Activity::query()
            ->where(function ($query) use ($rangeStart, $rangeEnd) {
                $query->whereBetween('date_start', [$rangeStart, $rangeEnd])
                    ->orWhere(function ($q) use ($rangeStart, $rangeEnd) {
                        // Check recurring events that might fall in this range
                        $q->whereIn('recurrence_rule', ['daily', 'weekly', 'monthly'])
                            ->where(function ($q2) use ($rangeStart) {
                                $q2->whereNull('end_recurrence')
                                    ->orWhere('end_recurrence', '>=', $rangeStart);
                            })
                            ->where('date_start', '<=', $rangeEnd);
                    });
            })
            ->orderBy('date_start')
            ->get();

        // 3. Load Exceptions
        $exceptionsByActivity = ActivityException::whereIn('activity_id', $fetchedActivities->pluck('id'))
            ->get()
            ->groupBy('activity_id')
            ->map(function ($group) {
                return $group->pluck('date')
                    ->map(fn($d) => Carbon::parse($d)->toDateString())
                    ->toArray();
            });

        // 4. Expand and Filter
        $activities = collect();
        foreach ($fetchedActivities as $activity) {
            // Expand occurrences within the [Today -> +3 Months] range
            $occurrences = $this->expandRecurringActivity($activity, $rangeStart, $rangeEnd);
            $skipDates = $exceptionsByActivity[$activity->id] ?? [];

            foreach ($occurrences as $occurrence) {
                $occDate = Carbon::parse($occurrence->date_start)->toDateString();

                // Skip exceptions
                if (in_array($occDate, $skipDates, true)) {
                    continue;
                }

                // Skip if it goes beyond end_recurrence
                if (!is_null($activity->end_recurrence)) {
                    $endRec = Carbon::parse($activity->end_recurrence)->endOfDay();
                    if (Carbon::parse($occurrence->date_start)->gt($endRec)) {
                        continue;
                    }
                }

                $activities->push($occurrence);
            }
        }

        // 5. Sort by Date and Take the first 5
        return $activities->sortBy('date_start')->values()->take(5);
    }

    public function scanTickets()
    {
        $activities = $this->getNextUpcomingActivities();

        // Retrieve the last selected activity from session (if available)
        $selectedActivityId = session('last_selected_activity_id');

        return view('admin.tickets.scan', compact('activities', 'selectedActivityId'));
    }

    public function check(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string',
            'activity_id' => 'required|integer',
        ]);

        $barcode = $request->input('barcode');
        $selectedActivityId = (int) $request->input('activity_id');

        // SAVE SELECTION: Store this in the session so it remembers it on reload
        session(['last_selected_activity_id' => $selectedActivityId]);

        // Reload the list for the dropdown
        $activities = $this->getNextUpcomingActivities();

        // Find ticket by UUID
        $ticket = Ticket::with(['activity', 'user'])
            ->where('uuid', $barcode)
            ->first();

        // 1. Check if Ticket Exists
        if (!$ticket) {
            $alertType = 'danger';
            $message = 'Ticket niet gevonden: ' . $barcode;
            // Pass $selectedActivityId back to the view
            return view('admin.tickets.scan', compact('alertType', 'message', 'activities', 'selectedActivityId'));
        }

        // 2. Check if Ticket belongs to the Selected Activity
        // Use strict comparison with the USER SELECTED ID, not the ticket's ID
        if ($ticket->activity_id !== $selectedActivityId) {
            $alertType = 'danger';
            $message = 'FOUT: Dit ticket hoort bij activiteit "' . ($ticket->activity->title ?? 'Onbekend') . '"';
            // We return early, keeping the user's selection (A), even though ticket is (B)
            return view('admin.tickets.scan', compact('ticket', 'message', 'alertType', 'activities', 'selectedActivityId'));
        }

        // 3. Status Checks
        $status = 'valid';
        $message = 'Geldig Ticket - Succesvol Ingecheckt';
        $alertType = 'success';

        if ($ticket->status === 'cancelled') {
            $status = 'invalid';
            $message = 'Ticket is ongeldig!';
            $alertType = 'danger';
        } elseif ($ticket->status === 'used') {
            $status = 'warning';
            $message = 'Ticket is al gebruikt op ' . \Carbon\Carbon::parse($ticket->scanned_at)->format('d-m-Y H:i');
            $alertType = 'warning';
        } else {
            $ticket->update([
                'status' => 'used',
                'scanned_at' => now()
            ]);
        }

        return view('admin.tickets.scan', compact('ticket', 'status', 'message', 'alertType', 'activities', 'selectedActivityId'));
    }

    protected function expandRecurringActivity($activity, $rangeStart, $rangeEnd)
    {
        $occurrences = [];

        if (empty($activity->recurrence_rule) || !in_array($activity->recurrence_rule, ['daily', 'weekly', 'monthly'])) {
            // For non-recurring, check if it falls in the range
            $start = Carbon::parse($activity->date_start);
            if ($start->between($rangeStart, $rangeEnd)) {
                return [$activity];
            }
            return [];
        }

        $originalStart = Carbon::parse($activity->date_start);
        $originalEnd = Carbon::parse($activity->date_end);
        $duration = $originalEnd->diffInSeconds($originalStart);

        $lastOccurrence = $activity->end_recurrence ? Carbon::parse($activity->end_recurrence)->endOfDay() : Carbon::maxValue();
        $currentOccurrenceStart = $originalStart->copy();

        // Optimization: If original start is way in the past, fast forward loop start (simple version)
        // Note: For perfect accuracy with months/years, iteration is safer, but for daily/weekly we could skip.
        // Keeping iteration for safety as per your original code structure.

        while ($currentOccurrenceStart->lte($rangeEnd) && $currentOccurrenceStart->lte($lastOccurrence)) {

            // Only add if it falls inside the [Today -> Future] range
            if ($currentOccurrenceStart->between($rangeStart, $rangeEnd)) {
                $currentOccurrenceEnd = $currentOccurrenceStart->copy()->addSeconds($duration);

                $clone = clone $activity;
                $clone->date_start = $currentOccurrenceStart->copy();
                $clone->date_end = $currentOccurrenceEnd->copy();
                $occurrences[] = $clone;
            }

            switch ($activity->recurrence_rule) {
                case 'daily':
                    $currentOccurrenceStart->addDay();
                    break;
                case 'weekly':
                    $currentOccurrenceStart->addWeek();
                    break;
                case 'monthly':
                    $currentOccurrenceStart->addMonth();
                    break;
            }
        }

        return $occurrences;
    }
}
