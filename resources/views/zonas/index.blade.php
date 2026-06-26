@extends('layouts.app')

@section('titulo', 'Zonas')

@section('contenido')
<div class="card">

    {{-- Header --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px">
        <div>
            <h2 style="color:var(--dark); font-size:22px; font-weight:800; margin-bottom:3px">
                <i class="fas fa-warehouse" style="color:var(--primary)"></i> Zonas del Almacén
            </h2>
            <p style="color:var(--muted); font-size:13px">Gestión de espacios físicos del almacén</p>
        </div>
        <a href="{{ route('zonas.crear') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nueva Zona
        </a>
    </div>

    {{-- Tabla --}}
    <div style="overflow-x:auto; border-radius:var(--radius); border:1px solid var(--border)">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Capacidad</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($zonas as $zona)
            @php
                $estadoStyles = [
                    'disponible'           => ['bg'=>'#D1FAE5', 'color'=>'#065F46', 'icon'=>'fa-check-circle'],
                    'parcialmente_ocupada' => ['bg'=>'#FEF3C7', 'color'=>'#92400E', 'icon'=>'fa-adjust'],
                    'llena'                => ['bg'=>'#FEE2E2', 'color'=>'#991B1B', 'icon'=>'fa-times-circle'],
                    'eliminada'            => ['bg'=>'#F1F5F9', 'color'=>'#64748B', 'icon'=>'fa-ban'],
                ];
                $style = $estadoStyles[$zona->estado] ?? ['bg'=>'#F1F5F9', 'color'=>'#64748B', 'icon'=>'fa-circle'];
            @endphp
            <tr>
                <td>
                    <span style="font-size:12px; font-weight:700; color:var(--muted)">#{{ $zona->id }}</span>
                </td>
                <td>
                    <span style="font-weight:700; color:var(--dark); display:flex; align-items:center; gap:6px">
                        <i class="fas fa-warehouse" style="color:var(--primary); font-size:12px"></i>
                        {{ $zona->nombre }}
                    </span>
                </td>
                <td>
                    <span style="display:flex; align-items:center; gap:5px; font-size:13px">
                        <i class="fas fa-boxes" style="color:var(--muted); font-size:11px"></i>
                        <strong>{{ $zona->capacidad }}</strong>
                        <span style="color:var(--muted)">paquetes</span>
                    </span>
                </td>
                <td>
                    <span class="badge" style="background:{{ $style['bg'] }}; color:{{ $style['color'] }}">
                        <i class="fas {{ $style['icon'] }}" style="font-size:9px"></i>
                        {{ strtoupper(str_replace('_', ' ', $zona->estado)) }}
                    </span>
                </td>
                <td>
                    @if($zona->estado !== 'eliminada')
                    <form action="{{ route('zonas.estado', $zona->id) }}" method="POST" style="display:inline">
                        @csrf
                        <button type="submit" class="btn btn-danger"
                                style="font-size:12px; padding:5px 12px"
                                onclick="return confirm('¿Eliminar esta zona?')">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </form>
                    @else
                    <span style="color:var(--muted); font-size:12px; font-style:italic">
                        <i class="fas fa-ban"></i> Eliminada
                    </span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center; padding:40px; color:var(--muted)">
                    <i class="fas fa-warehouse" style="font-size:24px; opacity:0.3; display:block; margin-bottom:8px"></i>
                    No hay zonas registradas.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection