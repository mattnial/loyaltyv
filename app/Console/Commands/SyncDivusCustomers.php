<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domains\Billing\Models\LegacyClient;
use App\Domains\Billing\Models\Customer;
use App\Domains\Billing\Models\InternetPlan;
use App\Domains\Billing\Models\Subscription;
use App\Domains\Technical\Models\Olt;

class SyncDivusCustomers extends Command
{
    protected $signature = 'billing:sync-bonus';
    protected $description = 'Importa clientes con limpieza total (Cédula, Teléfono, UTF8)';

    public function handle()
    {
        $this->info("🔌 Conectando a Vilcanet Bonus (Nube)...");

        try {
            $check = LegacyClient::first();
            if (!$check) {
                $this->warn("⚠️  Tabla 'clients' vacía.");
                return;
            }
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return;
        }

        $legacyClients = LegacyClient::whereNotNull('cedula')->where('cedula', '!=', '')->get();
        $total = $legacyClients->count();
        
        $this->info("📦 Se encontraron {$total} clientes. Aplicando filtros de limpieza...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $defaultOlt = Olt::first();
        $tempCounter = 1; 

        foreach ($legacyClients as $legacy) {
            
            // 0. LIMPIEZA DE CÉDULA/RUC (El arreglo nuevo)
            $rawCedula = trim($legacy->cedula);
            // Si viene "1191704718001 COOPER", cortamos en el espacio y nos quedamos con el número
            $cedulaParts = explode(' ', $rawCedula);
            $cedulaLimpia = trim($cedulaParts[0]); 
            // Cortamos a 20 caracteres por seguridad máxima
            $cedulaLimpia = substr($cedulaLimpia, 0, 20);

            // 1. Limpieza de Nombre
            $nombreCompleto = trim($legacy->name);
            $nombreCompleto = mb_convert_encoding($nombreCompleto, 'UTF-8', 'UTF-8');
            
            $partes = explode(' ', $nombreCompleto);
            $apellido = array_pop($partes);
            if (count($partes) > 0) $apellido = array_pop($partes) . ' ' . $apellido;
            $nombre = implode(' ', $partes);
            if (empty($nombre)) $nombre = "Cliente Importado";

            // 2. Limpieza de Teléfono
            $rawPhone = $legacy->phone ?? '';
            $phoneLimpio = preg_replace('/[^0-9]/', '', $rawPhone);
            if (empty($phoneLimpio)) $phoneLimpio = '0990000000';
            $phoneLimpio = substr($phoneLimpio, 0, 15);

            // 3. Coordenadas
            $coords = null;
            if (!empty($legacy->geo_lat) && !empty($legacy->geo_lon)) {
                $coords = $legacy->geo_lat . ',' . $legacy->geo_lon;
            }

            // 4. Guardar Cliente
            $customer = Customer::updateOrCreate(
                ['identification' => $cedulaLimpia], // <--- Usamos la cédula limpia
                [
                    'first_name' => $nombre,
                    'last_name'  => $apellido,
                    'email'      => $legacy->email,
                    'phone'      => $phoneLimpio,
                    'address'    => mb_convert_encoding($legacy->address ?? 'Dirección Pendiente', 'UTF-8', 'UTF-8'),
                    'coordinates'=> $coords,
                    'status'     => 'active'
                ]
            );

            // 5. Plan
            $planName = $legacy->service_type ?? 'Plan Basico';
            $planName = mb_convert_encoding($planName, 'UTF-8', 'UTF-8');
            $planPrice = $legacy->plan_price ?? 20.00;

            $plan = InternetPlan::firstOrCreate(
                ['name' => $planName],
                [
                    'price' => $planPrice,
                    'download_speed_kbps' => 20480,
                    'upload_speed_kbps' => 20480,
                    'mikrotik_profile_name' => 'default'
                ]
            );

            // 6. Estado
            $status = 'active';
            $estadoViejo = strtoupper($legacy->service_status ?? 'ACTIVO');
            if (in_array($estadoViejo, ['CORTADO', 'SUSPENDIDO', 'RETIRADO', 'INACTIVO'])) {
                $status = 'suspended';
            }

            // 7. IP Temporal
            $octet3 = floor($tempCounter / 254);
            $octet4 = ($tempCounter % 254) + 1;
            $fakeIp = "127.0.{$octet3}.{$octet4}";
            $tempCounter++;

            // 8. Crear Suscripción
            Subscription::updateOrCreate(
                ['customer_id' => $customer->id],
                [
                    'internet_plan_id' => $plan->id,
                    'olt_id'           => $defaultOlt ? $defaultOlt->id : null,
                    'service_ip'       => $fakeIp,
                    'pppoe_user'       => $cedulaLimpia,
                    'pppoe_password'   => $cedulaLimpia,
                    'status'           => $status,
                    'installation_date'=> $legacy->contract_date ?? now(),
                    'wifi_ssid'        => 'WIFI-' . substr($cedulaLimpia, -4),
                    'wifi_password'    => 'vilcanet123'
                ]
            );

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ ¡Misión Cumplida! Sincronización al 100%.");
    }
}