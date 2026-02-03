<?php

namespace App\Domains\Technical\Drivers;

use App\Domains\Technical\Contracts\OnuDriverInterface;

class MockHuaweiDriver implements OnuDriverInterface
{
    /**
     * Simula una respuesta exitosa de una ONU Huawei
     */
    public function getStatus(string $ip, array $credentials, string $onuIndex): array
    {
        // Simulamos un pequeño retraso de red (como en la vida real)
        sleep(1); 

        return [
            'raw_output' => "
                ONTID : 15
                Control flag : active
                Run state : online
                Config state : normal
                Match state : match
                Memory occupation : 14 %
                CPU occupation : 2 %
                Temperature : 45(C)
                Rx optical power : -19.45(dBm)
                Tx optical power : 2.21(dBm)
            ",
            'status' => 'online',
            'rx_power' => -19.45,
            'distance' => 450, // metros
            'timestamp' => now()->toDateTimeString()
        ];
    }

    public function updateWifiConfig(string $ip, array $credentials, string $onuIndex, string $ssid, string $password): bool
    {
        return true; // Fingimos que se cambió exitosamente
    }

    public function reboot(string $ip, array $credentials, string $onuIndex): bool
    {
        return true; // Fingimos que se reinició
    }
}