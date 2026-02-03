<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            
            // 1. Relaciones (El Triángulo Sagrado)
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('internet_plan_id')->constrained();
            $table->foreignId('olt_id')->nullable()->constrained(); // Nullable por si es Radioenlace
            
            // 2. Identificación Técnica (Red)
            $table->ipAddress('service_ip')->unique()->comment('IP fija asignada al cliente');
            $table->string('pppoe_user')->nullable()->unique();
            $table->string('pppoe_password')->nullable();
            
            // 3. Identificación Hardware (Fibra)
            $table->string('onu_serial')->nullable()->comment('MAC o SN del equipo');
            $table->string('onu_index')->nullable()->comment('Puerto en OLT: 0/1/0:5');
            
            // 4. Datos de Instalación
            $table->text('wifi_ssid')->nullable(); // Nombre WiFi Cliente
            $table->text('wifi_password')->nullable();
            $table->string('nap_box')->nullable()->comment('Caja NAP C-05');
            $table->integer('nap_port')->nullable();
            
            // 5. Estado y Fechas
            $table->string('status')->default('active')->comment('active, suspended, retired');
            $table->date('installation_date');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};