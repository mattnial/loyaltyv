<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\PointHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

// --- LIBRERÍAS DE GOOGLE (Las mismas que usamos en el test) ---
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;

class PointsController extends Controller
{
    // 1. Mostrar formulario de asignación manual
    public function create()
    {
        // Traemos clientes para el select
        $customers = Customer::select('id', 'first_name', 'last_name', 'identification', 'points')->get();
        return view('admin.loyalty.manual', compact('customers'));
    }

    // 2. Guardar los puntos y NOTIFICAR
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'points'      => 'required|integer|min:1',
            'description' => 'required|string|max:255',
        ]);

        $customer = Customer::findOrFail($request->customer_id);

        // A. Crear registro en el historial
        PointHistory::create([
            'customer_id' => $customer->id,
            'user_id'     => Auth::id(),
            'type'        => 'earn',
            'points'      => $request->points,
            'description' => $request->description,
        ]);

        // B. Notificación Interna (Base de Datos / Campanita dentro de la App)
        // (Mantenemos esto intacto porque es útil para el historial dentro de la app)
        try {
            $customer->notify(new \App\Notifications\LoyaltyNotification(
                '¡Recibiste Puntos!',
                "Se han acreditado {$request->points} puntos a tu cuenta. Motivo: {$request->description}",
                'points_earned'
            ));
        } catch (\Exception $e) {
            Log::error("Error notificación interna: " . $e->getMessage());
        }

        // ---------------------------------------------------------
        // C. NOTIFICACIÓN PUSH A GOOGLE (FIREBASE HTTP v1)
        // ---------------------------------------------------------
        // Usamos la librería oficial que ya probamos y funciona
        if ($customer->fcm_token) {
            try {
                // 1. Ubicación del archivo
                $rutaJson = storage_path('app/firebase_credentials.json');
                
                if (file_exists($rutaJson)) {
                    // 2. Obtener Credenciales y Project ID
                    $json = json_decode(file_get_contents($rutaJson), true);
                    $projectId = $json['project_id'];

                    $credenciales = new ServiceAccountCredentials(
                        'https://www.googleapis.com/auth/firebase.messaging',
                        $rutaJson
                    );
                    
                    // 3. Generar Token de Acceso (Bearer)
                    $accessToken = $credenciales->fetchAuthToken(HttpHandlerFactory::build());
                    $tokenValue = $accessToken['access_token'];

                    // 4. Configurar el Mensaje (CON 'notification' PARA BACKGROUND)
                    $payload = [
                        'message' => [
                            'token' => $customer->fcm_token,
                            'notification' => [
                                'title' => '¡Has recibido Puntos! 🎁',
                                'body'  => "Te ganaste {$request->points} puntos. Motivo: {$request->description}",
                            ],
                            'data' => [
                                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                                'type'         => 'points_update',
                                'points'       => (string)$request->points,
                                'description'  => $request->description
                            ]
                        ]
                    ];

                    // 5. Enviar a Google
                    Http::withToken($tokenValue)
                        ->withHeaders(['Content-Type' => 'application/json'])
                        ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $payload);
                        
                    Log::info("Notificación Push enviada a usuario {$customer->id}");
                } else {
                    Log::error("No se encontró firebase_credentials.json para enviar Push");
                }

            } catch (\Exception $e) {
                // Si falla el Push, solo lo anotamos en el log para no detener el sistema
                Log::error("Error enviando Push FCM v1: " . $e->getMessage());
            }
        }
        // ---------------------------------------------------------
        // FIN DEL BLOQUE FIREBASE
        // ---------------------------------------------------------

        // D. Sumar puntos al saldo del cliente
        $customer->increment('points', $request->points);

        return back()->with('success', "✅ Se asignaron {$request->points} puntos a {$customer->first_name} correctamente.");
    }
}