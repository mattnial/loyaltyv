<?php

namespace App\Domains\Technical\Contracts;

interface OnuDriverInterface
{
    /**
     * Verifica si la ONU responde y obtiene sus niveles de luz.
     * @return array {rx_power, tx_power, status, uptime}
     */
    public function getStatus(string $ip, array $credentials, string $onuIndex): array;

    /**
     * Cambia el nombre y contraseña del WiFi.
     */
    public function updateWifiConfig(string $ip, array $credentials, string $onuIndex, string $ssid, string $password): bool;

    /**
     * Reinicia la ONU remotamente.
     */
    public function reboot(string $ip, array $credentials, string $onuIndex): bool;
}