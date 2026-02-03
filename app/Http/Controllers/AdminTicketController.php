<?php

namespace App\Http\Controllers; // <--- Verifica que esto diga exactamente esto

use Illuminate\Http\Request;
use App\Domains\Billing\Models\Ticket;

class AdminTicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::with('customer')
                         ->orderByRaw("FIELD(status, 'open', 'in_progress', 'resolved', 'closed')")
                         ->orderBy('created_at', 'desc')
                         ->get();

        return view('admin.tickets.index', compact('tickets'));
    }

    public function update(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        
        $ticket->update([
            'status' => $request->status,
            'admin_response' => $request->admin_response,
            'priority' => $request->priority
        ]);

        return back()->with('success', 'Ticket actualizado correctamente.');
    }
}