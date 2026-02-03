<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domains\Technical\Services\OltService;

class OltController extends Controller
{
    protected $oltService;

    public function __construct(OltService $oltService)
    {
        $this->oltService = $oltService;
    }

    public function checkOnu(Request $request, $oltId)
    {
        // Validamos que nos envíen el índice de la ONU
        $request->validate([
            'onu_index' => 'required|string' // Ej: "0/1/0:15"
        ]);

        try {
            $data = $this->oltService->getOnuStatus($oltId, $request->onu_index);
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Muestra el panel visual de monitoreo
     */
    public function monitor($oltId)
    {
        // 1. Obtenemos datos de prueba (Simulamos la ONU 15)
        // En el futuro, esto vendrá de un formulario o click en un cliente
        $onuIndex = "0/1/0:15"; 
        
        try {
            $status = $this->oltService->getOnuStatus($oltId, $onuIndex);
            
            // 2. Retornamos la VISTA (HTML) pasándole los datos
            return view('olt.monitor', [
                'olt_id' => $oltId,
                'onu' => $onuIndex,
                'data' => $status
            ]);
            
        } catch (\Exception $e) {
            return "Error conectando a OLT: " . $e->getMessage();
        }
    }
}