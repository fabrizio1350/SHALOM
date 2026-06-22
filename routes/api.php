<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TrackingController;

Route::get('/encomienda/{codigo}', [TrackingController::class, 'show']);