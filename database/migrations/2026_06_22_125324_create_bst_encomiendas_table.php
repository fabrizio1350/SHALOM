<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bst_encomiendas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 100);
            $table->string('id_encomienda', 20);
            $table->unsignedInteger('id_izquierdo')->nullable();
            $table->unsignedInteger('id_derecho')->nullable();
            $table->foreign('id_izquierdo')->references('id')->on('bst_encomiendas');
            $table->foreign('id_derecho')->references('id')->on('bst_encomiendas');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bst_encomiendas');
    }
};