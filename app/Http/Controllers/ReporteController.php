<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Encomienda;
use App\Models\Zona;
use App\DataStructures\HeapSort;

class ReporteController extends Controller
{
    // Ver reporte de inventario
    public function index(Request $request)
    {
        $zonas         = Zona::where('estado', '!=', 'eliminada')->get();
        $filtro_zona   = $request->get('zona');
        $filtro_estado = $request->get('estado');
        $filtro_orden  = $request->get('orden', 'desc');

        $query = Encomienda::with('zona');

        if ($filtro_zona) {
            $query->where('id_zona', $filtro_zona);
        }

        if ($filtro_estado) {
            $query->where('estado', $filtro_estado);
        } else {
            $query->where(function($q) {
                $q->where('estado', '!=', 'despachado')
                  ->orWhere(function($q2) {
                      $q2->where('estado', 'despachado')
                         ->where('updated_at', '>=', now()->subDays(7));
                  });
            });
        }

        $encomiendas = $query->get();

        // HeapSort para ordenar por peso (mayor a menor por defecto)
        $heap        = new HeapSort($encomiendas->toArray());
        $encomiendas = collect($heap->sort('peso', $filtro_orden));

        // Estadisticas generales
        $estadisticas = [
            (object) [
                'total'          => $encomiendas->count(),
                'recibidas'      => $encomiendas->where('estado', 'recibido')->count(),
                'clasificadas'   => $encomiendas->where('estado', 'clasificado')->count(),
                'en_espera'      => $encomiendas->where('estado', 'en_espera')->count(),
                'despachadas'    => $encomiendas->where('estado', 'despachado')->count(),
                'daniadas'       => $encomiendas->where('estado', 'daniado')->count(),
                'tiempo_excedido'=> $encomiendas->where('estado', 'tiempo_excedido')->count(),
            ]
        ];

        return view('reportes.index', compact(
            'encomiendas', 'zonas', 'estadisticas',
            'filtro_zona', 'filtro_estado', 'filtro_orden'
        ));
    }

    // Exportar reporte
    public function exportar(Request $request)
    {
        $filtro_zona   = $request->get('zona');
        $filtro_estado = $request->get('estado');
        $filtro_orden  = $request->get('orden', 'desc');

        $query = Encomienda::with('zona');

        if ($filtro_zona)   $query->where('id_zona', $filtro_zona);
        if ($filtro_estado) $query->where('estado', $filtro_estado);

        $encomiendas = $query->get();

        // HeapSort antes de exportar
        $heap        = new HeapSort($encomiendas->toArray());
        $encomiendas = collect($heap->sort('peso', $filtro_orden));

        $filename = 'reporte_shalom_' . now()->format('Y_m_d') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename"
        ];

        $callback = function () use ($encomiendas) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Codigo', 'Remitente', 'Destinatario', 'Ciudad', 'Peso', 'Estado', 'Zona', 'Fecha Ingreso']);

            foreach ($encomiendas as $e) {
                fputcsv($file, [
                    $e['id_encomienda'],
                    $e['remitente'],
                    $e['destinatario'],
                    $e['ciudad_destino'],
                    $e['peso'],
                    $e['estado'],
                    $e['zona']['nombre'] ?? 'Sin zona',
                    $e['fecha_ingreso']
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}