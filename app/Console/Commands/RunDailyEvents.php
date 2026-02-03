<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RunDailyEvents extends Command
{
    protected $signature = 'vilcanet:daily-events';
    protected $description = 'Otorga puntos por cumpleaños, aniversario y navidad';

    public function handle()
    {
        $settings = DB::table('loyalty_settings')->first();
        if (!$settings) return;

        $hoy = Carbon::now();
        $esNavidad = ($hoy->month == 12 && $hoy->day == 25);

        $clientes = DB::table('customers')->get();

        foreach ($clientes as $c) {
            // 1. NAVIDAD
            if ($esNavidad) {
                $this->darPuntos($c->id, $settings->points_christmas, "Regalo de Navidad 🎄");
            }

            // 2. CUMPLEAÑOS
            if ($c->birth_date) {
                $cumple = Carbon::parse($c->birth_date);
                if ($cumple->month == $hoy->month && $cumple->day == $hoy->day) {
                    $this->darPuntos($c->id, $settings->points_birthday, "¡Feliz Cumpleaños! 🎂");
                }
            }

            // 3. ANIVERSARIO CONTRATO
            if ($c->contract_start_date) {
                $contrato = Carbon::parse($c->contract_start_date);
                if ($contrato->month == $hoy->month && $contrato->day == $hoy->day && $contrato->year != $hoy->year) {
                    $anios = $hoy->year - $contrato->year;
                    $this->darPuntos($c->id, $settings->points_anniversary, "Aniversario #$anios en Vilcanet 🎉");
                }
            }
        }
    }

    private function darPuntos($clientId, $points, $desc) {
        // Lógica para no repetir el mismo premio el mismo año (Opcional pero recomendado)
        // ... (Implementación simple aquí)
        DB::table('customers')->where('id', $clientId)->increment('points', $points);
        DB::table('point_histories')->insert([
            'customer_id' => $clientId, 'type' => 'earn', 'points' => $points, 
            'description' => $desc, 'created_at' => now(), 'updated_at' => now()
        ]);
    }
}