<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Encomienda;
use App\Models\Zona;
use App\DataStructures\AlertQueue;
use App\DataStructures\HistorialStack;
use App\DataStructures\ClasificacionTree;

class EncomiendaController extends Controller
{
    // Listar encomiendas (dashboard)
    public function index()
    {
        $encomiendas = Encomienda::with('zona')
                        ->orderBy('fecha_ingreso', 'desc')
                        ->get();
        return view('encomiendas.index', compact('encomiendas'));
    }

    // Formulario registrar encomienda
    public function crear()
    {
        $zonas = Zona::where('estado', '!=', 'eliminada')
                     ->where('estado', '!=', 'llena')
                     ->get();
        return view('encomiendas.crear', compact('zonas'));
    }

    // Registrar encomienda — llama al procedimiento PL/pgSQL
    public function registrar(Request $request)
    {
        $request->validate([
            'remitente'      => 'required|string|max:100',
            'destinatario'   => 'required|string|max:100',
            'ciudad_destino' => 'required|string|max:100',
            'peso'           => 'required|numeric|min:0.1',
            'dimensiones'    => 'nullable|string|max:50',
            'descripcion'    => 'nullable|string'
        ]);

        // Usar ClasificacionTree para clasificar el paquete
        $tree     = new ClasificacionTree();
        $categoria = $tree->clasificar($request->peso, $request->dimensiones ?? '');
        $estante   = $tree->asignarEstante($request->peso, $request->dimensiones ?? '');

        // Llamar al procedimiento PL/pgSQL
        DB::statement('CALL registrar_encomienda(?, ?, ?, ?, ?, ?, ?)', [
            $request->remitente,
            $request->destinatario,
            $request->ciudad_destino,
            $request->peso,
            $request->dimensiones,
            $request->descripcion,
            Auth::id()
        ]);

        return redirect()->route('encomiendas.index')
               ->with('success', "Encomienda registrada — Categoría: $categoria, Estante: $estante");
    }

    // Ver detalle de encomienda
    public function ver($id)
    {
        $encomienda = Encomienda::with('zona')->findOrFail($id);

        // Obtener historial via función PL/pgSQL
        $historialRaw = DB::select('SELECT * FROM obtener_historial_encomienda(?)', [$id]);

        // Usar HistorialStack para ordenar más reciente primero
        $stack = new HistorialStack();
        foreach (array_reverse($historialRaw) as $mov) {
            $stack->push((array)$mov);
        }
        $historial = $stack->toArray();

        // Usar ClasificacionTree para mostrar categoría del paquete
        $tree      = new ClasificacionTree();
        $categoria = $tree->clasificar($encomienda->peso, $encomienda->dimensiones ?? '');
        $estante   = $tree->asignarEstante($encomienda->peso, $encomienda->dimensiones ?? '');

        return view('encomiendas.ver', compact('encomienda', 'historial', 'categoria', 'estante'));
    }

    // Cambiar estado — llama al procedimiento PL/pgSQL
    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'estado'      => 'required|in:recibido,clasificado,en_espera,despachado,daniado,tiempo_excedido',
            'observacion' => 'nullable|string'
        ]);

        DB::statement('CALL cambiar_estado_encomienda(?, ?, ?, ?)', [
            $id,
            $request->estado,
            $request->observacion,
            Auth::id()
        ]);

        return redirect()->route('encomiendas.ver', $id)->with('success', 'Estado actualizado.');
    }

    // Reubicar encomienda por tiempo excedido
    public function reubicar(Request $request, $id)
    {
        $request->validate([
            'observacion' => 'required|string'
        ]);

        DB::statement('CALL reubicar_encomienda(?, ?, ?)', [
            $id,
            $request->observacion,
            Auth::id()
        ]);

        return redirect()->route('encomiendas.ver', $id)->with('success', 'Encomienda reubicada correctamente.');
    }

    // Despachar encomienda
    public function despachar(Request $request, $id)
    {
        DB::statement('CALL cambiar_estado_encomienda(?, ?, ?, ?)', [
            $id,
            'despachado',
            'Encomienda despachada',
            Auth::id()
        ]);

        return redirect()->route('encomiendas.index')->with('success', 'Encomienda despachada.');
    }

    // Notificar daño
    public function notificarDanio(Request $request, $id)
    {
        $request->validate([
            'observacion' => 'required|string'
        ]);

        DB::statement('CALL cambiar_estado_encomienda(?, ?, ?, ?)', [
            $id,
            'daniado',
            $request->observacion,
            Auth::id()
        ]);

        return redirect()->route('encomiendas.ver', $id)->with('success', 'Daño registrado.');
    }
}