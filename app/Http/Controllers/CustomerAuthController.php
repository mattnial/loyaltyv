<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerAuthController extends Controller
{
    // 1. Mostrar el formulario de Login (GET)
    public function showLoginForm()
    {
        return view('portal.login');
    }

    // 2. Procesar el Login (POST)
    public function login(Request $request)
    {
        // Validamos que envíe datos
        $credentials = $request->validate([
            'identification' => 'required',
            'password' => 'required',
        ]);

        // Intentamos loguear usando el guard 'customer'
        // (Recuerda que configuramos que la 'password' sea la cédula por ahora)
        if (Auth::guard('customer')->attempt([
            'identification' => $request->identification, 
            'password' => $request->password
        ])) {
            $request->session()->regenerate();
            // Si entra, lo mandamos al Dashboard
            return redirect()->intended(route('portal.dashboard'));
        }

        // Si falla, lo devolvemos con error
        return back()->withErrors([
            'identification' => 'Cédula o contraseña incorrectos.',
        ])->withInput($request->only('identification'));
    }

    // 3. Cerrar Sesión (POST)
    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // ANTES DECÍA: return redirect()->route('login');
        // CAMBIAR POR ESTO: 👇
        return redirect()->route('portal.login'); 
    }
}