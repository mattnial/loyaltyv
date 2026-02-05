<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LoyaltySetting;
use App\Models\StreakMilestone; // <--- Importamos el modelo que acabas de crear

class LoyaltySettingsController extends Controller
{
    public function index()
    {
        $settings = LoyaltySetting::firstOrNew([]);
        
        // Traemos los hitos ordenados (si la tabla existe y tiene datos)
        try {
            $milestones = StreakMilestone::orderBy('months_required', 'asc')->get();
        } catch (\Exception $e) {
            $milestones = []; // Si la tabla no existe aún, evitamos error
        }
        
        return view('admin.loyalty.index', compact('settings', 'milestones'));
    }

    public function update(Request $request)
    {
        // 1. Validar
        $data = $request->validate([
            'points_per_payment' => 'required|integer|min:1',
            'payment_start_day'  => 'required|integer|min:1|max:31',
            'payment_end_day'    => 'required|integer|min:1|max:31',
            'double_points_start'=> 'required|integer|min:1|max:31',
            'double_points_end'  => 'required|integer|min:1|max:31',
            'points_birthday'    => 'required|integer|min:0',
            'points_anniversary' => 'required|integer|min:0',
            'points_christmas'   => 'required|integer|min:0',
            // Validación de array de hitos
            'milestones'         => 'nullable|array',
            'milestones.*.months'=> 'required|integer|min:1',
            'milestones.*.points'=> 'required|integer|min:1',
        ]);

        // 2. Guardar Configuración General
        // Quitamos 'milestones' del array para que no falle al guardar LoyaltySetting
        $settingsData = collect($data)->except('milestones')->toArray();
        LoyaltySetting::updateOrCreate(['id' => 1], $settingsData);

        // 3. Guardar Hitos de Racha
        // Borramos los viejos y creamos los nuevos (estrategia simple de sincronización)
        if ($request->has('milestones')) {
            StreakMilestone::truncate(); // Limpia la tabla
            foreach ($request->milestones as $milestone) {
                StreakMilestone::create([
                    'months_required' => $milestone['months'],
                    'bonus_points'    => $milestone['points']
                ]);
            }
        }

        return back()->with('success', '¡Configuración y Hitos actualizados correctamente!');
    }
}