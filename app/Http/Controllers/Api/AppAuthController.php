<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // O 'Client', depende de cómo se llame tu modelo de clientes

class AppAuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validar datos
        $request->validate([
            'cedula' => 'required',
            'password' => 'required'
        ]);

        // 2. Intentar Login (Ajusta 'cedula' si tu campo se llama 'username' o 'dni')
        // Si tu sistema usa Hash::make en los passwords, esto funciona directo.
        if (Auth::attempt(['cedula' => $request->cedula, 'password' => $request->password])) {
            
            $user = Auth::user();
            
            // 3. Crear Token (Si usas Sanctum) o simplemente devolver éxito
            // $token = $user->createToken('VilcanetApp')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Bienvenido',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name, // O $user->fullname
                    'cedula' => $user->cedula,
                    'email' => $user->email,
                    'plan' => 'Fibra 500M (Ejemplo)', // Puedes sacar esto de la relación de planes
                ],
                // 'token' => $token 
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Credenciales incorrectas'
        ], 401);
    }
}