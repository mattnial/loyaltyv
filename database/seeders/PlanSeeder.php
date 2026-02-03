<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Billing\Models\InternetPlan;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        // Plan Económico
        InternetPlan::create([
            'name' => 'Plan Residencial 50M',
            'price' => 19.99,
            'download_speed_kbps' => 50 * 1024, // 51200
            'upload_speed_kbps' => 25 * 1024,
            'mikrotik_profile_name' => 'plan_50m_residencial'
        ]);

        // Plan Estándar
        InternetPlan::create([
            'name' => 'Plan Familia 100M',
            'price' => 24.99,
            'download_speed_kbps' => 100 * 1024,
            'upload_speed_kbps' => 50 * 1024,
            'mikrotik_profile_name' => 'plan_100m_familia'
        ]);

        // Plan Gamer
        InternetPlan::create([
            'name' => 'Plan Gamer Pro 300M',
            'price' => 39.99,
            'download_speed_kbps' => 300 * 1024,
            'upload_speed_kbps' => 300 * 1024, // Simétrico
            'mikrotik_profile_name' => 'plan_300m_gamer'
        ]);
    }
}