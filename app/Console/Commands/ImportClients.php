<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CustomersImport;
use Illuminate\Support\Facades\DB; // Importante para poder borrar

class ImportClients extends Command
{
    protected $signature = 'vilcanet:import-clients {file}';
    protected $description = 'Borra clientes antiguos e importa nuevos desde Excel';

    public function handle()
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error("❌ El archivo no existe: $file");
            return;
        }

        // CONFIRMACIÓN DE SEGURIDAD
        if ($this->confirm('⚠️ ESTO BORRARÁ TODOS LOS CLIENTES Y SUS PUNTOS ACUMULADOS para iniciar de cero. ¿Estás seguro?')) {
            
            $this->info("🧹 Borrando base de datos de clientes...");
            
            // Desactivar chequeo de claves foráneas temporalmente para evitar errores
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('customers')->truncate(); // <--- AQUÍ SE BORRA TODO
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            $this->info("⏳ Iniciando importación limpia...");

            try {
                Excel::import(new CustomersImport, $file);
                $this->info("✅ ¡Éxito! Base de datos renovada. Todos inician con 0 puntos.");
            } catch (\Exception $e) {
                $this->error("❌ Error al importar: " . $e->getMessage());
            }
        }
    }
}