<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reward;

class RewardsSeeder extends Seeder
{
    public function run()
    {
        // Limpiar tabla antes de llenar
        Reward::truncate();

        $premios = [
            [
                'name' => '1 Mes de Internet Gratis',
                'description' => 'Disfruta de un mes completo de servicio sin costo.',
                'cost' => 5000,
                'stock' => 10,
                'image_url' => 'https://cdn-icons-png.flaticon.com/512/2362/2362308.png',
                'is_active' => true
            ],
            [
                'name' => 'Router Dual Band Gigabit',
                'description' => 'Mejora tu velocidad WiFi con este router de última generación.',
                'cost' => 3500,
                'stock' => 5,
                'image_url' => 'https://m.media-amazon.com/images/I/61s-k4iO9CL.jpg',
                'is_active' => true
            ],
            [
                'name' => 'Descuento 50% Factura',
                'description' => 'Paga solo la mitad en tu próxima factura.',
                'cost' => 1500,
                'stock' => 50,
                'image_url' => 'https://cdn-icons-png.flaticon.com/512/726/726476.png',
                'is_active' => true
            ],
            [
                'name' => 'Instalación Punto Adicional',
                'description' => 'Cableado para un nuevo punto de red en tu casa.',
                'cost' => 1000,
                'stock' => 20,
                'image_url' => 'https://cdn-icons-png.flaticon.com/512/900/900618.png',
                'is_active' => true
            ],
            [
                'name' => 'Camiseta Vilcanet',
                'description' => 'Camiseta oficial de la comunidad.',
                'cost' => 500,
                'stock' => 0, // AGOTADO (Para probar que sale rojo en la App)
                'image_url' => 'https://cdn-icons-png.flaticon.com/512/863/863684.png',
                'is_active' => true
            ]
        ];

        foreach ($premios as $premio) {
            Reward::create($premio);
        }
    }
}