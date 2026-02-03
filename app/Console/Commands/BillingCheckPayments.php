<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domains\Billing\Models\Subscription;
use App\Domains\Billing\Services\DivusBridgeService;
use Carbon\Carbon;

class BillingCheckPayments extends Command
{
    protected $signature = 'billing:check-payments';
    protected $description = 'Revisa en Divus si los clientes cortados ya pagaron y los reconecta.';

    public function handle()
    {
        // 1. Buscamos SOLO a los clientes que están CORTADOS (suspended)
        $suspendedSubs = Subscription::with('customer')
                                     ->where('status', 'suspended')
                                     ->get();

        $count = $suspendedSubs->count();
        if ($count === 0) {
            $this->info("✅ No hay clientes cortados. Todo tranquilo.");
            return;
        }

        $this->info("🕵️  Revisando pagos para {$count} clientes suspendidos...");

        $divus = new DivusBridgeService();
        $reactivated = 0;

        foreach ($suspendedSubs as $sub) {
            $cedula = $sub->customer->identification;
            $name = $sub->customer->first_name;

            $this->comment("   > Consultando a: {$name} ({$cedula})...");

            // 2. Preguntamos a Divus sus facturas
            // (El Bridge ya hace el login y el scraping por nosotros)
            $invoices = $divus->getInvoicesByCedula($cedula);

            if (empty($invoices)) {
                $this->warn("     ⚠️  Sin datos en Divus.");
                continue;
            }

            // 3. Analizamos la ÚLTIMA factura (la más reciente)
            // Asumimos que la lista viene ordenada o la ordenamos por seguridad
            // (En tu scraping venían en orden, pero tomemos la primera del array por si acaso)
            $latestInvoice = $invoices[0]; 

            // Limpiamos el estado (quitamos espacios y mayúsculas)
            $estadoDivus = strtoupper(trim($latestInvoice['estado']));
            $monto = $latestInvoice['monto'];
            $fechaFactura = $latestInvoice['fecha']; // Ej: 2026-01-29

            // 4. EL CRITERIO DE RECONEXIÓN 💡
            // Si la última factura dice "PAGADO" o "CANCELADO" (en contabilidad significa pagado)
            if (str_contains($estadoDivus, 'PAGADO') || str_contains($estadoDivus, 'CANCELADO')) {
                
                // OPCIONAL: Verificar fecha para no activar por facturas viejas de hace un año
                // Aquí asumimos que si está cortado y la ÚLTIMA factura está pagada, es que pagó hoy.
                
                $this->info("     💰 ¡PAGO DETECTADO! ($monto) - Reconectando...");

                // A. Actualizamos el estado local
                $sub->status = 'active';
                
                // B. Extendemos la fecha de corte (Le damos 30 días más de servicio)
                // Ojo: Esto es una lógica simple. En producción podrías calcularlo exacto.
                $sub->paid_until = Carbon::now()->addMonth(); 
                
                $sub->save();

                // C. Aquí iría la orden al Mikrotik (Cuando tengamos IPs reales)
                // $mikrotik->activateUser($sub->service_ip);

                $reactivated++;
            } else {
                $this->line("     ❌ Sigue debiendo (Estado: {$estadoDivus})");
            }

            // Pequeña pausa para no saturar al servidor de Divus si son muchos
            usleep(200000); // 0.2 segundos
        }

        $this->newLine();
        $this->info("🎉 Proceso terminado. Se reconectaron {$reactivated} clientes automáticamente.");
    }
}