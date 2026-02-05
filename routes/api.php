<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Customer; // Usamos el modelo para poder crear tokens

// Controladores
use App\Http\Controllers\Api\ClubController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Admin\MonitorController;
use App\Models\Popup;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ========================================================================
// 1. RUTAS PÚBLICAS (Login y Noticias generales)
// ========================================================================

// Login de la App (Genera Token para usar en rutas protegidas)
Route::post('/login', function (Request $request) {
    $request->validate([
        'cedula' => 'required',
        'password' => 'required'
    ]);

    $customer = Customer::where('identification', $request->cedula)->first();

    if ($customer && Hash::check($request->password, $customer->password)) {
        // Borramos tokens viejos para limpieza
        $customer->tokens()->delete();
        
        // Creamos nuevo token
        $token = $customer->createToken('movil-app')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'token' => $token, // IMPORTANTE: La app debe guardar esto
            'user' => [
                'id' => $customer->id,
                'name' => $customer->first_name . ' ' . $customer->last_name,
                'email' => $customer->email ?? 'Sin correo',
                'plan' => $customer->plan ?? 'Estándar',
                'points' => $customer->points ?? 0,
                'streak' => $customer->streak_count ?? 0
            ]
        ]);
    }

    return response()->json(['status' => 'error', 'msg' => 'Credenciales incorrectas'], 401);
});

// Noticias (Pueden ser públicas)
Route::get('/news', [NewsController::class, 'index']);


// ========================================================================
// 2. RUTAS PROTEGIDAS (Requieren Token)
// ========================================================================
Route::middleware('auth:sanctum')->group(function () {
    
    // Perfil del Usuario
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/save-token', function (Request $request) {
    $request->user()->update(['fcm_token' => $request->token]);
    return response()->json(['message' => 'Token guardado']);
    });
    // Club de Fidelidad (Catálogo, Canje e Historial)
    Route::get('/club/data', [ClubController::class, 'index']); 
    Route::post('/club/redeem', [ClubController::class, 'redeem']); 
    Route::get('/club/history', [ClubController::class, 'history']); // Tu nueva ruta de historial
    Route::get('/notifications', function (Request $request) {
    // Devuelve las notificaciones del usuario logueado
    return $request->user()->notifications()->take(20)->get();
});

});

Route::get('/active-popup', function () {
    // Traemos TODAS las activas, no solo la primera
    $popups = \App\Models\Popup::where('is_active', true)->latest()->get();

    if ($popups->count() > 0) {
        return response()->json([
            'has_popup' => true,
            // Mapeamos para enviar una lista limpia
            'popups' => $popups->map(function($p) {
                return [
                    'id' => $p->id,
                    'title' => $p->title,
                    'image' => asset('storage/' . $p->image_path)
                ];
            })
        ]);
    } else {
        return response()->json([
            'has_popup' => false
        ]);
    }
});

// ========================================================================
// 3. RUTAS DE ADMINISTRACIÓN / MONITOR (Backend)
// ========================================================================
// Estas suelen llamarse desde el panel web admin, no desde la app móvil.
Route::get('/admin/monitor/data', [MonitorController::class, 'getLogs']);
Route::post('/admin/monitor/scan', [MonitorController::class, 'forceScan']);