@extends('layouts.app')

@section('titulo', 'Detalle Encomienda')

@php
function estadoLabel($estado) {
    $map = [
        'recibido'        => 'RECIBIDO',
        'clasificado'     => 'CLASIFICADO',
        'en_espera'       => 'EN ESPERA',
        'despachado'      => 'DESPACHADO',
        'daniado'         => 'DAÑADO',
        'tiempo_excedido' => 'TIEMPO EXCEDIDO',
    ];
    return $map[$estado] ?? strtoupper(str_replace('_', ' ', $estado));
}
@endphp

@section('contenido')

{{-- Header --}}
<div class="card" style="border-top:3px solid #e74c3c">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px">
        <div>
            <h2 style="color:#2c3e50; margin-bottom:4px">📦 {{ $encomienda->id_encomienda }}</h2>
            <span class="badge badge-{{ $encomienda->estado }}" style="font-size:13px">
                {{ estadoLabel($encomienda->estado) }}
            </span>
        </div>
        <a href="{{ route('encomiendas.index') }}" class="btn btn-secondary">← Volver</a>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px">
        {{-- Columna izquierda --}}
        <div style="background:#fef9f9; border-radius:8px; padding:15px">
            <h4 style="color:#e74c3c; margin-bottom:12px">👤 Personas</h4>
            <p style="margin-bottom:8px"><span style="color:#888; font-size:12px">Remitente</span><br>
                <strong>{{ $encomienda->remitente }}</strong></p>
            <p style="margin-bottom:8px"><span style="color:#888; font-size:12px">Destinatario</span><br>
                <strong>{{ $encomienda->destinatario }}</strong></p>
            <p><span style="color:#888; font-size:12px">Ciudad Destino</span><br>
                <strong>{{ $encomienda->ciudad_destino }}</strong></p>
        </div>

        {{-- Columna derecha --}}
        <div style="background:#f0f9ff; border-radius:8px; padding:15px">
            <h4 style="color:#2980b9; margin-bottom:12px">📦 Paquete</h4>
            <p style="margin-bottom:8px"><span style="color:#888; font-size:12px">Peso</span><br>
                <strong>{{ $encomienda->peso }} kg</strong></p>
            <p style="margin-bottom:8px"><span style="color:#888; font-size:12px">Dimensiones</span><br>
                <strong>{{ $encomienda->dimensiones ?? 'No especificado' }}</strong></p>
            <p style="margin-bottom:8px"><span style="color:#888; font-size:12px">Categoría / Estante</span><br>
                <strong>{{ ucfirst($categoria) }} — Estante {{ $estante }}
                ({{ $estante == 1 ? 'Arriba' : ($estante == 2 ? 'Medio' : 'Abajo') }})</strong></p>
            <p style="margin-bottom:8px"><span style="color:#888; font-size:12px">Zona</span><br>
                <strong>{{ $encomienda->zona ? $encomienda->zona->nombre : 'Sin zona' }}</strong></p>
            <p><span style="color:#888; font-size:12px">Fecha Ingreso</span><br>
                <strong>{{ \Carbon\Carbon::parse($encomienda->fecha_ingreso)->format('d/m/Y H:i') }}</strong></p>
        </div>
    </div>

    @if($encomienda->descripcion)
        <p style="color:#555"><strong>📝 Descripción:</strong> {{ $encomienda->descripcion }}</p>
    @endif

    {{-- Imagen --}}
    @if($encomienda->imagen)
    <div style="margin-top:15px">
        <p style="color:#888; font-size:12px; margin-bottom:8px">🖼️ Imagen del paquete</p>
        <img src="{{ asset('storage/' . $encomienda->imagen) }}"
             style="max-width:280px; border-radius:8px; border:2px solid #e74c3c; box-shadow:0 2px 8px rgba(0,0,0,0.1)">
    </div>
    @endif
</div>

{{-- Aviso tiempo excedido para operario — debe esperar al supervisor --}}
@if($encomienda->estado === 'tiempo_excedido' && auth()->user()->rol === 'operario')
<div class="card" style="border-left:4px solid #8e44ad; background:#f9f0ff">
    <h3 style="margin-bottom:10px; color:#8e44ad">⏰ Encomienda con Tiempo Excedido</h3>
    <p style="color:#666; font-size:14px">
        Esta encomienda ha superado el tiempo máximo de almacenamiento.<br>
        <strong>El supervisor debe resolver la alerta primero</strong> para que puedas proceder con la reubicación.
    </p>
