<?php

namespace App\Domains\Technical\Services;

use App\Domains\Technical\Drivers\Core\RouterOsClient;
use Exception;

class MikrotikService
{
    protected $client;

    public function __construct()
    {
        // $this->client = new RouterOsClient(); // REAL (Comentado por ahora)
        $this->client = new \App\Domains\Technical\Drivers\Core\MockRouterOsClient(); // SIMULADOR
    }

    /**
     * Habilita o Suspende un servicio PPPoE
     */
    public function togglePppoe($ipMikrotik, $userMikrotik, $passMikrotik, $pppoeName, $enable = true)
    {
        try {
            // 1. Conectar
            $this->client->connect($ipMikrotik, $userMikrotik, $passMikrotik);

            // 2. Buscar el ID interno del secreto PPPoE usando el nombre de usuario
            // Comando equivalente: /ppp/secret/print where name="juanperez"
            $results = $this->client->comm('/ppp/secret/print', [
                '?name' => $pppoeName
            ]);

            if (empty($results)) {
                throw new Exception("Usuario PPPoE '$pppoeName' no encontrado en Mikrotik.");
            }

            $id = $results[0]['.id']; // ID interno (ej: *A1)

            // 3. Ejecutar la acción (enable / disable)
            $command = $enable ? '/ppp/secret/enable' : '/ppp/secret/disable';
            $this->client->comm($command, ['.id' => $id]);

            // 4. Si estamos cortando, también pateamos al usuario activo para que se caiga YA
            if (!$enable) {
                $active = $this->client->comm('/ppp/active/print', ['?name' => $pppoeName]);
                if (!empty($active)) {
                    $this->client->comm('/ppp/active/remove', ['.id' => $active[0]['.id']]);
                }
            }

            $this->client->disconnect();
            return true;

        } catch (Exception $e) {
            return "Error Mikrotik: " . $e->getMessage();
        }
    }
}