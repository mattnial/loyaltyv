<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reward;
use App\Models\Customer;
use App\Notifications\LoyaltyNotification;
use Illuminate\Support\Facades\Notification;

class RewardController extends Controller
{
    public function index()
    {
        $rewards = Reward::orderBy('is_featured', 'desc')->get();
        return view('admin.rewards.index', compact('rewards'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'points_cost' => 'required|integer|min:1', // En el formulario se llama points_cost
            'stock'       => 'required|integer|min:0',
            'image'       => 'nullable|image|max:2048',
            'description' => 'nullable|string'
        ]);

        // Mapeamos los datos manualmente para que coincidan con la BD
        $data = [
            'name'        => $request->name,
            'cost'        => $request->points_cost, // <--- AQUÍ ESTÁ LA CORRECCIÓN CLAVE
            'stock'       => $request->stock,
            'description' => $request->description,
            'is_featured' => $request->has('is_featured'),
            'is_active'   => true,
        ];

        // Subir Imagen
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('rewards', 'public');
        }

        $reward = Reward::create($data);

        // --- NOTIFICACIÓN MASIVA ---
        if ($request->has('notify_users')) {
            // Usamos chunk para procesar en grupos de 100 y evitar saturar memoria
            Customer::chunk(100, function ($customers) use ($reward) {
                Notification::send($customers, new LoyaltyNotification(
                    '¡Nuevo Premio Disponible! 🎁',
                    "Llegó '{$reward->name}' al catálogo. Canjéalo por solo {$reward->cost} puntos.", // Usamos $reward->cost
                    'new_reward'
                ));
            });
        }

        return back()->with('success', 'Premio creado correctamente.');
    }

    public function toggleFeatured($id)
    {
        $reward = Reward::findOrFail($id);
        $reward->is_featured = !$reward->is_featured;
        $reward->save();

        return back()->with('success', 'Estado destacado actualizado.');
    }

    public function destroy($id)
    {
        $reward = Reward::findOrFail($id);
        $reward->delete();
        return back()->with('success', 'Premio eliminado.');
    }
}