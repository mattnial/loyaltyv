<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoyaltySettingsSeeder extends Seeder
{
    public function run()
    {
        // Limpiamos configuración vieja si hubiera
        DB::table('loyalty_settings')->truncate();

        // INSERTAMOS LA CONFIGURACIÓN (Puedes cambiar los números aquí)
        DB::table('loyalty_settings')->insert([
            'points_per_payment' => 100, // 100 puntos por mes pagado
            'payment_start_day' => 1,    // La racha cuenta si paga del 1...
            'payment_end_day' => 10,     // ... al 10 de cada mes
            'double_points_start' => 1,  // Puntos dobles si paga del 1...
            'double_points_end' => 5,    // ... al 5
            'points_birthday' => 500,    // Regalo de cumpleaños
            'points_anniversary' => 1000,// Regalo de aniversario
            'points_christmas' => 200,   // Regalo de Navidad
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}