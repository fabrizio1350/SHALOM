@extends('layouts.app')

@section('titulo', 'Reportes')

@section('contenido')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px">
        <div>
            <h2 style="color:#2c3e50; margin-bottom:4px">📊 Reporte de Inventario</h2>
            <p style="color:#888; font-size:13px">Ordenado por peso usando <strong>HeapSort</strong> — O(n log n)</p>
        </div>
        <a href="{{ route('reportes.exportar', request()->query()) }}" class="btn btn-success">
            ⬇ Exportar CSV
        </a>
    </div>

    {{-- Estadísticas --}}
    @if(isset($estadisticas[0]))
    @php $s = $estadisticas[0]; @endphp
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:25px">
        <div style="background:linear-gradient(135deg, #2c3e50, #3d5166); color:white; padding:18px; border-radius:10px; text-align:center; box-shadow:0 2px 6px rgba(0,0,0,0.15)">
            <h3 style="font-size:28px; margin-bottom:4px">{{ $s->total }}</h3>
            <p style="font-size:12px; opacity:0.8">📦 Total</p>
        </div>
        <div style="background:linear-gradient(135deg, #27ae60, #2ecc71); color:white; padding:18px; border-radius:10px; text-align:center; box-shadow:0 2px 6px rgba(0,0,0,0.15)">
            <h3 style="font-size:28px; margin-bottom:4px">{{ $s->despachadas }}</h3>
            <p style="font-size:12px; opacity:0.8">✅ Despachadas</p>
        </div>
        <div style="background:linear-gradient(135deg, #e67e22, #f39c12); color:white; padding:18px; border-radius:10px; text-align:center; box-shadow:0 2px 6px rgba(0,0,0,0.15)">
            <h3 style="font-size:28px; margin-bottom:4px">{{ $s->en_espera }}</h3>
            <p style="font-size:12px; opacity:0.8">⏳ En Espera</p>
        </div>
        <div style="background:linear-gradient(135deg, #c0392b, #e74c3c); color:white; padding:18px; border-radius:10px; text-align:center; box-shadow:0 2px 6px rgba(0,0,0,0.15)">
            <h3 style="font-size:28px; margin-bottom:4px">{{ $s->tiempo_excedido }}</h3>
            <p style="font-size:12px; opacity:0.8">⚠️ Tiempo Excedido</p>
        </div>
    </div>
    @endif

    {{-- Filtros --}}
    <div style="background:#f8f9fa; border-radius:8px; padding:15px; margin-bottom:20px">
        <form method="GET" action="{{ route('reportes.index') }}"
              style="display:flex; gap:10px; flex-wrap:wrap; align-items:center">
            <select name="zona" style="width:auto; margin-bottom:0">
                <option value="">🏢 Todas las zonas</option>
                @foreach($zonas as $zona)
                    <option value="{{ $zona->id }}" {{ $filtro_zona == $zona->id ? 'selected' : '' }}>
                        {{ $zona->nombre }}
                    </option>
                @endforeach
            </select>
            <select name="estado" style="width:auto; margin-bottom:0">
                <option value="">📋 Todos los estados</option>
                <option value="recibido"        {{ $filtro_estado === 'recibido' ? 'selected' : '' }}>Recibido</option>
                <option value="clasificado"     {{ $filtro_estado === 'clasificado' ? 'selected' : '' }}>Clasificado</option>
                <option value="en_espera"       {{ $filtro_estado === 'en_espera' ? 'selected' : '' }}>En Espera</option>
                <option value="despachado"      {{ $filtro_estado === 'despachado' ? 'selected' : '' }}>Despachado</option>
                <option value="daniado"         {{ $filtro_estado === 'daniado' ? 'selected' : '' }}>Dañado</option>
                <option value="tiempo_excedido" {{ $filtro_estado === 'tiempo_excedido' ? 'selected' : '' }}>Tiempo Excedido</option>
            </select>
            <select name="orden" style="width:auto; margin-bottom:0">
                <option value="desc" {{ $filtro_orden === 'desc' ? 'selected' : '' }}>⬇ Mayor peso (HeapSort)</option>
                <option value="asc"  {{ $filtro_orden === 'asc' ? 'selected' : '' }}>⬆ Menor peso (HeapSort)</option>
            </select>
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="{{ route('reportes.index') }}" class="btn btn-secondary">✕ Limpiar</a>
        </form>
    </div>

    {{-- Tabla --}}
    <div style="overflow-x:auto">
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Remitente</th>
                <th>Destinatario</th>
                <th>Ciudad</th>
                <th>Peso</th>
                <th>Zona</th>
                <th>Estado</th>
                <th>Fecha Ingreso</th>
            </tr>
        </thead>
        <tbody>
            @forelse($encomiendas as $enc)
            <tr>
                <td style="font-size:12px; font-weight:bold; color:#e74c3c">{{ $enc['id_encomienda'] }}</td>
                <td>{{ $enc['remitente'] }}</td>
                <td>{{ $enc['destinatario'] }}</td>
                <td>{{ $enc['ciudad_destino'] }}</td>
                <td><strong>{{ $enc['peso'] }} kg</strong></td>
                <td>
                    <span style="background:#fef9f9; border:1px solid #e74c3c; color:#e74c3c;
                                 padding:2px 8px; border-radius:10px; font-size:12px">
                        {{ $enc['zona']['nombre'] ?? 'Sin zona' }}
                    </span>
                </td>
                <td>
                    <span class="badge badge-{{ $enc['estado'] }}">
                        {{ strtoupper(str_replace('_', ' ', $enc['estado'])) }}
                    </span>
                </td>
                <td style="font-size:12px; color:#888">
                    {{ \Carbon\Carbon::parse($enc['fecha_ingreso'])->format('d/m/Y H:i') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center; padding:30px; color:#888">
                    📭 No hay encomiendas con los filtros aplicados.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<style>
@media (max-width: 768px) {
    div[style*="grid-template-columns:repeat(4,1fr)"] {
        grid-template-columns: repeat(2,1fr) !important;
    }
}
@media (max-width: 480px) {
    div[style*="grid-template-columns:repeat(4,1fr)"] {
        grid-template-columns: 1fr 1fr !important;
    }
}
</style>
@endsection