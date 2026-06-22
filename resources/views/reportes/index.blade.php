@extends('layouts.app')

@section('titulo', 'Reportes')

@section('contenido')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px">
        <h2>📊 Reporte de Inventario</h2>
        <a href="{{ route('reportes.exportar', request()->query()) }}" class="btn btn-success">
            ⬇ Exportar CSV
        </a>
    </div>

    {{-- Estadisticas --}}
    @if(isset($estadisticas[0]))
    @php $s = $estadisticas[0]; @endphp
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:15px; margin-bottom:25px">
        <div style="background:#1a1a2e; color:white; padding:15px; border-radius:8px; text-align:center">
            <h3>{{ $s->total }}</h3>
            <p>Total</p>
        </div>
        <div style="background:#28a745; color:white; padding:15px; border-radius:8px; text-align:center">
            <h3>{{ $s->despachadas }}</h3>
            <p>Despachadas</p>
        </div>
        <div style="background:#ffc107; color:black; padding:15px; border-radius:8px; text-align:center">
            <h3>{{ $s->en_espera }}</h3>
            <p>En Espera</p>
        </div>
        <div style="background:#dc3545; color:white; padding:15px; border-radius:8px; text-align:center">
            <h3>{{ $s->tiempo_excedido }}</h3>
            <p>Tiempo Excedido</p>
        </div>
    </div>
    @endif

    {{-- Filtros --}}
    <form method="GET" action="{{ route('reportes.index') }}" style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap">
        <select name="zona" style="width:auto">
            <option value="">Todas las zonas</option>
            @foreach($zonas as $zona)
                <option value="{{ $zona->id }}" {{ $filtro_zona == $zona->id ? 'selected' : '' }}>
                    {{ $zona->nombre }}
                </option>
            @endforeach
        </select>
        <select name="estado" style="width:auto">
            <option value="">Todos los estados</option>
            <option value="recibido" {{ $filtro_estado === 'recibido' ? 'selected' : '' }}>Recibido</option>
            <option value="clasificado" {{ $filtro_estado === 'clasificado' ? 'selected' : '' }}>Clasificado</option>
            <option value="en_espera" {{ $filtro_estado === 'en_espera' ? 'selected' : '' }}>En Espera</option>
            <option value="despachado" {{ $filtro_estado === 'despachado' ? 'selected' : '' }}>Despachado</option>
            <option value="daniado" {{ $filtro_estado === 'daniado' ? 'selected' : '' }}>Dañado</option>
            <option value="tiempo_excedido" {{ $filtro_estado === 'tiempo_excedido' ? 'selected' : '' }}>Tiempo Excedido</option>
        </select>
        <select name="orden" style="width:auto">
            <option value="desc" {{ $filtro_orden === 'desc' ? 'selected' : '' }}>⬇ Mayor peso primero (HeapSort)</option>
            <option value="asc" {{ $filtro_orden === 'asc' ? 'selected' : '' }}>⬆ Menor peso primero (HeapSort)</option>
        </select>
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="{{ route('reportes.index') }}" class="btn btn-warning">Limpiar</a>
    </form>

    <p style="color:#666; font-size:13px; margin-bottom:15px">
        📊 Ordenado por peso usando <strong>HeapSort</strong> — O(n log n)
    </p>

    {{-- Tabla --}}
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
                <td>{{ $enc['id_encomienda'] }}</td>
                <td>{{ $enc['remitente'] }}</td>
                <td>{{ $enc['destinatario'] }}</td>
                <td>{{ $enc['ciudad_destino'] }}</td>
                <td>{{ $enc['peso'] }} kg</td>
                <td>{{ $enc['zona']['nombre'] ?? 'Sin zona' }}</td>
                <td>
                    <span class="badge badge-{{ $enc['estado'] }}">
                        {{ strtoupper(str_replace('_', ' ', $enc['estado'])) }}
                    </span>
                </td>
                <td>{{ \Carbon\Carbon::parse($enc['fecha_ingreso'])->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center; padding:20px">No hay encomiendas.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection