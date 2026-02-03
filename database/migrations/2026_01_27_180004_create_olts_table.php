<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('olts', function (Blueprint $table) {
            $table->id();
            
            // Identificación del Equipo
            $table->string('name', 50)->comment('Ej: Nodo Centro');
            $table->ipAddress('ip_address')->unique();
            $table->string('driver')->default('huawei')->comment('huawei, zte, vsol');
            
            // Puertos de Conexión
            $table->integer('telnet_port')->default(23);
            $table->integer('snmp_port')->default(161);
            
            // Credenciales (Se guardarán encriptadas)
            $table->text('admin_user');     
            $table->text('admin_password'); 
            $table->string('snmp_community')->default('public');

            // Configuración
            $table->boolean('is_active')->default(true);
            $table->json('extra_config')->nullable(); // Para VLANs o datos raros
            
            $table->timestamps();
            $table->softDeletes(); // Papelera de reciclaje (Safety First)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('olts');
    }
};