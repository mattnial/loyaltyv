<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    // 1. Mostrar formulario (GET)
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    // 2. Procesar Login (POST)
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Usamos el guard por defecto 'web' (que mira la tabla 'users')
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Redirección inteligente basada en el rol
            $user = Auth::user();
            
            if ($user->role === 'billing') {
                return redirect()->route('admin.billing.index');
            }
            
            if ($user->role === 'technical') {
                return redirect()->route('admin.tickets');
            }

            // Super Admin va a tickets por defecto (o a un dashboard general si tuviéramos)
            return redirect()->route('admin.tickets');
        }

        return back()->withErrors([
            'email' => 'Credenciales incorrectas o acceso no autorizado.',
        ]);
    }

    // 3. Logout Admin
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}