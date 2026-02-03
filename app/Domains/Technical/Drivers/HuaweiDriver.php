<?php

namespace App\Domains\Technical\Drivers;

use App\Domains\Technical\Contracts\OnuDriverInterface;
use App\Domains\Technical\Drivers\Core\TelnetClient;
use Exception;

class HuaweiDriver implements OnuDriverInterface
{
    protected $client;

    public function __construct()
    {
        $this->client = new TelnetClient();
    }

    public function getStatus(string $ip, array $credentials, string $onuIndex): array
    {
        // 1. Conectar
        $this->client->connect($ip);
        
        // 2. Login (Usuario/Pass desencriptados vienen del Controller)
        if (!$this->client->login($credentials['user'], $credentials['password'])) {
            throw new Exception("Login fallido en OLT Huawei $ip");
        }

        // 3. Activar modo privilegiado (enable)
        $this->client->exec('enable');
        $this->client->exec('config'); // Entrar a modo config si es necesario

        // 4. Ejecutar comando real de Huawei
        // Formato onuIndex esperado: "0/1/0 15" (Frame/Slot/Port ONU_ID)
        // Convertimos "0/1/0:15" a "0 1 0 15" si es necesario
        $formattedIndex = str_replace(['/', ':'], ' ', $onuIndex);
        
        $response = $this->client->exec("display ont info $formattedIndex");
        
        // 5. Desconectar
        $this->client->disconnect();

        // 6. Parsear respuesta (Esto lo refinaremos luego con Regex)
        return [
            'raw_output' => $response,
            'status' => strpos($response, 'online') !== false ? 'online' : 'offline',
            'timestamp' => now()
        ];
    }

    public function updateWifiConfig(string $ip, array $credentials, string $onuIndex, string $ssid, string $password): bool
    {
        // Implementaremos esto cuando confirmemos que getStatus funciona
        return true;
    }

    public function reboot(string $ip, array $credentials, string $onuIndex): bool
    {
        $this->client->connect($ip);
        $this->client->login($credentials['user'], $credentials['password']);
        $this->client->exec('enable');
        
        $formattedIndex = str_replace(['/', ':'], ' ', $onuIndex);
        // Comando peligroso: Reboot de ONU
        $this->client->exec("ont reboot $formattedIndex");
        
        $this->client->disconnect();
        return true;
    }
}