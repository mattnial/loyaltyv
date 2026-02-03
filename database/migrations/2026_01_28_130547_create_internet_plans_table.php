<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internet_plans', function (Blueprint $table) {
            $table->id();

            // Información Comercial
            $table->string('name', 100); // Ej: "Fibra Óptica 100M"
            $table->decimal('price', 10, 2); // Ej: 24.99
            $table->string('currency', 3)->default('USD');

            // Información Técnica
            $table->integer('download_speed_kbps'); // Ej: 102400 (100Mb)
            $table->integer('upload_speed_kbps');   // Ej: 51200 (50Mb)

            // Sincronización con Mikrotik (Vital para el futuro)
            $table->string('mikrotik_profile_name')->nullable()->comment('Nombre exacto en el Queue Types del Mikrotik');

            // Configuración
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internet_plans');
    }
};