<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Borramos la tabla vieja si existe (porque está mal hecha)
        Schema::dropIfExists('streak_milestones');

        // 2. La creamos de nuevo con las columnas correctas
        Schema::create('streak_milestones', function (Blueprint $table) {
            $table->id();
            $table->integer('months_required'); // Esta es la columna que faltaba
            $table->integer('bonus_points');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('streak_milestones');
    }
};