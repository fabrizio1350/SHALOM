<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_movimientos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_encomienda', 20);
            $table->foreign('id_encomienda')->references('id_encomienda')->on('encomiendas');
            $table->string('estado_anterior', 20);
            $table->string('estado_nuevo', 20);
            $table->text('observacion')->nullable();
            $table->unsignedBigInteger('id_usuario');
            $table->foreign('id_usuario')->references('id')->on('users');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_movimientos');
    }
};