</div>
@endif

{{-- Cambiar estado — solo para recibido, clasificado y daniado; no supervisor --}}
@if(
    !in_array($encomienda->estado, ['despachado', 'tiempo_excedido', 'en_espera', 'daniado'])
    && auth()->user()->rol !== 'supervisor'
)
<div class="card">
    <h3 style="margin-bottom:15px; color:#2c3e50">🔄 Cambiar Estado</h3>
    <form action="{{ route('encomiendas.estado', $encomienda->id_encomienda) }}" method="POST">
        @csrf
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px">
            <div class="form-group" style="margin-bottom:0">
                <label>Nuevo Estado</label>
                <select name="estado" required>
                    <option value="">Seleccionar...</option>
                    <option value="recibido">Recibido</option>
                    <option value="clasificado">Clasificado</option>
                    <option value="despachado">Despachado</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0">
                <label>Observación</label>
                <textarea name="observacion" rows="2" placeholder="Opcional..."></textarea>
            </div>
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:10px">Actualizar Estado</button>
    </form>
</div>
@endif

{{-- Reubicar — solo cuando supervisor ya resolvió la alerta (estado en_espera) --}}
@if($encomienda->estado === 'en_espera' && auth()->user()->rol === 'operario')
<div class="card" style="border-left:4px solid #8e44ad">
    <h3 style="margin-bottom:10px; color:#8e44ad">📦 Reubicar Encomienda</h3>
    <p style="color:#666; margin-bottom:15px; font-size:14px">
        ✅ El supervisor ha autorizado la reubicación. Por favor confirma que el paquete fue reubicado físicamente.
    </p>
    <form action="{{ route('encomiendas.reubicar', $encomienda->id_encomienda) }}" method="POST">
        @csrf
        <div class="form-group">
            <label>Observación</label>
            <textarea name="observacion" rows="2" required>Reubicación física completada</textarea>
        </div>
        <button type="submit" class="btn btn-warning">📦 Confirmar Reubicación</button>
    </form>
</div>
@endif

{{-- Notificar daño --}}
@if($encomienda->estado !== 'despachado' && $encomienda->estado !== 'tiempo_excedido' && auth()->user()->rol === 'operario')
<div class="card" style="border-left:4px solid #e74c3c">
    <h3 style="margin-bottom:10px; color:#e74c3c">🚨 Notificar Daño</h3>
    <form action="{{ route('encomiendas.danio', $encomienda->id_encomienda) }}" method="POST">
        @csrf
        <div class="form-group">
            <label>Descripción del daño</label>
            <textarea name="observacion" rows="2" required placeholder="Describa el daño encontrado..."></textarea>
        </div>
        <button type="submit" class="btn btn-danger">Registrar Daño</button>
    </form>
</div>
@endif

{{-- Historial --}}
<div class="card">
    <h3 style="margin-bottom:15px; color:#2c3e50">
        📋 Historial de Movimientos
        <span style="font-size:13px; color:#888; font-weight:normal">
            ({{ $totalMovimientos }} movimientos)
        </span>
    </h3>
    @if(count($historial) > 0)
    <div style="overflow-x:auto">
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
                <td><span class="badge badge-{{ $mov['estado_anterior'] }}">
                    {{ estadoLabel($mov['estado_anterior']) }}
                </span></td>
                <td><span class="badge badge-{{ $mov['estado_nuevo'] }}">
                    {{ estadoLabel($mov['estado_nuevo']) }}
                </span></td>
                <td style="font-size:13px">{{ $mov['observacion'] ?? '—' }}</td>
                <td style="font-size:13px">{{ $mov['usuario'] }}</td>
                <td style="font-size:12px; color:#888">
                    {{ \Carbon\Carbon::parse($mov['fecha'])->format('d/m/Y H:i') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @else
        <p style="color:#888; text-align:center; padding:20px">No hay movimientos registrados.</p>
    @endif
</div>

<style>
@media (max-width: 600px) {
    div[style*="grid-template-columns:1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>
@endsection