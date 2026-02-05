<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Redemption;
use App\Models\PointHistory;
use App\Notifications\LoyaltyNotification;

class RedemptionController extends Controller
{
    // 1. Listar pedidos (Tablero Kanban simplificado)
    public function index()
    {
        $pending = Redemption::where('status', 'pending')->with(['customer', 'reward'])->latest()->get();
        $approved = Redemption::where('status', 'approved')->with(['customer', 'reward'])->latest()->get();
        $completed = Redemption::where('status', 'completed')->with(['customer', 'reward'])->latest()->take(20)->get();

        return view('admin.redemptions.index', compact('pending', 'approved', 'completed'));
    }

    // 2. APROBAR: Asignar sucursal y notificar
    public function approve(Request $request, $id)
    {
        $redemption = Redemption::findOrFail($id);
        
        $request->validate([
            'branch' => 'required|in:Loja,Vilcabamba,Palanda'
        ]);

        $redemption->update([
            'status' => 'approved',
            'pickup_branch' => $request->branch
        ]);

        // Notificar al cliente
        $redemption->customer->notify(new LoyaltyNotification(
            '¡Solicitud Aprobada! 📍',
            "Tu premio '{$redemption->reward->name}' está listo. Retíralo en nuestra sucursal de {$request->branch}.",
            'redemption_approved'
        ));

        return back()->with('success', 'Solicitud aprobada. Cliente notificado.');
    }

    // 3. COMPLETAR: Subir foto y cerrar
    public function complete(Request $request, $id)
    {
        $redemption = Redemption::findOrFail($id);

        $request->validate([
            'proof_photo' => 'required|image|max:4096' // Max 4MB
        ]);

        // Subir foto
        $path = $request->file('proof_photo')->store('proofs', 'public');

        $redemption->update([
            'status' => 'completed',
            'proof_photo_path' => $path
        ]);

        // Notificar al cliente con éxito
        $redemption->customer->notify(new LoyaltyNotification(
            '¡Premio Entregado! 🎁',
            "Gracias por canjear tus puntos. Hemos subido la prueba de entrega a tu historial.",
            'redemption_completed'
        ));

        return back()->with('success', 'Entrega registrada correctamente.');
    }

    // 4. RECHAZAR: Devolver puntos
    public function reject(Request $request, $id)
    {
        $redemption = Redemption::findOrFail($id);
        
        // Devolvemos los puntos al cliente
        $redemption->customer->increment('points', $redemption->points_used);
        
        // Registramos devolución en historial
        PointHistory::create([
            'customer_id' => $redemption->customer_id,
            'type' => 'earn', // Es un ingreso porque se los devolvemos
            'points' => $redemption->points_used,
            'description' => "Devolución por canje rechazado: {$redemption->reward->name}"
        ]);

        $redemption->update(['status' => 'rejected', 'admin_note' => $request->note]);

        $redemption->customer->notify(new LoyaltyNotification(
            'Solicitud Rechazada 😔',
            "No pudimos procesar tu canje. Te hemos devuelto los puntos.",
            'redemption_rejected'
        ));

        return back()->with('success', 'Solicitud rechazada y puntos devueltos.');
    }
}