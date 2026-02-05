<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\PointHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Para registrar errores si ocurren

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
        $customer->notify(new \App\Notifications\LoyaltyNotification(
            '¡Recibiste Puntos!',
            "Se han acreditado {$request->points} puntos a tu cuenta. Motivo: {$request->description}",
            'points_earned'
        ));

        // ---------------------------------------------------------
        // C. NOTIFICACIÓN PUSH A GOOGLE (FIREBASE HTTP v1) - NUEVO
        // ---------------------------------------------------------
        if ($customer->fcm_token) {
            try {
                // 1. Cargar el archivo JSON de credenciales
                // Asegúrate que el archivo esté en: storage/app/firebase_credentials.json
                $credentialsPath = storage_path('app/firebase_credentials.json');
                
                if (!file_exists($credentialsPath)) {
                    throw new \Exception("No se encontró el archivo firebase_credentials.json en storage/app/");
                }

                $credentials = json_decode(file_get_contents($credentialsPath), true);
                
                // 2. Generar el Token JWT manualmente (Sin depender de librerías externas complejas)
                $now = time();
                $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
                $payload = json_encode([
                    'iss' => $credentials['client_email'],
                    'sub' => $credentials['client_email'],
                    'aud' => 'https://oauth2.googleapis.com/token',
                    'iat' => $now,
                    'exp' => $now + 3600,
                    'scope' => 'https://www.googleapis.com/auth/firebase.messaging'
                ]);
                
                // Codificar en Base64 URL Safe
                $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
                $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
                
                // Firmar con la llave privada del JSON
                $signature = '';
                openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $credentials['private_key'], 'SHA256');
                $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

                // 3. Intercambiar el JWT por un Token de Acceso de Google
                $responseToken = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt
                ]);
                
                $accessToken = $responseToken->json()['access_token'];

                // 4. Enviar el Mensaje final a Firebase
                $projectId = $credentials['project_id'];
                
                Http::withToken($accessToken)->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                    'message' => [
                        'token' => $customer->fcm_token,
                        'notification' => [
                            'title' => '¡Recibiste Puntos! 🎁',
                            'body'  => "Te ganaste {$request->points} puntos nuevos.",
                        ],
                        'data' => [
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            'type' => 'points_update',
                            'points' => (string)$request->points
                        ]
                    ]
                ]);

            } catch (\Exception $e) {
                // Si falla el Push, lo guardamos en el log de Laravel pero NO detenemos el proceso
                Log::error("Error enviando notificación Push: " . $e->getMessage());
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