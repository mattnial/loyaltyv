<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User; // O tu modelo Customer
use App\Models\PointHistory;
use App\Services\DivusService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ScanDivusPayments extends Command
{
    protected $signature = 'vilcanet:scan-payments';
    protected $description = 'Escanea pagos nuevos en Divusware y asigna puntos';

    public function handle()
    {
        // 1. FORZAR ZONA HORARIA (CRÍTICO)
        date_default_timezone_set('America/Guayaquil');
        $this->info('Iniciando escaneo... Hora del sistema: ' . date('Y-m-d H:i:s'));

        $settings = \Illuminate\Support\Facades\DB::table('loyalty_settings')->first();
        if (!$settings) {
            $this->error('Falta configuración de lealtad.'); return;
        }

        $divus = new \App\Services\DivusService();
        
        try {
            $ventas = $divus->getLiveSales();
        } catch (\Exception $e) {
            $this->error("Error conexión: " . $e->getMessage()); return;
        }
        
        $count = count($ventas);
        $this->info("Registros descargados: $count");

        // FECHA DE HOY (ECUADOR)
        $hoyDivus = date('d/m/Y'); 
        $this->line("Buscando pagos con fecha: $hoyDivus");

        $nuevos = 0;
        $omitidosFecha = 0;

        foreach ($ventas as $v) {
            // MAPEO CONFIRMADO POR TU DEBUG
            // [0]=>Fecha, [1]=>Factura, [2]=>Cliente, [6]=>Monto
            $fechaRaw   = trim(strip_tags($v[0] ?? ''));
            $factura    = trim(strip_tags($v[1] ?? ''));
            $clienteRaw = trim(strip_tags($v[2] ?? ''));
            $plan       = trim(strip_tags($v[3] ?? ''));
            $montoRaw   = trim(strip_tags($v[6] ?? '0')); 

            // CHEQUEO DE FECHA ESTRICTO
            // Usamos strpos porque la fecha viene con hora "02/02/2026 07:37"
            if (strpos($fechaRaw, $hoyDivus) === false) {
                $omitidosFecha++;
                continue; 
            }

            // Extracción de datos
            $cedula = '';
            $nombre = $clienteRaw;
            if (strpos($clienteRaw, ' - ') !== false) {
                $parts = explode(' - ', $clienteRaw);
                $cedula = trim($parts[0]);
                $nombre = trim($parts[1]);
            }

            if (empty($cedula) || empty($factura)) continue;

            $dateObj = \DateTime::createFromFormat('d/m/Y H:i', $fechaRaw);
            $fechaSQL = $dateObj ? $dateObj->format('Y-m-d H:i:s') : now();

            // Guardar en BD
            $log = \App\Models\PaymentLog::firstOrCreate(
                ['invoice_num' => $factura],
                [
                    'cedula' => $cedula,
                    'client_name' => $nombre,
                    'amount' => floatval(str_replace(',', '.', $montoRaw)),
                    'payment_date' => $fechaSQL,
                    'plan' => $plan,
                    'is_processed' => false
                ]
            );

            if ($log->wasRecentlyCreated) {
                $nuevos++;
                $this->info(" [+] Guardado: $nombre ($factura) - $montoRaw");
                
                // --- ASIGNAR PUNTOS (Lógica Rápida) ---
                // Verifica si el cliente existe en tu App
                $clienteApp = \App\Models\Customer::where('identification', $cedula)->first();
                
                if ($clienteApp) {
                    $puntos = $settings->points_per_payment * max(1, $log->amount);
                    
                    // Puntos Dobles?
                    $dia = intval(date('d'));
                    if ($dia >= $settings->double_points_start && $dia <= $settings->double_points_end) {
                        $puntos *= 2;
                    }

                    // Actualizar Cliente
                    $clienteApp->points += $puntos;
                    $clienteApp->streak_count += 1;
                    $clienteApp->last_payment_date = $fechaSQL;
                    $clienteApp->save();

                    // Marcar procesado
                    $log->is_processed = true;
                    $log->save();

                    \App\Models\PointHistory::create([
                        'customer_id' => $clienteApp->id,
                        'type' => 'earn',
                        'points' => $puntos,
                        'description' => "Pago Factura #$factura"
                    ]);
                    $this->comment("     -> ⭐ ¡Puntos asignados!");
                }
            }
        }
        
        $this->info("------------------------------------------------");
        $this->info("Resumen:");
        $this->info("- Total descargados: $count");
        $this->info("- Omitidos (otra fecha): $omitidosFecha");
        $this->info("- Nuevos Guardados: $nuevos");
    }

    private function darPuntos($clientId, $points, $description) {
        DB::table('customers')->where('id', $clientId)->increment('points', $points);
        
        DB::table('point_histories')->insert([
            'customer_id' => $clientId,
            'type' => 'earn',
            'points' => $points,
            'description' => $description,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}