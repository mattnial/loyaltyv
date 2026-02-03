<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domains\Billing\Models\Customer;

class ResetCustomerPasswords extends Command
{
    protected $signature = 'billing:reset-passwords';
    protected $description = 'Resetea las contraseñas de todos los clientes para que sean igual a su cédula.';

    public function handle()
    {
        $this->info("🔐 Iniciando reseteo masivo de contraseñas...");

        // Procesamos por bloques para no saturar la memoria
        $count = 0;
        
        Customer::chunk(100, function ($customers) use (&$count) {
            foreach ($customers as $customer) {
                // Regla: La contraseña es su identificación
                $customer->password = bcrypt($customer->identification);
                $customer->saveQuietly(); // saveQuietly evita disparar eventos extra si los hubiera
                $count++;
                
                $this->output->write('.'); // Feedback visual
            }
        });

        $this->newLine();
        $this->info("✅ ¡Listo! Se actualizaron las contraseñas de {$count} clientes.");
        $this->info("   Ahora todos pueden entrar con Usuario: Cédula / Pass: Cédula");
    }
}