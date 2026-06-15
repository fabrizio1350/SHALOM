<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encomiendas', function (Blueprint $table) {
            $table->string('id_encomienda', 20)->primary();
            $table->string('remitente', 100);
            $table->string('destinatario', 100);
            $table->string('ciudad_destino', 100);
            $table->decimal('peso', 8, 2);
            $table->string('dimensiones', 50)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('estado', 20)->default('recibido');
            $table->unsignedInteger('id_zona')->nullable();
            $table->foreign('id_zona')->references('id')->on('zonas');
            $table->timestamp('fecha_ingreso')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encomiendas');
    }
};