<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Technical\Models\Olt;

class OltSeeder extends Seeder
{
    public function run(): void
    {
        Olt::create([
            'name' => 'OLT Huawei Principal',
            'ip_address' => '192.168.100.20',
            'driver' => 'huawei',
            'admin_user' => 'root',
            'admin_password' => 'admin123',
            'is_active' => true,
            'snmp_community' => 'public'
        ]);
    }
}