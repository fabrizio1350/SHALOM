<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tiempo_maximo_dias')->default(7);
            $table->unsignedInteger('id_zona_reubicacion')->nullable();
            $table->foreign('id_zona_reubicacion')->references('id')->on('zonas');
            $table->timestamp('fecha_actualizacion')->useCurrent();
            $table->unsignedBigInteger('id_admin')->nullable();
            $table->foreign('id_admin')->references('id')->on('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion');
    }
};