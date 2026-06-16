<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracion', function (Blueprint $table) {
            $table->decimal('peso_maximo_pequeno', 8, 2)->default(5.00)->after('tiempo_maximo_dias');
            $table->decimal('peso_maximo_mediano', 8, 2)->default(20.00)->after('peso_maximo_pequeno');
        });
    }

    public function down(): void
    {
        Schema::table('configuracion', function (Blueprint $table) {
            $table->dropColumn(['peso_maximo_pequeno', 'peso_maximo_mediano']);
        });
    }
};