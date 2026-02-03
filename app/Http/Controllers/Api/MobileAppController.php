<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Domains\Billing\Models\Customer;
use App\Domains\Billing\Services\DivusBridgeService;
use App\Domains\Billing\Models\Ticket;
use Illuminate\Support\Facades\Hash;

class MobileAppController extends Controller
{
    // 1. LOGIN DESDE EL CELULAR 📲
    public function login(Request $request)
    {
        $request->validate([
            'identification' => 'required',
            'password' => 'required',
        ]);

        $customer = Customer::where('identification', $request->identification)->first();

        // Verificamos contraseña (ahora que ya están reseteadas y encriptadas)
        if (! $customer || ! Hash::check($request->password, $customer->password)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        // Borramos tokens viejos para no acumular basura
        $customer->tokens()->delete();

        // Creamos el nuevo "Pasaporte" para el celular
        $token = $customer->createToken('flutter-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'customer_name' => $customer->first_name,
            'message' => 'Login Exitoso'
        ]);
    }

    // 2. DATOS DEL HOME (Perfil + Deuda) 🏠
    public function home(Request $request)
    {
        $customer = $request->user(); // El usuario se detecta automático por el token
        
        // Traemos facturas de la nube
        $divus = new DivusBridgeService();
        try {
            $invoices = $divus->getInvoicesByCedula($customer->identification);
        } catch (\Exception $e) {
            $invoices = [];
        }

        // Calculamos deuda total
        $totalDeuda = 0;
        foreach ($invoices as $inv) {
            if (str_contains(strtoupper($inv['estado']), 'PENDIENTE')) {
                 $monto = floatval(str_replace(['$', ','], '', $inv['monto']));
                 $totalDeuda += $monto;
            }
        }

        return response()->json([
            'client' => [
                'name' => $customer->first_name . ' ' . $customer->last_name,
                'plan' => $customer->subscription->plan->name ?? 'Plan Básico',
                'ip'   => $customer->subscription->service_ip ?? '---',
                'status' => $customer->status,
            ],
            'debt' => number_format($totalDeuda, 2),
            'last_invoices' => array_slice($invoices, 0, 3) // Mandamos solo las ultimas 3 para el home
        ]);
    }

    // 3. LISTADO COMPLETO DE FACTURAS 📄
    public function invoices(Request $request)
    {
        $divus = new DivusBridgeService();
        try {
            $invoices = $divus->getInvoicesByCedula($request->user()->identification);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error conectando a facturación'], 500);
        }
        return response()->json($invoices);
    }

    // 4. LISTADO DE TICKETS 🎫
    public function tickets(Request $request)
    {
        $tickets = Ticket::where('customer_id', $request->user()->id)
                         ->orderBy('created_at', 'desc')
                         ->get();
        return response()->json($tickets);
    }

    // 5. CREAR TICKET DESDE LA APP 🛠️
    public function storeTicket(Request $request)
    {
        $request->validate([
            'subject' => 'required|string',
            'description' => 'required|string',
        ]);

        $ticket = Ticket::create([
            'customer_id' => $request->user()->id,
            'subject' => $request->subject,
            'description' => $request->description,
            'status' => 'open',
            'priority' => 'medium'
        ]);

        return response()->json(['message' => 'Ticket creado', 'ticket' => $ticket]);
    }

    // 6. LOGOUT 🚪
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada']);
    }
}