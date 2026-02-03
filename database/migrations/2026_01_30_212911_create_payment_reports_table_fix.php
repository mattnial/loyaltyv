<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Verificamos si existe para no dar error doble
        if (!Schema::hasTable('payment_reports')) {
            Schema::create('payment_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade'); // Ojo: constrained('customers') explícito
                $table->string('invoice_number');
                $table->decimal('amount', 10, 2);
                $table->string('payment_method')->default('transferencia');
                $table->string('proof_image_path');
                $table->string('status')->default('pending');
                $table->text('admin_note')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_reports');
    }
};