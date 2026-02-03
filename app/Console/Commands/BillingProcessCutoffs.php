<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domains\Billing\Models\Subscription;
use App\Domains\Technical\Services\MikrotikService;
use Carbon\Carbon;

class BillingProcessCutoffs extends Command
{
    protected $signature = 'billing:process-cutoffs';
    protected $description = 'Corta automáticamente el servicio a clientes vencidos';

    public function handle()
    {
        $this->info("🕵️  Buscando clientes morosos...");

        // 1. Buscar suscripciones ACTIVAS que vencieron AYER o antes
        $overdue = Subscription::where('status', 'active')
            ->where('paid_until', '<', Carbon::now()->startOfDay())
            ->get();

        if ($overdue->isEmpty()) {
            $this->info("✅ No hay clientes vencidos hoy. Todos al día.");
            return;
        }

        $this->info("⚠️  Se encontraron {$overdue->count()} clientes vencidos. Iniciando corte masivo...");
        $mikrotik = new MikrotikService();

        foreach ($overdue as $sub) {
            $this->line("✂️  Cortando a: {$sub->customer->full_name} (IP: {$sub->service_ip})");

            // 2. Ejecutar corte en Mikrotik
            // IMPORTANTE: En producción usarías la IP real de la OLT/Router de ese cliente
            // Aquí usamos una IP genérica para la simulación
            $routerIp = '192.168.1.1'; 
            
            $result = $mikrotik->togglePppoe(
                $routerIp, 'admin', 'password', 
                $sub->pppoe_user, 
                false // FALSE = Disable (Cortar)
            );

            // 3. Si el Mikrotik confirmó el corte, actualizamos la BD local
            if ($result === true) {
                $sub->status = 'suspended';
                $sub->save();
                $this->info("   --> ÉXITO: Servicio suspendido en sistema.");
            } else {
                $this->error("   --> ERROR: El Mikrotik no respondió. {$result}");
            }
        }
        
        $this->newLine();
        $this->info("🏁 Proceso finalizado.");
    }
}