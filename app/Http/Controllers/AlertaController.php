<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Alerta;
use App\Models\Encomienda;

class AlertaController extends Controller
{
    // Listar alertas activas
    public function index()
    {
        // Primero generar alertas de tiempo excedido via procedimiento
        DB::statement('CALL generar_alertas_tiempo()');

        $alertas = Alerta::with('encomienda')
                    ->where('estado', '!=', 'resuelta')
                    ->orderBy('fecha_generada', 'desc')
                    ->get();

        return view('alertas.index', compact('alertas'));
    }

    // Atender alerta
    public function atender(Request $request, $id)
    {
        $request->validate([
            'accion'      => 'required|in:atendida,resuelta',
            'observacion' => 'nullable|string'
        ]);

        $alerta = Alerta::findOrFail($id);
        $alerta->estado = $request->accion;
        $alerta->save();

        // Si se resuelve, cambiar estado de encomienda via procedimiento
        if ($request->accion === 'resuelta') {
            DB::statement('CALL cambiar_estado_encomienda(?, ?, ?, ?)', [
                $alerta->id_encomienda,
                'en_espera',
                'Alerta resuelta: ' . $request->observacion,
                Auth::id()
            ]);
        }

        return redirect()->route('alertas.index')->with('success', 'Alerta actualizada.');
    }
}