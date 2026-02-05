<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('point_histories', function (Blueprint $table) {
            // Guardaremos el ID del usuario admin que hizo el movimiento.
            // Si es NULL, asumiremos que fue el "SISTEMA" automático.
            $table->foreignId('user_id')->nullable()->constrained('users')->after('customer_id');
        });
    }

    public function down()
    {
        Schema::table('point_histories', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};