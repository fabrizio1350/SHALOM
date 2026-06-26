@extends('layouts.app')

@section('titulo', 'Alertas')

@section('contenido')
<div class="card">

    {{-- Header --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px">
        <div>
            <h2 style="color:var(--dark); font-size:22px; font-weight:800; margin-bottom:3px">
                <i class="fas fa-bell" style="color:var(--primary)"></i> Alertas Activas
            </h2>
            <p style="color:var(--muted); font-size:13px; display:flex; align-items:center; gap:6px">
                <i class="fas fa-layer-group" style="font-size:11px"></i>
                Cola FIFO:
                <strong style="color:var(--primary)">{{ $total_en_cola }}</strong>
                alertas — se procesan en orden de llegada
            </p>
        </div>
        @if($total_en_cola > 0)
        <div style="background:#FEF3C7; border:1px solid #FCD34D; border-radius:10px;
                    padding:10px 16px; font-size:13px; display:flex; align-items:center; gap:8px">
            <i class="fas fa-exclamation-triangle" style="color:#D97706"></i>
            <strong style="color:#92400E">{{ $total_en_cola }}</strong>
            <span style="color:#92400E">alerta(s) pendiente(s)</span>
        </div>
        @else
        <div style="background:#D1FAE5; border:1px solid #6EE7B7; border-radius:10px;
                    padding:10px 16px; font-size:13px; display:flex; align-items:center; gap:8px">
            <i class="fas fa-check-circle" style="color:#059669"></i>
            <span style="color:#065F46; font-weight:600">Sin alertas pendientes</span>
        </div>
        @endif
    </div>

    {{-- Tabla --}}
    <div style="overflow-x:auto; border-radius:var(--radius); border:1px solid var(--border)">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Encomienda</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Fecha Generada</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($alertas as $alerta)
            <tr>
                <td>
                    <span style="font-size:12px; font-weight:700; color:var(--muted)">#{{ $alerta->id }}</span>
                </td>
                <td>
                    <a href="{{ route('encomiendas.ver', $alerta->id_encomienda) }}"
                       style="font-weight:700; color:var(--primary); font-size:12px;
                              font-family:monospace; text-decoration:none"
                       onmouseover="this.style.textDecoration='underline'"
                       onmouseout="this.style.textDecoration='none'">
                        {{ $alerta->id_encomienda }}
                    </a>
                </td>
                <td>
                    <span class="badge" style="background:{{ $alerta->tipo === 'tiempo_excedido' ? '#F3E8FF' : '#FEE2E2' }};
                                               color:{{ $alerta->tipo === 'tiempo_excedido' ? '#6B21A8' : '#991B1B' }}">
                        <i class="fas {{ $alerta->tipo === 'tiempo_excedido' ? 'fa-clock' : 'fa-exclamation-triangle' }}"
                           style="font-size:9px"></i>
                        {{ strtoupper(str_replace('_', ' ', $alerta->tipo)) }}
                    </span>
                </td>
                <td>
                    @php
                        $estadoStyles = [
                            'generada'   => ['bg'=>'#FEF3C7', 'color'=>'#92400E', 'icon'=>'fa-circle'],
                            'notificada' => ['bg'=>'#DBEAFE', 'color'=>'#1D4ED8', 'icon'=>'fa-paper-plane'],
                            'atendida'   => ['bg'=>'#D1FAE5', 'color'=>'#065F46', 'icon'=>'fa-check'],
                            'resuelta'   => ['bg'=>'#E0E7FF', 'color'=>'#3730A3', 'icon'=>'fa-check-double'],
                        ];
                        $style = $estadoStyles[$alerta->estado] ?? ['bg'=>'#F1F5F9', 'color'=>'#64748B', 'icon'=>'fa-circle'];
                    @endphp
                    <span class="badge" style="background:{{ $style['bg'] }}; color:{{ $style['color'] }}">
                        <i class="fas {{ $style['icon'] }}" style="font-size:9px"></i>
                        {{ strtoupper($alerta->estado) }}
                    </span>
                </td>
                <td style="font-size:12px; color:var(--muted); white-space:nowrap">
                    <i class="fas fa-calendar-alt" style="font-size:10px"></i>
                    {{ \Carbon\Carbon::parse($alerta->fecha_generada)->format('d/m/Y H:i') }}
                </td>
                <td>
                    <form action="{{ route('alertas.atender', $alerta->id) }}" method="POST"
                          style="display:flex; gap:6px; align-items:center; flex-wrap:wrap">
                        @csrf
                        <select name="accion" required
                                style="width:auto; margin-bottom:0; font-size:12px; padding:6px 10px">
                            <option value="atendida">Atendida</option>
                            <option value="resuelta">Resuelta</option>
                        </select>
                        <input type="text" name="observacion" placeholder="Observación..."
                               style="width:140px; margin-bottom:0; font-size:12px; padding:6px 10px">
                        <button type="submit" class="btn btn-success"
                                style="font-size:12px; padding:6px 14px">
                            <i class="fas fa-check"></i> Actualizar
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; padding:40px; color:var(--muted)">
                    <i class="fas fa-bell-slash" style="font-size:28px; opacity:0.3; display:block; margin-bottom:10px"></i>
                    No hay alertas activas en este momento.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection