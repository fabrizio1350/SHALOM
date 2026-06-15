@extends('layouts.app')

@section('titulo', 'Detalle Encomienda')

@section('contenido')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px">
        <h2>📦 {{ $encomienda->id_encomienda }}</h2>
        <a href="{{ route('encomiendas.index') }}" class="btn btn-warning">← Volver</a>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:25px">
        <div>
            <p><strong>Remitente:</strong> {{ $encomienda->remitente }}</p>
            <p><strong>Destinatario:</strong> {{ $encomienda->destinatario }}</p>
            <p><strong>Ciudad Destino:</strong> {{ $encomienda->ciudad_destino }}</p>
            <p><strong>Peso:</strong> {{ $encomienda->peso }} kg</p>
        </div>
        <div>
            <p><strong>Dimensiones:</strong> {{ $encomienda->dimensiones ?? 'No especificado' }}</p>
            <p><strong>Zona:</strong> {{ $encomienda->zona ? $encomienda->zona->nombre : 'Sin zona' }}</p>
            <p><strong>Fecha Ingreso:</strong> {{ \Carbon\Carbon::parse($encomienda->fecha_ingreso)->format('d/m/Y H:i') }}</p>
            <p><strong>Estado:</strong>
                <span class="badge badge-{{ $encomienda->estado }}">
                    {{ strtoupper(str_replace('_', ' ', $encomienda->estado)) }}
                </span>
            </p>
        </div>
    </div>

    @if($encomienda->descripcion)
        <p><strong>Descripción:</strong> {{ $encomienda->descripcion }}</p>
    @endif
</div>

{{-- Cambiar estado --}}
@if($encomienda->estado !== 'despachado' && $encomienda->estado !== 'tiempo_excedido' && auth()->user()->rol !== 'supervisor')
<div class="card">
    <h3 style="margin-bottom:15px">Cambiar Estado</h3>
    <form action="{{ route('encomiendas.estado', $encomienda->id_encomienda) }}" method="POST">
        @csrf
        <div class="form-group">
            <label>Nuevo Estado</label>
            <select name="estado" required>
                <option value="">Seleccionar...</option>
                <option value="recibido">Recibido</option>
                <option value="clasificado">Clasificado</option>
                <option value="en_espera">En Espera</option>
                <option value="despachado">Despachado</option>
                <option value="daniado">Dañado</option>
            </select>
        </div>
        <div class="form-group">
            <label>Observación</label>
            <textarea name="observacion" rows="2"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar Estado</button>
    </form>
</div>
@endif

{{-- Reubicar por tiempo excedido --}}
@if($encomienda->estado === 'tiempo_excedido' && auth()->user()->rol === 'operario')
<div class="card" style="border-left: 4px solid #6f42c1">
    <h3 style="margin-bottom:15px; color:#6f42c1">⚠️ Reubicar Encomienda</h3>
    <p style="margin-bottom:15px">Esta encomienda ha excedido el tiempo máximo de almacenamiento. Debe ser reubicada.</p>
    <form action="{{ route('encomiendas.estado', $encomienda->id_encomienda) }}" method="POST">
        @csrf
        <input type="hidden" name="estado" value="en_espera">
        <div class="form-group">
            <label>Observación de reubicación</label>
            <textarea name="observacion" rows="2" required>Reubicación por tiempo excedido</textarea>
        </div>
        <button type="submit" class="btn btn-warning">Reubicar Encomienda</button>
    </form>
</div>
@endif

{{-- Notificar daño --}}
@if($encomienda->estado !== 'despachado' && auth()->user()->rol === 'operario')
<div class="card">
    <h3 style="margin-bottom:15px">Notificar Daño</h3>
    <form action="{{ route('encomiendas.danio', $encomienda->id_encomienda) }}" method="POST">
        @csrf
        <div class="form-group">
            <label>Descripción del daño</label>
            <textarea name="observacion" rows="2" required></textarea>
        </div>
        <button type="submit" class="btn btn-danger">Registrar Daño</button>
    </form>
</div>
@endif

{{-- Historial de movimientos --}}
<div class="card">
    <h3 style="margin-bottom:15px">📋 Historial de Movimientos</h3>
    @if(count($historial) > 0)
    <table>
        <thead>
            <tr>
                <th>Estado Anterior</th>
                <th>Estado Nuevo</th>
                <th>Observación</th>
                <th>Usuario</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach($historial as $mov)
            <tr>
                <td>
                    <span class="badge badge-{{ $mov->estado_anterior }}">
                        {{ strtoupper(str_replace('_', ' ', $mov->estado_anterior)) }}
                    </span>
                </td>
                <td>
                    <span class="badge badge-{{ $mov->estado_nuevo }}">
                        {{ strtoupper(str_replace('_', ' ', $mov->estado_nuevo)) }}
                    </span>
                </td>
                <td>{{ $mov->observacion ?? '—' }}</td>
                <td>{{ $mov->usuario }}</td>
                <td>{{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <p>No hay movimientos registrados.</p>
    @endif
</div>
@endsection