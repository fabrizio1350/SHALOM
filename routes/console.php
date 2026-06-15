<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;

// Generar alertas de tiempo excedido cada dia a medianoche
Schedule::call(function () {
    DB::statement('CALL generar_alertas_tiempo()');
})->daily()->name('generar-alertas');