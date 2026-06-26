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
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px">
        <div>
            <p style="color:var(--muted); font-size:12px; margin-bottom:4px; text-transform:uppercase;
                      letter-spacing:0.5px; font-weight:600">
                <i class="fas fa-box" style="color:var(--primary)"></i> Detalle de Encomienda
            </p>
            <h2 style="color:var(--dark); font-size:20px; font-weight:800; margin-bottom:6px;
                       font-family:monospace; letter-spacing:0.5px">
                {{ $encomienda->id_encomienda }}
            </h2>
            <span class="badge badge-{{ $encomienda->estado }}" style="font-size:12px">
                {{ estadoLabel($encomienda->estado) }}
            </span>
        </div>
        <a href="{{ route('encomiendas.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px">

        {{-- Columna izquierda — Personas --}}
        <div style="background:var(--primary-light); border:1px solid rgba(232,39,42,0.15);
                    border-radius:12px; padding:16px">
            <h4 style="color:var(--primary); margin-bottom:14px; font-size:13px; font-weight:700;
                       display:flex; align-items:center; gap:7px; text-transform:uppercase; letter-spacing:0.5px">
                <i class="fas fa-users"></i> Personas
            </h4>
            <div style="margin-bottom:10px">
                <span style="color:var(--muted); font-size:11px; font-weight:600; text-transform:uppercase;
                             letter-spacing:0.5px">Remitente</span>
                <p style="font-weight:700; color:var(--dark); margin-top:2px">{{ $encomienda->remitente }}</p>
            </div>
            <div style="margin-bottom:10px">
                <span style="color:var(--muted); font-size:11px; font-weight:600; text-transform:uppercase;
                             letter-spacing:0.5px">Destinatario</span>
                <p style="font-weight:700; color:var(--dark); margin-top:2px">{{ $encomienda->destinatario }}</p>
            </div>
            <div>
                <span style="color:var(--muted); font-size:11px; font-weight:600; text-transform:uppercase;
                             letter-spacing:0.5px">Ciudad Destino</span>
                <p style="font-weight:700; color:var(--dark); margin-top:2px; display:flex; align-items:center; gap:5px">
                    <i class="fas fa-map-marker-alt" style="color:var(--primary); font-size:11px"></i>
                    {{ $encomienda->ciudad_destino }}
                </p>
            </div>
        </div>

        {{-- Columna derecha — Paquete --}}
        <div style="background:#F0F9FF; border:1px solid rgba(59,130,246,0.2);
                    border-radius:12px; padding:16px">
            <h4 style="color:#2563EB; margin-bottom:14px; font-size:13px; font-weight:700;
                       display:flex; align-items:center; gap:7px; text-transform:uppercase; letter-spacing:0.5px">
                <i class="fas fa-box"></i> Paquete
            </h4>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px">
                <div>
                    <span style="color:var(--muted); font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px">Peso</span>
                    <p style="font-weight:700; color:var(--dark); margin-top:2px">{{ $encomienda->peso }} kg</p>
                </div>
                <div>
                    <span style="color:var(--muted); font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px">Dimensiones</span>
                    <p style="font-weight:700; color:var(--dark); margin-top:2px">{{ $encomienda->dimensiones ?? 'No especificado' }}</p>
                </div>
                <div>
                    <span style="color:var(--muted); font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px">Categoría</span>
                    <p style="font-weight:700; color:var(--dark); margin-top:2px">{{ ucfirst($categoria) }}</p>
                </div>
                <div>
                    <span style="color:var(--muted); font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px">Estante</span>
                    <p style="font-weight:700; color:var(--dark); margin-top:2px">
                        {{ $estante }} — {{ $estante == 1 ? 'Arriba' : ($estante == 2 ? 'Medio' : 'Abajo') }}
                    </p>
                </div>
                <div>
                    <span style="color:var(--muted); font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px">Zona</span>
                    <p style="font-weight:700; color:var(--dark); margin-top:2px">
                        {{ $encomienda->zona ? $encomienda->zona->nombre : 'Sin zona' }}
                    </p>
                </div>
                <div>
                    <span style="color:var(--muted); font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px">Fecha Ingreso</span>
                    <p style="font-weight:700; color:var(--dark); margin-top:2px">
                        {{ \Carbon\Carbon::parse($encomienda->fecha_ingreso)->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    @if($encomienda->descripcion)
        <div style="background:var(--surface); border-radius:8px; padding:12px 16px;
                    border:1px solid var(--border); margin-bottom:10px">
            <span style="color:var(--muted); font-size:11px; font-weight:600; text-transform:uppercase;
                         letter-spacing:0.5px"><i class="fas fa-file-alt"></i> Descripción</span>
            <p style="color:var(--text); margin-top:4px">{{ $encomienda->descripcion }}</p>
        </div>
    @endif

    {{-- Imagen --}}
    @if($encomienda->imagen)
    <div style="margin-top:15px">
        <span style="color:var(--muted); font-size:11px; font-weight:600; text-transform:uppercase;
                     letter-spacing:0.5px; display:block; margin-bottom:8px">
            <i class="fas fa-image"></i> Imagen del paquete
        </span>
        <img src="{{ asset('storage/' . $encomienda->imagen) }}"
             style="max-width:280px; border-radius:10px; border:2px solid var(--border);
                    box-shadow:var(--shadow)">
    </div>
    @endif
</div>

{{-- Aviso tiempo excedido --}}
@if($encomienda->estado === 'tiempo_excedido' && auth()->user()->rol === 'operario')
<div class="card" style="border-left:4px solid #7C3AED; background:#F5F3FF">
    <h3 style="margin-bottom:8px; color:#7C3AED; display:flex; align-items:center; gap:8px">
        <i class="fas fa-clock"></i> Encomienda con Tiempo Excedido
    </h3>
    <p style="color:#6D28D9; font-size:14px">
        Esta encomienda ha superado el tiempo máximo de almacenamiento.
        <strong>El supervisor debe resolver la alerta primero</strong> para que puedas proceder con la reubicación.
    </p>
</div>
@endif

{{-- Cambiar estado --}}
@if(!in_array($encomienda->estado, ['despachado', 'tiempo_excedido', 'en_espera', 'daniado']) && auth()->user()->rol !== 'supervisor')
<div class="card">
    <h3 style="margin-bottom:15px; color:var(--dark); display:flex; align-items:center; gap:8px">
        <i class="fas fa-exchange-alt" style="color:var(--primary)"></i> Cambiar Estado
    </h3>
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
        <button type="submit" class="btn btn-primary" style="margin-top:12px">
            <i class="fas fa-save"></i> Actualizar Estado
        </button>
    </form>
</div>
@endif

{{-- Reubicar --}}
@if($encomienda->estado === 'en_espera' && auth()->user()->rol === 'operario')
<div class="card" style="border-left:4px solid #7C3AED">
    <h3 style="margin-bottom:8px; color:#7C3AED; display:flex; align-items:center; gap:8px">
        <i class="fas fa-arrows-alt"></i> Reubicar Encomienda
    </h3>
    <p style="color:var(--muted); margin-bottom:15px; font-size:14px">
        <i class="fas fa-check-circle" style="color:var(--success)"></i>
        El supervisor ha autorizado la reubicación. Confirma que el paquete fue reubicado físicamente.
    </p>
    <form action="{{ route('encomiendas.reubicar', $encomienda->id_encomienda) }}" method="POST">
        @csrf
        <div class="form-group">
            <label>Observación</label>
            <textarea name="observacion" rows="2" required>Reubicación física completada</textarea>
        </div>
        <button type="submit" class="btn btn-warning">
            <i class="fas fa-arrows-alt"></i> Confirmar Reubicación
        </button>
    </form>
</div>
@endif

{{-- Notificar daño --}}
@if($encomienda->estado !== 'despachado' && $encomienda->estado !== 'tiempo_excedido' && auth()->user()->rol === 'operario')
<div class="card" style="border-left:4px solid var(--primary)">
    <h3 style="margin-bottom:10px; color:var(--primary); display:flex; align-items:center; gap:8px">
        <i class="fas fa-exclamation-triangle"></i> Notificar Daño
    </h3>
    <form action="{{ route('encomiendas.danio', $encomienda->id_encomienda) }}" method="POST">
        @csrf
        <div class="form-group">
            <label>Descripción del daño</label>
            <textarea name="observacion" rows="2" required placeholder="Describa el daño encontrado..."></textarea>
        </div>
        <button type="submit" class="btn btn-danger">
            <i class="fas fa-exclamation-circle"></i> Registrar Daño
        </button>
    </form>
</div>
@endif

{{-- Historial --}}
<div class="card">
    <h3 style="margin-bottom:15px; color:var(--dark); display:flex; align-items:center; gap:8px">
        <i class="fas fa-history" style="color:var(--primary)"></i> Historial de Movimientos
        <span style="font-size:12px; color:var(--muted); font-weight:400; background:var(--surface);
                     border:1px solid var(--border); padding:2px 10px; border-radius:20px">
            {{ $totalMovimientos }} movimientos
        </span>
    </h3>
    @if(count($historial) > 0)
    <div style="overflow-x:auto; border-radius:var(--radius); border:1px solid var(--border)">
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
                <td style="font-size:13px; color:var(--muted)">{{ $mov['observacion'] ?? '—' }}</td>
                <td style="font-size:13px; font-weight:500">
                    <i class="fas fa-user" style="color:var(--muted); font-size:10px"></i>
                    {{ $mov['usuario'] }}
                </td>
                <td style="font-size:12px; color:var(--muted); white-space:nowrap">
                    <i class="fas fa-calendar-alt" style="font-size:10px"></i>
                    {{ \Carbon\Carbon::parse($mov['fecha'])->format('d/m/Y H:i') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @else
        <div style="text-align:center; padding:30px; color:var(--muted)">
            <i class="fas fa-history" style="font-size:24px; opacity:0.3; display:block; margin-bottom:8px"></i>
            No hay movimientos registrados.
        </div>
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