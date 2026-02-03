<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domains\Technical\Drivers\HuaweiDriver;
use Exception;

class TestOltConnection extends Command
{
    // Este será el comando que escribirás en la terminal
    // Ejemplo: php artisan technical:test-olt 192.168.1.10 root admin123 "0/1/1:0"
    protected $signature = 'technical:test-olt 
                            {ip : IP de la OLT} 
                            {user : Usuario Telnet} 
                            {password : Password Telnet} 
                            {onu_index=0/1/1:0 : Index de prueba (Frame/Slot/Port:ID)}';

    protected $description = 'Prueba de conexión Telnet directa con OLT Huawei';

    public function handle()
    {
        $this->info("🔌 Iniciando secuencia de conexión a {$this->argument('ip')}...");

        // $driver = new HuaweiDriver(); // Comentamos el real por ahora
        $driver = new \App\Domains\Technical\Drivers\MockHuaweiDriver(); // Usamos el simulador
        
        $credentials = [
            'user' => $this->argument('user'),
            'password' => $this->argument('password')
        ];

        try {
            $startTime = microtime(true);
            
            // Llamamos a la función que creaste en el Driver
            $result = $driver->getStatus(
                $this->argument('ip'),
                $credentials,
                $this->argument('onu_index')
            );

            $duration = round(microtime(true) - $startTime, 2);

            $this->newLine();
            $this->info("✅ ¡ÉXITO! Conexión establecida en {$duration}s");
            $this->line("----------------------------------------");
            $this->comment("Respuesta cruda de la OLT:");
            $this->info($result['raw_output']);
            $this->line("----------------------------------------");
            $this->info("Estado interpretado: " . strtoupper($result['status']));

        } catch (Exception $e) {
            $this->newLine();
            $this->error("❌ FALLO LA CONEXIÓN:");
            $this->error($e->getMessage());
            
            // Tips de depuración comunes
            $this->newLine();
            $this->comment("Posibles causas:");
            $this->comment("1. Tu PC no llega a la IP de la OLT (Haz ping primero).");
            $this->comment("2. El puerto 23 (Telnet) está cerrado en la OLT.");
            $this->comment("3. Usuario/Clave incorrectos.");
            $this->comment("4. Windows Firewall bloqueó a PHP (Revisa popups).");
        }
    }
}