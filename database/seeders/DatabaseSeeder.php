<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // El orden es CRÍTICO:
        // 1. Infraestructura (OLT)
        $this->call(OltSeeder::class);
        
        // 2. Productos (Planes)
        $this->call(PlanSeeder::class);
        
        // 3. Clientes
        $this->call(CustomerSeeder::class);
        
        // 4. Ventas (Suscripciones) - Este falla si los anteriores no existen
        $this->call(SubscriptionSeeder::class);
    }
}