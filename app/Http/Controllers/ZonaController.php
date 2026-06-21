<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Zona;

class ZonaController extends Controller
{
    // Listar zonas
    public function index()
    {
        $zonas = Zona::where('estado', '!=', 'eliminada')
                     ->orderBy('nombre')
                     ->get();
        return view('zonas.index', compact('zonas'));
    }

    // Formulario crear zona
    public function crear()
    {
        return view('zonas.crear');
    }

    // Guardar nueva zona
    public function store(Request $request)
    {
        $request->validate([
            'nombre'    => 'required|string|max:50|unique:zonas',
            'capacidad' => 'required|integer|min:1'
        ]);

        Zona::create([
            'nombre'    => $request->nombre,
            'capacidad' => $request->capacidad,
            'estado'    => 'disponible'
        ]);

        return redirect()->route('zonas.index')->with('success', 'Zona creada correctamente.');
    }

    // Cambiar estado zona
    public function cambiarEstado(Request $request, $id)
    {
        $zona = Zona::findOrFail($id);

        // Si está eliminada no se puede cambiar
        if ($zona->estado === 'eliminada') {
            return redirect()->route('zonas.index')->with('error', 'La zona ya está eliminada.');
        }

        $zona->estado = 'eliminada';
        $zona->save();

        // Actualizar estado de zona via procedimiento
        DB::statement('CALL actualizar_estado_zona(?)', [$id]);

        return redirect()->route('zonas.index')->with('success', 'Zona eliminada correctamente.');
    }
        // Vista 2D del almacen
// Vista 2D del almacen
public function almacen()
{
    // Usar función PL/pgSQL que consulta arbol_almacen con WITH RECURSIVE
    $nodos = DB::select('SELECT * FROM obtener_arbol_almacen()');

    $tree        = new \App\DataStructures\ClasificacionTree();
    $almacenTree = new \App\DataStructures\AlmacenTree();

    // Separar zonas y encomiendas del resultado
    $zonas       = collect($nodos)->where('tipo', 'zona');
    $encomiendas = collect($nodos)->where('tipo', 'encomienda');

    // Obtener datos completos de zonas para el AlmacenTree
    $zonasCompletas = \App\Models\Zona::where('estado', '!=', 'eliminada')
                        ->with(['encomiendas' => function($q) {
                            $q->where('estado', '!=', 'despachado');
                        }])
                        ->get();

    // Construir árbol con AlmacenTree
    foreach ($zonasCompletas as $zona) {
        $almacenTree->agregarZona($zona->toArray());
        foreach ($zona->encomiendas as $enc) {
            $estante = $tree->asignarEstante($enc->peso, $enc->dimensiones ?? '');
            $almacenTree->agregarPaquete($enc->toArray(), $estante);
        }
    }

    $arbol         = $almacenTree->getTree();
    $totalNodos    = count($nodos); // Total de nodos del árbol SQL

    return view('zonas.almacen', compact('arbol', 'totalNodos'));
}
}