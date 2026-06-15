<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Encomienda;
use App\Models\Zona;

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

        return redirect()->route('encomiendas.index')->with('success', 'Encomienda registrada correctamente.');
    }

    // Ver detalle de encomienda
    public function ver($id)
    {
        $encomienda = Encomienda::with('zona')->findOrFail($id);

        // Obtener historial via función PL/pgSQL
        $historial = DB::select('SELECT * FROM obtener_historial_encomienda(?)', [$id]);

        return view('encomiendas.ver', compact('encomienda', 'historial'));
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