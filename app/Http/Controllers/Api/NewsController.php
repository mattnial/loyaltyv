<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index()
    {
        // Traer noticias activas, ordenadas por el número de 'orden'
        $news = News::where('is_active', true)
                    ->orderBy('order', 'asc')
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'title' => $item->title,
                            'description' => $item->description,
                            // Aseguramos que la imagen tenga la URL completa
                            'image' => asset($item->image_url),
                            'action_url' => $item->action_url,
                            'is_popup' => (bool) $item->is_popup, // ¿Es alerta flotante?
                        ];
                    });

        return response()->json([
            'status' => 'success',
            'data' => $news
        ]);
    }
}