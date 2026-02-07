<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Domains\Billing\Models\PaymentReport;
use App\Services\FirebaseService; // <--- IMPORTANTE: Importamos el motor de notificaciones

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
        // Cargamos también al cliente para obtener su token
        $payment = PaymentReport::with('customer')->findOrFail($id);
        
        $payment->status = 'approved';
        $payment->save();

        // --- NOTIFICACIÓN AUTOMÁTICA ---
        // Verificamos si el cliente tiene un token guardado
        if ($payment->customer && $payment->customer->fcm_token) {
            $monto = number_format($payment->amount, 2); // Asumiendo que tienes un campo 'amount'
            
            FirebaseService::send(
                $payment->customer->fcm_token,
                'Pago Aprobado ✅',
                "Hemos validado tu reporte de pago por $$monto. ¡Gracias!",
                ['type' => 'payment_update', 'id' => (string)$id, 'status' => 'approved']
            );
        }

        return back()->with('success', 'Pago aprobado, registrado y cliente notificado.');
    }

    // 3. Rechazar Pago
    public function reject(Request $request, $id)
    {
        $payment = PaymentReport::with('customer')->findOrFail($id);
        
        $payment->status = 'rejected';
        $payment->admin_note = $request->note;
        $payment->save();

        // --- NOTIFICACIÓN AUTOMÁTICA ---
        if ($payment->customer && $payment->customer->fcm_token) {
            // Usamos la nota del admin para decirle por qué falló
            $motivo = $request->note ?? 'No se pudo verificar la transferencia.';

            FirebaseService::send(
                $payment->customer->fcm_token,
                'Pago Rechazado ❌',
                "Tu pago no pudo ser validado. Motivo: $motivo",
                ['type' => 'payment_update', 'id' => (string)$id, 'status' => 'rejected']
            );
        }

        return back()->with('error', 'Pago rechazado y notificación enviada.');
    }
}