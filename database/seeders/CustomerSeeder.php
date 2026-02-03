<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Billing\Models\Customer;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // Cliente 1: Residencial
        Customer::create([
            'identification' => '1102589634',
            'first_name' => 'Juan',
            'last_name' => 'Pérez Loja',
            'email' => 'juan.perez@gmail.com',
            'phone' => '0991234567',
            'address' => 'Av. Orillas del Zamora y 24 de Mayo, Casa verde de 2 pisos',
            'coordinates' => '-3.99313, -79.20422', // Coordenadas reales de Loja
        ]);

        // Cliente 2: Negocio (RUC)
        Customer::create([
            'identification' => '1104567890001',
            'first_name' => 'Restaurante',
            'last_name' => 'El Lojano',
            'email' => 'facturas@ellojano.com',
            'phone' => '0987654321',
            'address' => 'Calle Bolívar y Rocafuerte',
            'coordinates' => '-3.99500, -79.20000',
        ]);
    }
}