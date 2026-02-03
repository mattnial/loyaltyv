<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Inspiring;
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
// Definición de Comandos de Consola y Programación

// ==========================================
// 1. ESCANEO ENTRE SEMANA (Lun - Vie)
// ==========================================
// De 07:20 AM a 06:20 PM
Schedule::command('vilcanet:scan-payments')
         ->weekdays() // Solo Lunes a Viernes
         ->everyTenMinutes()
         ->between('7:20', '18:20')
         ->timezone('America/Guayaquil')
         ->withoutOverlapping(); // Evita que se monten si uno tarda mucho

// ==========================================
// 2. ESCANEO FIN DE SEMANA (Sab - Dom)
// ==========================================
// De 07:30 AM a 01:00 PM
Schedule::command('vilcanet:scan-payments')
         ->weekends() // Solo Sábado y Domingo
         ->everyTenMinutes()
         ->between('7:30', '13:00')
         ->timezone('America/Guayaquil')
         ->withoutOverlapping();

// ==========================================
// 3. EVENTOS ESPECIALES (Cumpleaños/Navidad)
// ==========================================
// Una sola vez al día, antes de abrir (7:00 AM)
Schedule::command('vilcanet:daily-events')
         ->dailyAt('07:00')
         ->timezone('America/Guayaquil');