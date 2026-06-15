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
}