<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Popup;
use Illuminate\Support\Facades\Storage;

class PopupController extends Controller
{
    public function index()
    {
        $popups = Popup::latest()->get();
        return view('admin.popups.index', compact('popups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048', // Max 2MB
        ]);

        $path = $request->file('image')->store('popups', 'public');

        // Desactivar anteriores si quieres que solo salga uno a la vez (Opcional)
        // Popup::query()->update(['is_active' => false]);

        Popup::create([
            'title' => $request->title,
            'image_path' => $path,
            'is_active' => true
        ]);

        return back()->with('success', 'Publicidad subida correctamente.');
    }

    public function toggle($id)
    {
        $popup = Popup::findOrFail($id);
        $popup->is_active = !$popup->is_active;
        $popup->save();
        return back()->with('success', 'Estado actualizado.');
    }

    public function destroy($id)
    {
        $popup = Popup::findOrFail($id);
        Storage::disk('public')->delete($popup->image_path);
        $popup->delete();
        return back()->with('success', 'Publicidad eliminada.');
    }
}