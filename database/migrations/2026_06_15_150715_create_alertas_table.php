<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_encomienda', 20);
            $table->foreign('id_encomienda')->references('id_encomienda')->on('encomiendas');
            $table->string('tipo', 50);
            // valores: tiempo_excedido | dano_fisico
            $table->string('estado', 20)->default('generada');
            // valores: generada | notificada | atendida | resuelta
            $table->timestamp('fecha_generada')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};