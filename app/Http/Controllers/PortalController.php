<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Domains\Billing\Services\DivusBridgeService;
use App\Domains\Billing\Models\Ticket;

class PortalController extends Controller
{
    // --- 1. DASHBOARD (La pantalla principal) ---
    public function dashboard()
    {
        $customer = Auth::guard('customer')->user();
        
        // A. Traemos Facturas desde la Nube (Divus)
        $divus = new DivusBridgeService();
        // Usamos try-catch por si falla la conexión a Divus, que no se caiga toda la web
        try {
            $invoices = $divus->getInvoicesByCedula($customer->identification);
        } catch (\Exception $e) {
            $invoices = []; // Si falla, mostramos lista vacía
        }
        
        // B. Traemos el Historial de Tickets (Local)
        $tickets = Ticket::where('customer_id', $customer->id)
                         ->orderBy('created_at', 'desc')
                         ->get();

        return view('portal.dashboard', compact('customer', 'invoices', 'tickets'));
    }

    // --- 2. CREAR TICKET (Guardar en BD) ---
    public function storeTicket(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:100',
            'description' => 'required|string',
        ]);

        Ticket::create([
            'customer_id' => Auth::guard('customer')->id(),
            'subject' => $request->subject,
            'description' => $request->description,
            'priority' => 'medium', // Prioridad por defecto
            'status' => 'open'
        ]);

        return back()->with('success', '¡Recibido! Tu reporte ha sido enviado a soporte técnico.');
    }

    // --- 3. CAMBIAR CONTRASEÑA ---
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed' // 'confirmed' busca un campo password_confirmation
        ]);
        
        $customer = Auth::guard('customer')->user();
        $customer->password = Hash::make($request->password);
        $customer->save();

        return back()->with('success', 'Tu contraseña ha sido actualizada exitosamente.');
    }
}