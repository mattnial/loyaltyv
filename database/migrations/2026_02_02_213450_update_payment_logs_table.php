<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payment_logs', function (Blueprint $table) {
            $table->string('client_name')->nullable()->after('cedula');
            $table->string('invoice_num')->nullable()->after('amount');
            $table->string('plan')->nullable()->after('invoice_num');
        });
    }

    public function down()
    {
        Schema::table('payment_logs', function (Blueprint $table) {
            $table->dropColumn(['client_name', 'invoice_num', 'plan']);
        });
    }
};