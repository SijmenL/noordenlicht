<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function download($uuid)
    {
        $ticket = Ticket::where('uuid', $uuid)->with('activity', 'user', 'order')->firstOrFail();

        $pdf = Pdf::loadView('pdf.ticket', compact('ticket'));

        return $pdf->download('ticket-' . $ticket->activity->title . '.pdf');
    }

    public function list()
    {
        $tickets = Ticket::with(['activity', 'user', 'order'])
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return view('admin.tickets.index', compact('tickets'));
    }

    // Show single ticket details (PDF render view)
    public function details($id)
    {
        $ticket = Ticket::with(['activity', 'user'])->findOrFail($id);
        return view('admin.tickets.show', compact('ticket'));
    }

    // Cancel a ticket
    public function cancelTicket($id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->update(['status' => 'canceled']);

        return back()->with('success', 'Ticket is geannuleerd.');
    }

    // Show the scanning page
    public function scanTickets()
    {
        return view('admin.tickets.scan');
    }

    // Process the scan (AJAX or Form Submit)
    public function check(Request $request)
    {
        $request->validate(['barcode' => 'required|string']);

        $barcode = $request->input('barcode');

        // Find ticket by UUID
        $ticket = Ticket::with(['activity', 'user'])
            ->where('uuid', $barcode)
            ->first();

        if (!$ticket) {
            return back()->with('error', 'Ticket niet gevonden: ' . $barcode)->withInput();
        }

        $status = 'valid';
        $message = 'Geldig Ticket';
        $alertType = 'success';

        if ($ticket->status === 'canceled') {
            $status = 'invalid';
            $message = 'Ticket is geannuleerd!';
            $alertType = 'danger';
        } elseif ($ticket->status === 'used') {
            $status = 'warning';
            $message = 'Ticket is al gebruikt!';
            $alertType = 'warning';
        }

        // Return view with result
        return view('admin.tickets.scan', compact('ticket', 'status', 'message', 'alertType'));
    }

    public function checkIn($id)
    {
        $ticket = Ticket::findOrFail($id);
        if ($ticket->status == 'valid') {
            $ticket->update(['status' => 'used']);
            return redirect()->route('admin.tickets.scan')->with('success', 'Ingecheckt: ' . $ticket->uuid);
        }

        return back()->with('error', 'Kon ticket niet inchecken.');
    }
}
