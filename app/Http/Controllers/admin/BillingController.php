<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Domains\Billing\Models\PaymentReport;

class BillingController extends Controller
{
    // 1. Bandeja de Entrada de Pagos (Solo pendientes)
    public function index()
    {
        $payments = PaymentReport::with('customer')
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.billing.index', compact('payments'));
    }

    // 2. Aprobar Pago
    public function approve($id)
    {
        $payment = PaymentReport::findOrFail($id);
        
        $payment->status = 'approved';
        $payment->save();

        // Aquí podríamos conectar con Divus más adelante
        
        return back()->with('success', 'Pago aprobado y registrado correctamente.');
    }

    // 3. Rechazar Pago
    public function reject(Request $request, $id)
    {
        $payment = PaymentReport::findOrFail($id);
        
        $payment->status = 'rejected';
        $payment->admin_note = $request->note;
        $payment->save();

        return back()->with('error', 'Pago rechazado.');
    }
}