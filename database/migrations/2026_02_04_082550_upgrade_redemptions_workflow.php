<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('redemptions', function (Blueprint $table) {
            // Estado del canje
            if (!Schema::hasColumn('redemptions', 'status')) {
                // pending: Esperando aprobación
                // approved: Aprobado, cliente debe ir a retirar
                // completed: Entregado con foto
                // rejected: Rechazado (se devuelven puntos)
                $table->string('status')->default('pending')->after('points_used');
            }

            // Dónde debe retirar
            if (!Schema::hasColumn('redemptions', 'pickup_branch')) {
                $table->string('pickup_branch')->nullable()->after('status'); // Loja, Vilcabamba, Palanda
            }

            // Foto de prueba
            if (!Schema::hasColumn('redemptions', 'proof_photo_path')) {
                $table->string('proof_photo_path')->nullable()->after('pickup_branch');
            }
            
            // Notas del admin
            if (!Schema::hasColumn('redemptions', 'admin_note')) {
                $table->text('admin_note')->nullable()->after('proof_photo_path');
            }
        });
    }

    public function down()
    {
        // No borramos columnas por seguridad
    }
};