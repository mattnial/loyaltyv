<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;

class FirebaseService
{
    public static function send($token, $title, $body, $data = [])
    {
        if (!$token) return;

        try {
            // 1. Cargar credenciales
            $credentialsPath = storage_path('app/firebase_credentials.json');
            if (!file_exists($credentialsPath)) {
                Log::error("FCM: No se encontró el JSON de credenciales.");
                return;
            }

            // 2. Autenticación Google (OAuth 2.0)
            $json = json_decode(file_get_contents($credentialsPath), true);
            $projectId = $json['project_id'];

            $credentials = new ServiceAccountCredentials(
                'https://www.googleapis.com/auth/firebase.messaging',
                $credentialsPath
            );
            
            $accessToken = $credentials->fetchAuthToken(HttpHandlerFactory::build());
            $tokenValue = $accessToken['access_token'];

            // 3. Construir Mensaje
            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'data' => array_merge([
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'sound' => 'default',
                        'status' => 'done'
                    ], $data)
                ]
            ];

            // 4. Enviar
            Http::withToken($tokenValue)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $payload);

            Log::info("FCM: Notificación enviada a token: " . substr($token, 0, 10) . "...");

        } catch (\Exception $e) {
            Log::error("FCM Error: " . $e->getMessage());
        }
    }
}