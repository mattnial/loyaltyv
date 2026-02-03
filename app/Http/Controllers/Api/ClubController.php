<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reward;
use App\Models\Redemption;
use App\Models\PointHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ClubController extends Controller
{
    // 1. OBTENER DATOS DEL CLUB (Home + Catálogo)
    public function index(Request $request)
    {
        // En Laravel, obtenemos el usuario autenticado directamente
        // Asumiendo que usas autenticación o pasas el ID (para pruebas rápidas usaremos la cédula como antes, pero lo ideal es Auth::user())
        
        $cedula = $request->input('cedula');
        $client = DB::table('customers')->where('identification', $cedula)->first();

        if (!$client) {
            return response()->json(['status' => 'error', 'msg' => 'Cliente no encontrado'], 404);
        }

        // Calcular Nivel
        $pts = $client->points;
        $nivel = "Bronce";
        if ($pts >= 1000) $nivel = "Plata";
        if ($pts >= 2500) $nivel = "Oro";
        if ($pts >= 5000) $nivel = "Diamante";

        // Obtener Catálogo (Solo activos y con stock)
        $rewards = Reward::where('is_active', 1)
                         ->where('stock', '>', 0)
                         ->orderBy('cost', 'asc')
                         ->get();

        // Obtener Historial de Canjes (Últimos 5)
        $history = Redemption::where('customer_id', $client->id)
                             ->orderBy('created_at', 'desc')
                             ->take(5)
                             ->get();

        return response()->json([
            'status' => 'success',
            'user' => [
                'points' => $pts,
                'level' => $nivel
            ],
            'catalog' => $rewards,
            'history' => $history
        ]);
    }

    // 2. CANJEAR PREMIO
    public function redeem(Request $request)
    {
        $cedula = $request->input('cedula');
        $rewardId = $request->input('reward_id');

        // Transacción de Base de Datos (Si falla algo, se deshace todo)
        return DB::transaction(function () use ($cedula, $rewardId) {
            
            // A. Buscar Cliente (Bloqueamos fila para evitar errores de concurrencia)
            $client = DB::table('customers')->where('identification', $cedula)->lockForUpdate()->first();
            
            // B. Buscar Premio
            $reward = Reward::where('id', $rewardId)->lockForUpdate()->first();

            // Validaciones
            if (!$client) return response()->json(['status' => 'error', 'msg' => 'Cliente no encontrado']);
            if (!$reward) return response()->json(['status' => 'error', 'msg' => 'Premio no existe']);
            if ($reward->stock < 1) return response()->json(['status' => 'error', 'msg' => 'Stock agotado']);
            if ($client->points < $reward->cost) return response()->json(['status' => 'error', 'msg' => 'Puntos insuficientes']);

            // C. Procesar Canje
            
            // 1. Restar Puntos
            DB::table('customers')->where('id', $client->id)->decrement('points', $reward->cost);
            
            // 2. Restar Stock
            $reward->decrement('stock');

            // 3. Crear Registro de Canje
            Redemption::create([
                'customer_id' => $client->id,
                'reward_id' => $reward->id,
                'reward_name' => $reward->name,
                'points_spent' => $reward->cost,
                'status' => 'pending'
            ]);

            // 4. Guardar Historial
            PointHistory::create([
                'customer_id' => $client->id,
                'type' => 'spend',
                'points' => $reward->cost,
                'description' => "Canje App: " . $reward->name
            ]);

            return response()->json(['status' => 'success', 'msg' => '¡Canje exitoso!']);
        });
    }
    // En ClubController.php

    public function redeemCode(Request $request)
    {
        $cedula = $request->input('cedula');
        $code = strtoupper($request->input('code'));

        $client = DB::table('customers')->where('identification', $cedula)->first();
        if (!$client) return response()->json(['status' => 'error', 'msg' => 'Cliente no encontrado']);

        $promo = DB::table('promo_codes')->where('code', $code)->where('is_active', 1)->first();

        // Validaciones
        if (!$promo) return response()->json(['status' => 'error', 'msg' => 'Código inválido']);
        if (Carbon::parse($promo->expires_at)->isPast()) return response()->json(['status' => 'error', 'msg' => 'Código expirado']);
        if ($promo->used_count >= $promo->max_uses) return response()->json(['status' => 'error', 'msg' => 'Código agotado']);

        // ¿Ya lo usó?
        $used = DB::table('promo_code_usages')->where('customer_id', $client->id)->where('promo_code_id', $promo->id)->exists();
        if ($used) return response()->json(['status' => 'error', 'msg' => 'Ya canjeaste este código']);

        // Aplicar
        DB::transaction(function() use ($client, $promo) {
            DB::table('customers')->where('id', $client->id)->increment('points', $promo->points);
            DB::table('promo_codes')->where('id', $promo->id)->increment('used_count');
            DB::table('promo_code_usages')->insert([
                'customer_id' => $client->id,
                'promo_code_id' => $promo->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            // Historial
            DB::table('point_histories')->insert([
                'customer_id' => $client->id,
                'type' => 'earn',
                'points' => $promo->points,
                'description' => "Código Promo: " . $promo->code,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        });

        return response()->json(['status' => 'success', 'msg' => "¡Ganaste {$promo->points} puntos!"]);
    }
}