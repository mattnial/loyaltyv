<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LoyaltySetting; // Importante: Importar el modelo

class LoyaltySettingsController extends Controller
{
    // 1. Mostrar el Panel
    public function index()
    {
        // Busca la configuración o crea una vacía en memoria si no existe
        $settings = LoyaltySetting::firstOrNew([]);
        
        // Retorna la vista (asegúrate que el archivo index.blade.php exista en resources/views/admin/loyalty/)
        return view('admin.loyalty.index', compact('settings'));
    }

    // 2. Guardar Cambios
    public function update(Request $request)
    {
        // Validamos que los datos sean números correctos
        $data = $request->validate([
            'points_per_payment' => 'required|integer|min:1',
            'payment_start_day'  => 'required|integer|min:1|max:31',
            'payment_end_day'    => 'required|integer|min:1|max:31',
            'double_points_start'=> 'required|integer|min:1|max:31',
            'double_points_end'  => 'required|integer|min:1|max:31',
            'points_birthday'    => 'required|integer|min:0',
            'points_anniversary' => 'required|integer|min:0',
            'points_christmas'   => 'required|integer|min:0',
        ]);

        // Guardamos en la base de datos (ID 1 siempre)
        LoyaltySetting::updateOrCreate(['id' => 1], $data);

        return back()->with('success', '¡Configuración actualizada correctamente!');
    }
}