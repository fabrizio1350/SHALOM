<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Encomienda;
use App\Models\Zona;
use App\DataStructures\HistorialStack;
use App\DataStructures\ClasificacionTree;
use App\DataStructures\BinarySearchTree;

class EncomiendaController extends Controller
{
    // Listar encomiendas con BST para búsqueda por nombre
    public function index(Request $request)
    {
        $busqueda    = $request->get('busqueda');
        $encomiendas = Encomienda::with('zona')
                        ->orderBy('fecha_ingreso', 'desc')
                        ->get();

        $bst = new BinarySearchTree();
        foreach ($encomiendas as $enc) {
            $bst->insertar($enc->remitente, $enc->id_encomienda, $enc->toArray());
        }

        // Si hay búsqueda usa el BST, si no reutiliza la query ya realizada
        if ($busqueda) {
            $encomiendas = collect($bst->buscar($busqueda));
        }

        $totalBST = $bst->totalNodos();
        return view('encomiendas.index', compact('encomiendas', 'busqueda', 'totalBST'));
    }

    // Formulario registrar encomienda
    public function crear()
    {
        $zonas = Zona::where('estado', '!=', 'eliminada')
                     ->where('estado', '!=', 'llena')
                     ->get();
        return view('encomiendas.crear', compact('zonas'));
    }

    public function registrar(Request $request)
    {
        $request->validate([
            'remitente'      => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'destinatario'   => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'ciudad_destino' => 'required|in:Abancay,Arequipa,Ayacucho,Bagua,Cajamarca,Callao,Chiclayo,Chimbote,Cusco,Huancayo,Huanuco,Huaraz,Ica,Ilo,Iquitos,Juliaca,Lima,Moquegua,Moyobamba,Nazca,Piura,Pucallpa,Puerto Maldonado,Puno,Sullana,Tacna,Tarapoto,Tarma,Trujillo,Tumbes,Yurimaguas',
            'peso'           => 'required|numeric|min:0.1|max:70',
            'dimensiones'    => ['nullable', 'string', 'max:50', 'regex:/^\d+x\d+x\d+$/'],
            'descripcion'    => 'nullable|string|max:500',
            'imagen'         => 'nullable|file|max:2048'
        ], [
            'remitente.regex'   => 'El remitente solo puede contener letras.',
            'destinatario.regex'=> 'El destinatario solo puede contener letras.',
            'ciudad_destino.in' => 'Selecciona una ciudad válida.',
            'peso.min'          => 'El peso mínimo es 1 kg.',
            'peso.max'          => 'El peso máximo es 70 kg.',
            'dimensiones.regex' => 'Las dimensiones deben tener formato LxAxH (ej: 30x20x15).',
            'imagen.mimes'      => 'Solo se permiten imágenes JPG o PNG.',
            'imagen.max'        => 'La imagen no puede superar 2MB.',
        ]);

        // Guardar imagen si se subió
        $rutaImagen = null;
        if ($request->hasFile('imagen')) {
            $rutaImagen = $request->file('imagen')->store('encomiendas', 'public');
        }

        // Usar ClasificacionTree para clasificar el paquete
        $tree      = new ClasificacionTree();
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

        // Guardar imagen en la encomienda recién creada
        // Se filtra por remitente + destinatario para evitar colisiones en registros simultáneos
        if ($rutaImagen) {
            $ultimaEncomienda = Encomienda::where('remitente', $request->remitente)
                ->where('destinatario', $request->destinatario)
                ->latest('fecha_ingreso')
                ->first();
            $ultimaEncomienda->imagen = $rutaImagen;
            $ultimaEncomienda->save();
        }

        return redirect()->route('encomiendas.index')
            ->with('success', "Encomienda registrada — Categoría: $categoria, Estante: $estante");
    }    

    // Ver detalle de encomienda
    public function ver($id)
    {
        $encomienda = Encomienda::with('zona')->findOrFail($id);

        $historialRaw     = DB::select('SELECT * FROM obtener_historial_encomienda(?)', [$id]);
        $totalMovimientos = \App\Models\HistorialMovimiento::where('id_encomienda', $id)->count();

        // La función SQL retorna DESC (más reciente primero).
        // push() apila en ese orden y toArray() invierte → resultado final: más reciente primero ✅
        $stack = new HistorialStack();
        foreach ($historialRaw as $mov) {
            $stack->push((array)$mov);
        }
        $historial = $stack->toArray();

        $tree      = new ClasificacionTree();
        $categoria = $tree->clasificar($encomienda->peso, $encomienda->dimensiones ?? '');
        $estante   = $tree->asignarEstante($encomienda->peso, $encomienda->dimensiones ?? '');

        return view('encomiendas.ver', compact('encomienda', 'historial', 'categoria', 'estante', 'totalMovimientos'));
    }

    // Cambiar estado
    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'estado'      => 'required|in:recibido,clasificado,en_espera,despachado,daniado',
            'observacion' => 'nullable|string'
        ]);

        DB::statement('CALL cambiar_estado_encomienda(?, ?, ?, ?)', [
            $id, $request->estado, $request->observacion, Auth::id()
        ]);

        return redirect()->route('encomiendas.ver', $id)->with('success', 'Estado actualizado.');
    }

    // Reubicar encomienda
    public function reubicar(Request $request, $id)
    {
        $request->validate(['observacion' => 'required|string']);

        DB::statement('CALL reubicar_encomienda(?, ?, ?)', [
            $id, $request->observacion, Auth::id()
        ]);

        return redirect()->route('encomiendas.ver', $id)->with('success', 'Encomienda reubicada correctamente.');
    }

    // Despachar encomienda
    public function despachar(Request $request, $id)
    {
        DB::statement('CALL cambiar_estado_encomienda(?, ?, ?, ?)', [
            $id, 'despachado', 'Encomienda despachada', Auth::id()
        ]);

        return redirect()->route('encomiendas.index')->with('success', 'Encomienda despachada.');
    }

    // Notificar daño
    public function notificarDanio(Request $request, $id)
    {
        $request->validate(['observacion' => 'required|string']);

        DB::statement('CALL cambiar_estado_encomienda(?, ?, ?, ?)', [
            $id, 'daniado', $request->observacion, Auth::id()
        ]);

        return redirect()->route('encomiendas.ver', $id)->with('success', 'Daño registrado.');
    }
}