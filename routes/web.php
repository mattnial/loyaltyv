<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// --- IMPORTACIONES ---
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
use App\Http\Controllers\Admin\LoyaltySettingsController;
// 1. RUTAS PÚBLICAS
Route::get('/', function () { return view('welcome'); });

// Redirección inteligente
Route::get('/login', function () {
    return redirect()->route('portal.login');
})->name('login');

// 2. PORTAL DE CLIENTES
Route::get('/portal/login', [CustomerAuthController::class, 'showLoginForm'])->name('portal.login');
Route::post('/portal/login', [CustomerAuthController::class, 'login'])->name('portal.login.submit');
// Puedes proteger esto con middleware 'auth' si tienes login de admin
Route::get('/monitor-pagos', [MonitorController::class, 'index']);
//JS
Route::get('/admin/monitor/data', [MonitorController::class, 'getLogs']);
Route::post('/admin/monitor/scan', [MonitorController::class, 'forceScan']);
//
Route::middleware('auth:customer')->prefix('portal')->group(function () {
    Route::get('/dashboard', [PortalController::class, 'dashboard'])->name('portal.dashboard');
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('portal.logout');
    Route::post('/password', [PortalController::class, 'updatePassword']);
    Route::post('/ticket', [PortalController::class, 'storeTicket']);
    Route::post('/payment/report', [PaymentReportController::class, 'store'])->name('portal.payment.report');
    Route::get('/billing/divus-pdf/{pdfId}', [DivusInvoiceController::class, 'pdf'])->name('billing.pdf.download');
    Route::get('/billing/invoices/{customerId}', [DivusInvoiceController::class, 'list']);
});

// 3. AREA ADMINISTRATIVA (STAFF)
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::prefix('admin')->middleware('auth')->group(function () {

    // Dashboard General (Redirige)
    Route::get('/', function () {
        if (Auth::user()->isBilling()) return redirect()->route('admin.billing.index');
        return redirect()->route('admin.tickets');
    })->name('admin.dashboard');

    // GRUPO TÉCNICO (Usando el alias que creamos)
    Route::middleware('role.technical')->group(function () {
        Route::get('/tickets', [AdminTicketController::class, 'index'])->name('admin.tickets');
        Route::post('/tickets/{id}', [AdminTicketController::class, 'update'])->name('admin.tickets.update');
        Route::get('/monitor/{oltId}', [OltController::class, 'monitor']);
    });

    // GRUPO FACTURACIÓN (Usando el alias que creamos)
    Route::middleware('role.billing')->group(function () {
        Route::get('/payments', [BillingController::class, 'index'])->name('admin.billing.index');
        Route::post('/payments/{id}/approve', [BillingController::class, 'approve'])->name('admin.billing.approve');
        Route::post('/payments/{id}/reject', [BillingController::class, 'reject'])->name('admin.billing.reject');
        Route::get('/subscriptions', [SubscriptionController::class, 'index']);
        Route::get('/subscriptions/toggle/{id}', [SubscriptionController::class, 'toggleStatus']);
    });

});
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    
    // Ruta para VER el panel
    Route::get('/fidelidad', [LoyaltySettingsController::class, 'index'])
        ->name('loyalty.index');

    // Ruta para GUARDAR cambios
    Route::post('/fidelidad', [LoyaltySettingsController::class, 'update'])
        ->name('loyalty.update');
});
// RUTA TEMPORAL DE DIAGNÓSTICO
Route::get('/debug-divus', function () {
    // 1. Instanciar el servicio
    $service = new \App\Services\DivusService();
    
    // 2. Intentar traer ventas
    echo "<h1>Probando Conexión Divusware desde Laravel...</h1>";
    
    try {
        $ventas = $service->getLiveSales();
        
        echo "<h3>Resultado:</h3>";
        echo "Registros encontrados: " . count($ventas) . "<br>";
        
        if (count($ventas) > 0) {
            echo "<pre>";
            print_r($ventas[0]); // Muestra el primer pago encontrado
            echo "</pre>";
        } else {
            echo "⚠️ <b>Lista vacía.</b> Posibles causas:<br>";
            echo "- Login falló (revisar cookie).<br>";
            echo "- URL incorrecta.<br>";
            echo "- No hay ventas hoy.<br>";
        }
    } catch (\Exception $e) {
        echo "❌ <b>ERROR FATAL:</b> " . $e->getMessage();
    }
    
    // 3. Verificar Archivo Cookie
    $cookiePath = storage_path('app/divus_cookies.txt');
    echo "<hr><h3>Estado de Cookies:</h3>";
    echo "Ruta: " . $cookiePath . "<br>";
    
    if (file_exists($cookiePath)) {
        echo "✅ El archivo existe.<br>";
        echo "Contenido: <textarea style='width:100%; height:100px'>" . file_get_contents($cookiePath) . "</textarea>";
    } else {
        echo "❌ <b>EL ARCHIVO NO EXISTE.</b> Problema de permisos en storage/app.";
    }
});