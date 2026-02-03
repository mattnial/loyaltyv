<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::create('news', function (Blueprint $table) {
        $table->id();
        $table->string('title');          // Ej: "¡Aumentamos tu velocidad!"
        $table->text('description')->nullable(); // Texto largo
        $table->string('image_url');      // Foto de la promo
        $table->string('action_url')->nullable(); // Link si hacen clic (ej: WhatsApp)
        $table->boolean('is_popup')->default(false); // ¿Debe salir como alerta flotante?
        $table->boolean('is_active')->default(true); // Para apagarla sin borrarla
        $table->integer('order')->default(0); // Para ordenar cuál sale primero
        $table->timestamps();
    });
}
};
