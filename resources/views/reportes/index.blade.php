@extends('layouts.app')

@section('titulo', 'Reportes')

@section('contenido')
<div class="card">

    {{-- Header --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px">
        <div>
            <h2 style="color:var(--dark); font-size:22px; font-weight:800; margin-bottom:3px">
                <i class="fas fa-chart-bar" style="color:var(--primary)"></i> Reporte de Inventario
            </h2>
            <p style="color:var(--muted); font-size:13px">
                Ordenado por peso usando <strong>HeapSort</strong> — O(n log n)
            </p>
        </div>
        <a href="{{ route('reportes.exportar', request()->query()) }}" class="btn btn-success">
            <i class="fas fa-file-csv"></i> Exportar CSV
        </a>
    </div>

    {{-- Estadísticas --}}
    @if(isset($estadisticas[0]))
    @php $s = $estadisticas[0]; @endphp
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:24px">

        <div class="stat-card" style="border-top:3px solid var(--dark)">
            <div class="stat-icon"><i class="fas fa-boxes"></i></div>
            <div class="stat-number" style="color:var(--dark)">{{ $s->total }}</div>
            <div class="stat-label" style="color:var(--muted)">Total</div>
        </div>

        <div class="stat-card" style="border-top:3px solid #10B981">
            <div class="stat-icon" style="color:#10B981"><i class="fas fa-check-circle"></i></div>
            <div class="stat-number" style="color:#10B981">{{ $s->despachadas }}</div>
            <div class="stat-label" style="color:#10B981">Despachadas</div>
        </div>

        <div class="stat-card" style="border-top:3px solid #F59E0B">
            <div class="stat-icon" style="color:#F59E0B"><i class="fas fa-hourglass-half"></i></div>
            <div class="stat-number" style="color:#F59E0B">{{ $s->en_espera }}</div>
            <div class="stat-label" style="color:#F59E0B">En Espera</div>
        </div>

        <div class="stat-card" style="border-top:3px solid var(--primary)">
            <div class="stat-icon" style="color:var(--primary)"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-number" style="color:var(--primary)">{{ $s->tiempo_excedido }}</div>
            <div class="stat-label" style="color:var(--primary)">Tiempo Excedido</div>
        </div>

    </div>
    @endif

    {{-- Filtros --}}
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:10px;
                padding:16px; margin-bottom:20px">
        <p style="font-size:12px; font-weight:700; color:var(--muted); text-transform:uppercase;
                  letter-spacing:0.5px; margin-bottom:12px">
            <i class="fas fa-filter" style="color:var(--primary)"></i> Filtros
        </p>
        <form method="GET" action="{{ route('reportes.index') }}"
              style="display:flex; gap:10px; flex-wrap:wrap; align-items:center">
            <select name="zona" style="width:auto; margin-bottom:0">
                <option value="">Todas las zonas</option>
                @foreach($zonas as $zona)
                    <option value="{{ $zona->id }}" {{ $filtro_zona == $zona->id ? 'selected' : '' }}>
                        {{ $zona->nombre }}
                    </option>
                @endforeach
            </select>
            <select name="estado" style="width:auto; margin-bottom:0">
                <option value="">Todos los estados</option>
                <option value="recibido"        {{ $filtro_estado === 'recibido' ? 'selected' : '' }}>Recibido</option>
                <option value="clasificado"     {{ $filtro_estado === 'clasificado' ? 'selected' : '' }}>Clasificado</option>
                <option value="en_espera"       {{ $filtro_estado === 'en_espera' ? 'selected' : '' }}>En Espera</option>
                <option value="despachado"      {{ $filtro_estado === 'despachado' ? 'selected' : '' }}>Despachado</option>
                <option value="daniado"         {{ $filtro_estado === 'daniado' ? 'selected' : '' }}>Dañado</option>
                <option value="tiempo_excedido" {{ $filtro_estado === 'tiempo_excedido' ? 'selected' : '' }}>Tiempo Excedido</option>
            </select>
            <select name="orden" style="width:auto; margin-bottom:0">
                <option value="desc" {{ $filtro_orden === 'desc' ? 'selected' : '' }}>Mayor peso primero</option>
                <option value="asc"  {{ $filtro_orden === 'asc' ? 'selected' : '' }}>Menor peso primero</option>
            </select>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Filtrar
            </button>
            <a href="{{ route('reportes.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Limpiar
            </a>
        </form>
    </div>

    {{-- Tabla --}}
    <div style="overflow-x:auto; border-radius:var(--radius); border:1px solid var(--border)">
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
            @php
                $estLabel = [
                    'recibido'        => 'RECIBIDO',
                    'clasificado'     => 'CLASIFICADO',
                    'en_espera'       => 'EN ESPERA',
                    'despachado'      => 'DESPACHADO',
                    'daniado'         => 'DAÑADO',
                    'tiempo_excedido' => 'TIEMPO EXCEDIDO',
                ][$enc['estado']] ?? strtoupper(str_replace('_', ' ', $enc['estado']));
            @endphp
            <tr>
                <td>
                    <span style="font-size:11px; font-weight:700; color:var(--primary);
                                 font-family:monospace">{{ $enc['id_encomienda'] }}</span>
                </td>
                <td style="font-weight:500">{{ $enc['remitente'] }}</td>
                <td style="color:var(--muted)">{{ $enc['destinatario'] }}</td>
                <td>
                    <span style="display:flex; align-items:center; gap:4px; font-size:13px">
                        <i class="fas fa-map-marker-alt" style="color:var(--primary); font-size:10px"></i>
                        {{ $enc['ciudad_destino'] }}
                    </span>
                </td>
                <td>
                    <strong style="color:var(--dark)">{{ $enc['peso'] }}</strong>
                    <span style="color:var(--muted); font-size:11px"> kg</span>
                </td>
                <td>
                    <span style="background:var(--primary-light); border:1px solid rgba(232,39,42,0.2);
                                 color:var(--primary); padding:3px 10px; border-radius:20px;
                                 font-size:11px; font-weight:600; white-space:nowrap">
                        <i class="fas fa-warehouse" style="font-size:9px"></i>
                        {{ $enc['zona']['nombre'] ?? 'Sin zona' }}
                    </span>
                </td>
                <td>
                    <span class="badge badge-{{ $enc['estado'] }}">{{ $estLabel }}</span>
                </td>
                <td style="font-size:12px; color:var(--muted); white-space:nowrap">
                    <i class="fas fa-calendar-alt" style="font-size:10px"></i>
                    {{ \Carbon\Carbon::parse($enc['fecha_ingreso'])->format('d/m/Y H:i') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center; padding:40px; color:var(--muted)">
                    <i class="fas fa-inbox" style="font-size:24px; opacity:0.3; display:block; margin-bottom:8px"></i>
                    No hay encomiendas con los filtros aplicados.
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
</style>
@endsection