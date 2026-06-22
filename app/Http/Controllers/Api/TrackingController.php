<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Encomienda;
use Illuminate\Http\JsonResponse;

class TrackingController extends Controller
{
    public function show(string $codigo): JsonResponse
    {
        $encomienda = Encomienda::with('zona')
                        ->where('id_encomienda', $codigo)
                        ->first();

        if (!$encomienda) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Encomienda no encontrada.'
            ], 404);
        }

        return response()->json([
            'success'       => true,
            'codigo'        => $encomienda->id_encomienda,
            'remitente'     => $encomienda->remitente,
            'destinatario'  => $encomienda->destinatario,
            'ciudad_destino'=> $encomienda->ciudad_destino,
            'peso'          => $encomienda->peso,
            'dimensiones'   => $encomienda->dimensiones,
            'estado'        => $encomienda->estado,
            'zona'          => $encomienda->zona ? $encomienda->zona->nombre : 'Sin zona',
            'fecha_ingreso' => $encomienda->fecha_ingreso,
        ]);
    }
}