<?php

namespace App\Domains\Technical\Drivers\Core;

class MockRouterOsClient
{
    private $connected = false;

    public function connect($ip, $user, $password, $port = 8728)
    {
        // Simulamos que siempre conecta exitosamente
        $this->connected = true;
        return true;
    }

    public function disconnect()
    {
        $this->connected = false;
    }

    public function comm($command, array $params = [])
    {
        if (!$this->connected) {
            throw new \Exception("Simulación: No hay conexión activa.");
        }

        // 1. Simular búsqueda de secreto PPPoE (/ppp/secret/print)
        if ($command === '/ppp/secret/print') {
            // Si preguntan por cualquier usuario, devolvemos un ID falso
            return [
                ['.id' => '*13', 'name' => $params['?name'] ?? 'usuario_simulado', 'profile' => 'default']
            ];
        }

        // 2. Simular búsqueda de conexión activa (/ppp/active/print)
        if ($command === '/ppp/active/print') {
            return [
                ['.id' => '*A1', 'name' => $params['?name'] ?? 'usuario_simulado']
            ];
        }

        // 3. Para comandos de acción (enable, disable, remove), devolvemos vacío (éxito en Mikrotik)
        return [];
    }
}