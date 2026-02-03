<?php

namespace App\Http\Controllers;

use App\Domains\Billing\Models\Subscription;
use App\Domains\Technical\Services\MikrotikService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Muestra la lista de todos los suscriptores
     */
    public function index()
    {
        // Traemos las suscripciones con sus relaciones (Cliente y Plan)
        $subscriptions = Subscription::with(['customer', 'plan'])->get();
        
        return view('subscriptions.index', compact('subscriptions'));
    }

    /**
     * Acción del Botón: Cortar o Activar servicio manualmente
     */
    public function toggleStatus($id)
    {
        $sub = Subscription::findOrFail($id);
        $mikrotik = new MikrotikService();
        
        // Si está activo, lo queremos cortar (false). Si está suspendido, activar (true).
        $shouldEnable = ($sub->status !== 'active');
        
        // 1. Ejecutar orden en Mikrotik (Simulado)
        // Nota: En producción usarías la IP real de la OLT/Router de ese cliente
        $routerIp = '192.168.1.1'; 

        $result = $mikrotik->togglePppoe(
            $routerIp, 'admin', 'password',
            $sub->pppoe_user,
            $shouldEnable
        );

        if ($result === true) {
            // 2. Si Mikrotik respondió bien, actualizamos la DB
            $sub->status = $shouldEnable ? 'active' : 'suspended';
            $sub->save();
            
            return back()->with('success', 'El servicio ha sido ' . ($shouldEnable ? 'ACTIVADO' : 'CORTADO') . ' correctamente.');
        } else {
            return back()->with('error', 'Error de comunicación con Mikrotik: ' . $result);
        }
    }
}