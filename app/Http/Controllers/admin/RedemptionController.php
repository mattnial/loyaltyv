<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Redemption;
use App\Services\FirebaseService; // <--- IMPORTANTE: Usamos nuestro nuevo servicio

class RedemptionController extends Controller
{
    public function index()
    {
        // Listar pedidos pendientes primero
        $redemptions = Redemption::with(['customer', 'reward'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('admin.loyalty.redemptions', compact('redemptions'));
    }

    // APROBAR PEDIDO
    public function approve($id)
    {
        $redemption = Redemption::with('customer', 'reward')->findOrFail($id);
        
        $redemption->status = 'approved';
        $redemption->processed_at = now();
        $redemption->save();

        // --- NOTIFICACIÓN AUTOMÁTICA ---
        FirebaseService::send(
            $redemption->customer->fcm_token,
            '¡Pedido Aprobado! ✅',
            "Tu canje por '{$redemption->reward->name}' ha sido aprobado. Pronto lo recibirás.",
            ['type' => 'order_update', 'order_id' => (string)$id]
        );

        return back()->with('success', 'Pedido aprobado y cliente notificado.');
    }

    // RECHAZAR PEDIDO (Devolver puntos)
    public function reject(Request $request, $id)
    {
        $redemption = Redemption::with('customer', 'reward')->findOrFail($id);
        
        // Devolvemos los puntos al cliente
        $redemption->customer->increment('points', $redemption->points_used);

        $redemption->status = 'rejected';
        $redemption->processed_at = now();
        $redemption->save();

        // --- NOTIFICACIÓN AUTOMÁTICA ---
        FirebaseService::send(
            $redemption->customer->fcm_token,
            'Pedido Rechazado ❌',
            "Hemos devuelto tus puntos. Motivo: No hay stock disponible por el momento.",
            ['type' => 'order_update', 'order_id' => (string)$id]
        );

        return back()->with('success', 'Pedido rechazado y puntos devueltos.');
    }

    // COMPLETAR / ENTREGAR
    public function complete($id)
    {
        $redemption = Redemption::with('customer', 'reward')->findOrFail($id);
        
        $redemption->status = 'completed';
        $redemption->save();

        // --- NOTIFICACIÓN AUTOMÁTICA ---
        FirebaseService::send(
            $redemption->customer->fcm_token,
            '¡Premio Entregado! 🚚',
            "Tu premio '{$redemption->reward->name}' ha sido entregado exitosamente. ¡Disfrútalo!",
            ['type' => 'order_update', 'order_id' => (string)$id]
        );

        return back()->with('success', 'Pedido marcado como entregado.');
    }
}