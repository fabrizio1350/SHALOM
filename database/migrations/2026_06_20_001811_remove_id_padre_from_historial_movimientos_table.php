<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::table('historial_movimientos', function (Blueprint $table) {
        $table->dropForeign(['id_padre']);
        $table->dropColumn('id_padre');
    });
}

public function down(): void
{
    Schema::table('historial_movimientos', function (Blueprint $table) {
        $table->unsignedInteger('id_padre')->nullable()->after('id');
        $table->foreign('id_padre')->references('id')->on('historial_movimientos');
    });
}
};
