<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Mejorar tabla de Premios
        Schema::table('rewards', function (Blueprint $table) {
            // Agregamos columnas si no existen
            if (!Schema::hasColumn('rewards', 'image_path')) {
                $table->string('image_path')->nullable()->after('name');
            }
            if (!Schema::hasColumn('rewards', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('stock');
            }
            if (!Schema::hasColumn('rewards', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_featured');
            }
        });

        // 2. Tabla de Notificaciones (Estándar de Laravel)
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        // No borramos columnas para evitar pérdida de datos accidental
    }
};