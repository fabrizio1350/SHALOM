<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;

// Generar alertas de tiempo excedido cada dia a medianoche
Schedule::call(function () {
    DB::statement('CALL generar_alertas_tiempo()');
})->daily()->name('generar-alertas');

// Limpiar encomiendas despachadas con mas de 7 dias
Schedule::call(function () {
    DB::statement('CALL limpiar_encomiendas_despachadas()');
})->daily()->name('limpiar-despachadas');