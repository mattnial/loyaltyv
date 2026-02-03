<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            // Solo agregamos la columna si no existe
            if (!Schema::hasColumn('customers', 'points')) {
                $table->integer('points')->default(0);
            }
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'points')) {
                $table->dropColumn('points');
            }
        });
    }
};