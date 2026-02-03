<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Billing\Models\Subscription;
use App\Domains\Billing\Models\Customer;
use App\Domains\Billing\Models\InternetPlan;
use App\Domains\Technical\Models\Olt;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buscamos los actores
        $juan = Customer::where('identification', '1102589634')->first(); // Juan Pérez
        $plan50 = InternetPlan::where('download_speed_kbps', 51200)->first(); // Plan 50M
        $olt = Olt::first(); // La OLT que creamos antes

        if ($juan && $plan50 && $olt) {
            // 2. Creamos la Suscripción
            Subscription::create([
                'customer_id' => $juan->id,
                'internet_plan_id' => $plan50->id,
                'olt_id' => $olt->id,
                
                // Datos Técnicos
                'service_ip' => '10.20.30.5', // IP Privada Típica de ISP
                'pppoe_user' => 'juanperez',
                'pppoe_password' => '123456',
                
                // Datos Hardware
                'onu_serial' => 'HWTC12345678', // Serial Huawei Típico
                'onu_index' => '0/1/0:5', // Puerto Físico
                
                // Estado
                'status' => 'active',
                'installation_date' => now()->subMonths(2), // Cliente desde hace 2 meses
                'wifi_ssid' => 'Familia Perez WiFi',
                'wifi_password' => 'perez2026'
            ]);
        }
    }
}