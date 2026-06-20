<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arbol_almacen', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nivel');
            $table->string('tipo', 20); // almacen, zona, encomienda
            $table->string('id_nodo', 20);
            $table->string('nombre', 200);
            $table->string('estado', 25);
            $table->unsignedInteger('id_padre')->nullable();
            $table->foreign('id_padre')->references('id')->on('arbol_almacen');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arbol_almacen');
    }
};