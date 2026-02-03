<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. CONFIGURACIÓN GENERAL (Lo que editas en el Admin)
        Schema::create('loyalty_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('points_per_payment')->default(10);
            $table->integer('payment_start_day')->default(1); // Día inicio racha
            $table->integer('payment_end_day')->default(10);  // Día fin racha
            $table->integer('double_points_start')->default(1);
            $table->integer('double_points_end')->default(5);
            $table->integer('points_birthday')->default(500);
            $table->integer('points_anniversary')->default(1000);
            $table->integer('points_christmas')->default(200);
            $table->timestamps();
        });

        // 2. HITOS DE RACHA (Ej: 3 meses = 100 pts)
        Schema::create('streak_milestones', function (Blueprint $table) {
            $table->id();
            $table->integer('months');
            $table->integer('bonus_points');
            $table->timestamps();
        });

        // 3. LOG DE PAGOS (Para no procesar el mismo pago dos veces)
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->string('cedula')->index();
            $table->decimal('amount', 10, 2);
            $table->dateTime('payment_date');
            $table->boolean('is_processed')->default(false);
            $table->timestamps();
        });

        // 4. CUPONES / CÓDIGOS PROMO
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->integer('points');
            $table->integer('max_uses')->default(100);
            $table->integer('used_count')->default(0);
            $table->date('expires_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. USO DE CUPONES (Quién canjeó qué)
        Schema::create('promo_code_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('promo_code_id')->constrained('promo_codes');
            $table->timestamps();
        });
        
        // 6. Actualizar tabla Clientes (Campos necesarios para la lógica)
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'streak_count')) {
                $table->integer('streak_count')->default(0);
            }
            if (!Schema::hasColumn('customers', 'last_payment_date')) {
                $table->date('last_payment_date')->nullable();
            }
            if (!Schema::hasColumn('customers', 'birth_date')) {
                $table->date('birth_date')->nullable(); // Necesario para cumple
            }
            if (!Schema::hasColumn('customers', 'contract_start_date')) {
                $table->date('contract_start_date')->nullable(); // Necesario para aniversario
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('promo_code_usages');
        Schema::dropIfExists('promo_codes');
        Schema::dropIfExists('payment_logs');
        Schema::dropIfExists('streak_milestones');
        Schema::dropIfExists('loyalty_settings');
    }
};