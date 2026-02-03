<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class MonitorController extends Controller
{
    // 1. Mostrar la Vista HTML
    public function index()
    {
        return view('admin.monitor');
    }

    // 2. API: Obtener los últimos logs (AJAX)
    public function getLogs()
    {
        // Traemos los últimos 50 pagos registrados
        $logs = DB::table('payment_logs')
                  ->orderBy('payment_date', 'desc')
                  ->limit(50)
                  ->get();

        // Formateamos para que se vea bonito en JS
        $data = $logs->map(function($log) {
            return [
                'cedula' => $log->cedula,
                'monto' => $log->amount,
                'fecha' => Carbon::parse($log->payment_date)->format('d/m/Y H:i:s'),
                'estado' => $log->is_processed ? 'Procesado (Puntos Asignados)' : 'Pendiente/Error',
                'color' => $log->is_processed ? 'text-green-600' : 'text-gray-500'
            ];
        });

        return response()->json([
            'status' => 'ok', 
            'logs' => $data,
            'last_check' => now()->format('H:i:s')
        ]);
    }

    // 3. API: Forzar Escaneo (Botón Manual)
    public function forceScan()
    {
        // 1. Aumentar límites
        set_time_limit(300); 
        ini_set('memory_limit', '512M');
        ini_set('display_errors', 1); // Forzar mostrar errores PHP nativos

        try {
            // 2. Loguear inicio
            \Illuminate\Support\Facades\Log::info("Iniciando escaneo manual desde Monitor...");

            // 3. Ejecutar comando y capturar buffer
            \Illuminate\Support\Facades\Artisan::call('vilcanet:scan-payments');
            $output = \Illuminate\Support\Facades\Artisan::output();

            // 4. Verificar si salió vacío
            if (empty(trim($output))) {
                return response()->json([
                    'status' => 'error',
                    'msg' => 'El comando se ejecutó pero no devolvió nada. Revisa storage/logs/laravel.log'
                ], 500);
            }

            return response()->json([
                'status' => 'ok',
                'msg' => 'Escaneo finalizado.',
                'details' => $output
            ]);

        } catch (\Throwable $e) {
            // 5. CAPTURAR CUALQUIER ERROR (Incluso de sintaxis)
            \Illuminate\Support\Facades\Log::error("Error en Monitor: " . $e->getMessage());
            
            return response()->json([
                'status' => 'error', 
                // Aquí enviamos 'msg' que es lo que espera tu JS
                'msg' => 'CRASH: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine()
            ], 500);
        }
    }
}