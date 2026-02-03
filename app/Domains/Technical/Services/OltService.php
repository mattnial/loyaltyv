<?php

namespace App\Domains\Technical\Services;

use App\Domains\Technical\Models\Olt;
use App\Domains\Technical\Drivers\HuaweiDriver;
use App\Domains\Technical\Drivers\MockHuaweiDriver;
use Exception;

class OltService
{
    /**
     * Obtiene el estado de una ONU específica buscando la OLT en la DB.
     */
    public function getOnuStatus(int $oltId, string $onuIndex)
    {
        // 1. Buscar la OLT en la base de datos
        $olt = Olt::findOrFail($oltId);

        // 2. Decidir qué driver usar
        // TRUCO: Por ahora forzamos el Mock para que no te falle sin hardware real.
        // Cuando tengas la IP real, cambiarás esto por: $driver = new HuaweiDriver();
        $driver = new MockHuaweiDriver(); 

        // 3. Preparar credenciales (desencriptadas automáticamente por el Modelo)
        $credentials = [
            'user' => $olt->admin_user,
            'password' => $olt->admin_password
        ];

        // 4. Llamar al driver
        return $driver->getStatus($olt->ip_address, $credentials, $onuIndex);
    }
}