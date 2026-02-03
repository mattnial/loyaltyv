<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domains\Billing\Services\DivusBridgeService;
use App\Domains\Billing\Models\Customer;
use Illuminate\Support\Facades\Log;

class DivusInvoiceController extends Controller
{
    protected $divus;

    public function __construct(DivusBridgeService $divus)
    {
        $this->divus = $divus;
    }

    // --- AQUÍ ESTABA EL ERROR: TE FALTABA EL NOMBRE DE ESTA FUNCIÓN ---
    public function list($customerId)
    {
        $customer = Customer::findOrFail($customerId);
        
        // Llamamos al puente para que busque las facturas
        try {
            $invoices = $this->divus->getInvoicesByCedula($customer->identification);
        } catch (\Exception $e) {
            $invoices = [];
        }
        
        return view('subscriptions.invoices', compact('customer', 'invoices'));
    }

    /**
     * Descarga directa del PDF sin almacenamiento local.
     */
    public function pdf($pdfId)
    {
        try {
            // 1. Llamada al servicio (No guarda en disco, todo en RAM)
            $pdfContent = $this->divus->getInvoicePdf($pdfId);

            // 2. Generamos el nombre del archivo para el usuario
            $fileName = 'Factura-Vilcanet-' . $pdfId . '.pdf';

            // 3. Stream directo al navegador
            return response()->streamDownload(function () use ($pdfContent) {
                echo $pdfContent;
            }, $fileName, [
                'Content-Type' => 'application/pdf',
            ]);

        } catch (\Exception $e) {
            // 4. Manejo de Excepciones
            Log::error("Fallo descarga PDF {$pdfId}: " . $e->getMessage());

            // Redirigimos atrás con un mensaje de error (asegúrate de mostrar session('error') en tu vista)
            return back()->with('error', 'No se pudo descargar la factura. Intente más tarde.');
        }
    }
}