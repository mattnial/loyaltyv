<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reward;
use App\Models\Redemption;
use Illuminate\Support\Facades\DB;

class ClubController extends Controller
{
    // 1. PANTALLA PRINCIPAL: Catálogo + Puntos del Usuario
    public function index(Request $request)
    {
        $user = $request->user();

        // Premios Activos y con Stock
        $rewards = Reward::where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('is_featured', 'desc') // Destacados primero
            ->orderBy('cost', 'asc')
            ->get()
            ->map(function($reward) {
                return [
                    'id'          => $reward->id,
                    'name'        => $reward->name,
                    'description' => $reward->description,
                    'cost'        => $reward->cost,
                    'image'       => $reward->image_path ? asset('storage/' . $reward->image_path) : null,
                    'is_featured' => $reward->is_featured,
                ];
            });

        return response()->json([
            'user_points' => $user->points,
            'rewards'     => $rewards
        ]);
    }

    // 2. CANJEAR PREMIO (Solicitud)
    public function redeem(Request $request)
    {
        $request->validate([
            'reward_id' => 'required|exists:rewards,id'
        ]);

        $user = $request->user();
        $reward = Reward::find($request->reward_id);

        // Validaciones
        if ($user->points < $reward->cost) {
            return response()->json(['message' => 'No tienes suficientes puntos.'], 400);
        }
        if ($reward->stock < 1) {
            return response()->json(['message' => 'Este premio se agotó.'], 400);
        }

        // Transacción Atómica (Seguridad)
        DB::transaction(function () use ($user, $reward) {
            // Descontar puntos
            $user->decrement('points', $reward->cost);
            
            // Restar stock
            $reward->decrement('stock');

            // Crear Solicitud de Canje
            Redemption::create([
                'customer_id' => $user->id,
                'reward_id'   => $reward->id,
                'reward_name' => $reward->name, // <--- ¡AQUÍ ESTABA EL ERROR! (Agregado)
                'points_used' => $reward->cost,
                'status'      => 'pending', 
            ]);
        });

        return response()->json([
            'message'     => '¡Canje exitoso! Tu solicitud está pendiente de aprobación.',
            'new_balance' => $user->points
        ]);
    }

    // 3. HISTORIAL DE CANJES
    public function history(Request $request)
    {
        $redemptions = Redemption::where('customer_id', $request->user()->id)
            ->with('reward')
            ->latest()
            ->get()
            ->map(function($r) {
                return [
                    'id'           => $r->id,
                    // Si tenemos reward_name en BD úsalo, si no, intenta buscarlo en la relación
                    'reward_name'  => $r->reward_name ?? ($r->reward ? $r->reward->name : 'Premio Eliminado'),
                    'points'       => $r->points_used,
                    'status'       => $r->status,
                    'status_label' => $r->status_text ?? ucfirst($r->status),
                    'status_color' => $r->status_color ?? 'grey',
                    'branch'       => $r->pickup_branch,
                    'date'         => $r->created_at->format('d/m/Y'),
                    'proof_photo'  => $r->proof_photo_path ? asset('storage/' . $r->proof_photo_path) : null,
                ];
            });

        return response()->json($redemptions);
    }
}