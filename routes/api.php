<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Api\ClubController;
// IMPORTANTE: Agregamos el controlador del Monitor que creamos
use App\Http\Controllers\Admin\MonitorController;
use App\Http\Controllers\Api\NewsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ========================================================================
// 1. RUTAS DEL CLIENTE (APP MÓVIL)
// ========================================================================

// Login
Route::post('/login', function (Request $request) {
    $cedulaInput = $request->input('cedula');
    $passwordInput = $request->input('password');

    if (!$cedulaInput || !$passwordInput) {
        return response()->json(['status' => 'error', 'msg' => 'Faltan datos'], 400);
    }

    $client = DB::table('customers')->where('identification', $cedulaInput)->first();

    if ($client) {
        if (Hash::check($passwordInput, $client->password)) {
            return response()->json([
                'status' => 'success',
                'user' => [
                    'id' => $client->id,
                    'name' => $client->first_name . ' ' . $client->last_name,
                    'email' => $client->email ?? 'Sin correo',
                    'cedula' => $client->identification,
                    'phone' => $client->phone ?? '',
                    'address' => $client->address ?? '',
                    // NUEVOS CAMPOS PARA LA APP
                    'plan' => $client->plan ?? 'Estándar', // <--- IMPORTANTE
                    'points' => $client->points ?? 0,
                    'streak' => $client->streak_count ?? 0
                ]
            ]);
        }
    }
    return response()->json(['status' => 'error', 'msg' => 'Credenciales incorrectas'], 401);
});

// Club de Puntos (Catálogo y Canje)
Route::get('/club/data', [ClubController::class, 'index']);
Route::post('/club/redeem', [ClubController::class, 'redeem']);
Route::get('/news', [NewsController::class, 'index']);

// ========================================================================
// 2. RUTAS DEL ADMINISTRADOR (MONITOR DE PAGOS)
// ========================================================================

// Obtener datos para la tabla
Route::get('/admin/monitor/data', [MonitorController::class, 'getLogs']);

// Forzar escaneo manual (El botón que daba error)
Route::post('/admin/monitor/scan', [MonitorController::class, 'forceScan']);