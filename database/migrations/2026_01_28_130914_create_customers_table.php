<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            
            // Identificación Legal (Ecuador)
            $table->string('identification', 13)->unique()->comment('Cédula o RUC');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 150)->nullable()->unique(); // Para facturas electrónicas
            $table->string('phone', 20); // WhatsApp para notificaciones
            
            // Ubicación (Vital para instalaciones)
            $table->text('address')->comment('Calle principal, secundaria y referencia');
            $table->string('coordinates')->nullable()->comment('Latitud,Longitud (Google Maps)');
            
            // Estado del Cliente (No del servicio, sino de la persona)
            // 'active' = cliente normal, 'blacklisted' = moroso peligroso, 'prospect' = interesado
            $table->string('status')->default('active'); 
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};