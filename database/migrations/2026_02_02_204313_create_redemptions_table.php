<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers'); // Relación con tu tabla de clientes
            $table->foreignId('reward_id')->constrained('rewards');
            $table->string('reward_name'); // Guardamos nombre por si luego borras el premio
            $table->integer('points_spent');
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('redemptions');
    }
};