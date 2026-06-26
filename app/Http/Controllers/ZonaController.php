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

        if ($zona->estado === 'eliminada') {
            return redirect()->route('zonas.index')
                ->with('error', 'La zona ya está eliminada.');
        }

        // Verificar si tiene encomiendas activas
        $encomiendas = $zona->encomiendas()
            ->where('estado', '!=', 'despachado')
            ->count();

        if ($encomiendas > 0) {
            return redirect()->route('zonas.index')
                ->with('error', "No se puede eliminar la zona. Tiene {$encomiendas} encomienda(s) activa(s).");
        }

        $zona->estado = 'eliminada';
        $zona->save();

        return redirect()->route('zonas.index')->with('success', 'Zona eliminada correctamente.');
    }

    // Vista 2D del almacen
    public function almacen()
    {
        $nodos = DB::select('SELECT * FROM obtener_arbol_almacen()');

        $tree        = new \App\DataStructures\ClasificacionTree();
        $almacenTree = new \App\DataStructures\AlmacenTree();

        $zonasCompletas = \App\Models\Zona::where('estado', '!=', 'eliminada')
                            ->with(['encomiendas' => function($q) {
                                $q->where('estado', '!=', 'despachado');
                            }])
                            ->get();

        foreach ($zonasCompletas as $zona) {
            $almacenTree->agregarZona($zona->toArray());
            foreach ($zona->encomiendas as $enc) {
                $estante = $tree->asignarEstante($enc->peso, $enc->dimensiones ?? '');
                $almacenTree->agregarPaquete($enc->toArray(), $estante);
            }
        }

        $arbol      = $almacenTree->getTree();
        $totalNodos = count($nodos);

        return view('zonas.almacen', compact('arbol', 'totalNodos'));
    }
}