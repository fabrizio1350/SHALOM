<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Encomienda;
use App\Models\Zona;

class ReporteController extends Controller
{
    // Ver reporte de inventario
    public function index(Request $request)
    {
        $zonas  = Zona::where('estado', '!=', 'eliminada')->get();
        $filtro_zona   = $request->get('zona');
        $filtro_estado = $request->get('estado');

        $query = Encomienda::with('zona');

        if ($filtro_zona) {
            $query->where('id_zona', $filtro_zona);
        }

        if ($filtro_estado) {
            $query->where('estado', $filtro_estado);
        }

        $encomiendas = $query->orderBy('fecha_ingreso', 'desc')->get();

        // Estadisticas generales
        $estadisticas = DB::select('
            SELECT
                COUNT(*) as total,
                COUNT(CASE WHEN estado = \'recibido\' THEN 1 END) as recibidas,
                COUNT(CASE WHEN estado = \'clasificado\' THEN 1 END) as clasificadas,
                COUNT(CASE WHEN estado = \'en_espera\' THEN 1 END) as en_espera,
                COUNT(CASE WHEN estado = \'despachado\' THEN 1 END) as despachadas,
                COUNT(CASE WHEN estado = \'daniado\' THEN 1 END) as daniadas,
                COUNT(CASE WHEN estado = \'tiempo_excedido\' THEN 1 END) as tiempo_excedido
            FROM encomiendas
        ');

        return view('reportes.index', compact('encomiendas', 'zonas', 'estadisticas', 'filtro_zona', 'filtro_estado'));
    }

    // Exportar reporte
    public function exportar(Request $request)
    {
        $filtro_zona   = $request->get('zona');
        $filtro_estado = $request->get('estado');

        $query = Encomienda::with('zona');

        if ($filtro_zona) {
            $query->where('id_zona', $filtro_zona);
        }

        if ($filtro_estado) {
            $query->where('estado', $filtro_estado);
        }

        $encomiendas = $query->orderBy('fecha_ingreso', 'desc')->get();

        // Generar CSV simple
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
                    $e->id_encomienda,
                    $e->remitente,
                    $e->destinatario,
                    $e->ciudad_destino,
                    $e->peso,
                    $e->estado,
                    $e->zona ? $e->zona->nombre : 'Sin zona',
                    $e->fecha_ingreso
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}