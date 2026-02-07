<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // <--- AGREGADO
use Google\Auth\Credentials\ServiceAccountCredentials; // <--- AGREGADO
use Google\Auth\HttpHandler\HttpHandlerFactory; // <--- AGREGADO

// --- CONTROLADORES ---
use App\Http\Controllers\Controller;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\AdminTicketController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\OltController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\DivusInvoiceController;
use App\Http\Controllers\PaymentReportController;
use App\Http\Controllers\Admin\MonitorController;

// Controladores de Fidelidad
use App\Http\Controllers\Admin\LoyaltySettingsController;
use App\Http\Controllers\Admin\PointsController;
use App\Http\Controllers\Admin\RewardController;
use App\Http\Controllers\Admin\RedemptionController;
//POPUPS PARA LA APP
use App\Http\Controllers\Admin\PopupController;


// ====================================================
// 1. RUTAS PÚBLICAS Y PORTAL DE CLIENTES
// ====================================================

Route::get('/', function () { return view('welcome'); });

// Redirección inteligente al login
Route::get('/login', function () {
    return redirect()->route('portal.login');
})->name('login');

// Login de Clientes
Route::get('/portal/login', [CustomerAuthController::class, 'showLoginForm'])->name('portal.login');
Route::post('/portal/login', [CustomerAuthController::class, 'login'])->name('portal.login.submit');

// Área Privada del Cliente
Route::middleware('auth:customer')->prefix('portal')->group(function () {
    Route::get('/dashboard', [PortalController::class, 'dashboard'])->name('portal.dashboard');
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('portal.logout');
    Route::post('/password', [PortalController::class, 'updatePassword']);
    Route::post('/ticket', [PortalController::class, 'storeTicket']);
    Route::post('/payment/report', [PaymentReportController::class, 'store'])->name('portal.payment.report');
    
    // Facturas
    Route::get('/billing/divus-pdf/{pdfId}', [DivusInvoiceController::class, 'pdf'])->name('billing.pdf.download');
    Route::get('/billing/invoices/{customerId}', [DivusInvoiceController::class, 'list']);
});


// ====================================================
// 2. RUTAS DE ADMINISTRACIÓN (STAFF)
// ====================================================

// Login Administrativo
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Rutas JS para el monitor (pueden requerir auth según necesidad)
Route::get('/admin/monitor/data', [MonitorController::class, 'getLogs']);
Route::post('/admin/monitor/scan', [MonitorController::class, 'forceScan']);

// GRUPO PRINCIPAL DE ADMIN (Protegido con Auth)
// Todo aquí adentro tendrá el prefijo URL "admin/" y el prefijo de nombre "admin."
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // --- DASHBOARD GENERAL ---
    Route::get('/', function () {
        if (Auth::user()->isBilling()) return redirect()->route('admin.billing.index');
        return redirect()->route('admin.tickets');
    })->name('dashboard');

    // --- ÁREA TÉCNICA ---
    Route::middleware('role.technical')->group(function () {
        Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets');
        Route::post('/tickets/{id}', [AdminTicketController::class, 'update'])->name('tickets.update');
        Route::get('/monitor/{oltId}', [OltController::class, 'monitor']);
    });

    // --- ÁREA DE FACTURACIÓN ---
    Route::middleware('role.billing')->group(function () {
        Route::get('/payments', [BillingController::class, 'index'])->name('billing.index');
        Route::post('/payments/{id}/approve', [BillingController::class, 'approve'])->name('billing.approve');
        Route::post('/payments/{id}/reject', [BillingController::class, 'reject'])->name('billing.reject');
        
        Route::get('/subscriptions', [SubscriptionController::class, 'index']);
        Route::get('/subscriptions/toggle/{id}', [SubscriptionController::class, 'toggleStatus']);
    });

    // ==========================================
    // SISTEMA DE FIDELIDAD (LOYALTY)
    // ==========================================
    
    // 1. Configuración General
    Route::get('/fidelidad', [LoyaltySettingsController::class, 'index'])->name('loyalty.index');
    Route::post('/fidelidad', [LoyaltySettingsController::class, 'update'])->name('loyalty.update');

    // 2. Puntos Manuales
    Route::get('/fidelidad/manual', [PointsController::class, 'create'])->name('loyalty.manual');
    Route::post('/fidelidad/manual', [PointsController::class, 'store'])->name('loyalty.manual.store');

    // 3. Catálogo de Premios
    Route::get('/premios', [RewardController::class, 'index'])->name('rewards.index');
    Route::post('/premios', [RewardController::class, 'store'])->name('rewards.store');
    Route::post('/premios/{id}/toggle', [RewardController::class, 'toggleFeatured'])->name('rewards.toggle');
    Route::post('/premios/{id}/delete', [RewardController::class, 'destroy'])->name('rewards.destroy');

    // 4. Gestión de Pedidos / Canjes (Logística)
    Route::get('/pedidos', [RedemptionController::class, 'index'])->name('redemptions.index');
    Route::post('/pedidos/{id}/approve', [RedemptionController::class, 'approve'])->name('redemptions.approve');
    Route::post('/pedidos/{id}/complete', [RedemptionController::class, 'complete'])->name('redemptions.complete');
    Route::post('/pedidos/{id}/reject', [RedemptionController::class, 'reject'])->name('redemptions.reject');

    Route::get('/publicidad', [App\Http\Controllers\Admin\PopupController::class, 'index'])
        ->name('popups.index');

    Route::post('/publicidad', [App\Http\Controllers\Admin\PopupController::class, 'store'])
        ->name('popups.store');

    Route::post('/publicidad/{id}/toggle', [App\Http\Controllers\Admin\PopupController::class, 'toggle'])
        ->name('popups.toggle');

    Route::post('/publicidad/{id}/delete', [App\Http\Controllers\Admin\PopupController::class, 'destroy'])
        ->name('popups.destroy');

});
Route::post('/user/update-token', function (Request $request) {
    $request->validate([
        'token' => 'required|string'
    ]);

    $user = $request->user(); // Obtiene el usuario logueado
    $user->fcm_token = $request->token; // Asigna el token
    $user->save(); // Guarda en la BD

    return response()->json(['message' => 'Token actualizado correctamente']);
});

