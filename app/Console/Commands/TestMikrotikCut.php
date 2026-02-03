<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domains\Technical\Services\MikrotikService;

class TestMikrotikCut extends Command
{
    // Firma: php artisan technical:cut 192.168.88.1 admin password juanperez off
    protected $signature = 'technical:cut {ip} {user} {pass} {pppoe_user} {action=off}';
    protected $description = 'Prueba de corte/activación en Mikrotik';

    public function handle()
    {
        $service = new MikrotikService();
        $enable = $this->argument('action') === 'on';
        
        $this->info("Intentando " . ($enable ? "ACTIVAR" : "CORTAR") . " a {$this->argument('pppoe_user')}...");

        $result = $service->togglePppoe(
            $this->argument('ip'),
            $this->argument('user'),
            $this->argument('pass'),
            $this->argument('pppoe_user'),
            $enable
        );

        if ($result === true) {
            $this->info("✅ ¡Éxito! El comando se ejecutó en el Mikrotik.");
        } else {
            $this->error("❌ Fallo: $result");
        }
    }
}