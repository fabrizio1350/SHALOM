<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Alerta;
use App\Models\Encomienda;
use App\DataStructures\AlertQueue;

class AlertaController extends Controller
{
    // Listar alertas activas usando función PL/pgSQL con cursor
    public function index()
    {
        // Llamar función PL/pgSQL que usa cursor internamente
        $alertasRaw = DB::select('SELECT * FROM obtener_alertas_activas()');

        // Usar AlertQueue FIFO para procesar en orden de llegada
        $queue = new AlertQueue();
        foreach ($alertasRaw as $alerta) {
            $queue->enqueue((array)$alerta);
        }

        $total_en_cola = $queue->size();

        // Obtener alertas con relación encomienda para la vista
        $alertas = Alerta::with('encomienda')
                    ->where('estado', '!=', 'resuelta')
                    ->orderBy('fecha_generada', 'asc')
                    ->get();

        return view('alertas.index', compact('alertas', 'total_en_cola'));
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

        // Si se resuelve cambiar estado de encomienda via procedimiento
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