// ====================================================
// 3. HERRAMIENTAS DE DIAGNÓSTICO
// ====================================================

Route::get('/debug-divus', function () {
    $service = new \App\Services\DivusService();
    echo "<h1>Probando Conexión Divusware...</h1>";
    try {
        $ventas = $service->getLiveSales();
        echo "<h3>Resultado:</h3>Registros: " . count($ventas);
        if (count($ventas) > 0) { echo "<pre>"; print_r($ventas[0]); echo "</pre>"; }
    } catch (\Exception $e) {
        echo "❌ ERROR: " . $e->getMessage();
    }
});

// NUEVA RUTA: PRUEBA DE FIREBASE HTTP V1 (MODERNO)
Route::get('/test-fcm-v1', function () {
    // Token específico del emulador
    $tokenDevice = 'e5WPnNxzRJSDJGYaCAuMHq:APA91bEY8ITf4y7gyALh3pryR5Gcs3G5uI2F-L9sVMEyg1ebmieSdrDE9ys1CMIl2U7u1dOPBJ7e5m-JtfX9Egy1V1bDlX1YdPBmooZsDqsQqKRyWT1VMmg';
    
    // Archivo de credenciales JSON (Asegúrate que esté en storage/app/)
    $rutaJson = storage_path('app/firebase_credentials.json');

    // 1. Verificar archivo
    if (!file_exists($rutaJson)) {
        return response()->json([
            'error' => 'ARCHIVO NO ENCONTRADO',
            'mensaje' => "No encuentro el archivo JSON en: $rutaJson",
            'solucion' => 'Copia el archivo descargado de Google dentro de storage/app/ y llámalo firebase_credentials.json'
        ], 500);
    }

    try {
        // 2. Obtener Token de Acceso (OAuth 2.0)
        $json = json_decode(file_get_contents($rutaJson), true);
        $projectId = $json['project_id'];

        $credenciales = new ServiceAccountCredentials(
            'https://www.googleapis.com/auth/firebase.messaging',
            $rutaJson
        );
        
        $accessToken = $credenciales->fetchAuthToken(HttpHandlerFactory::build());
        $tokenValue = $accessToken['access_token'];

        // 3. Enviar Notificación (Formato HTTP v1)
        $payload = [
            'message' => [
                'token' => $tokenDevice,
                'notification' => [
                    'title' => '🚀 Prueba Exitosa (HTTP v1)',
                    'body'  => '¡Si ves esto con la App cerrada, Laravel ya funciona!',
                ],
                'data' => [
                    'tipo' => 'manual',
                    'puntos' => '50',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ]
            ]
        ];

        $response = Http::withToken($tokenValue)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $payload);

        return $response->json();

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'CRASH',
            'error' => $e->getMessage(),
            'linea' => $e->getLine()
        ], 500);
    }
});