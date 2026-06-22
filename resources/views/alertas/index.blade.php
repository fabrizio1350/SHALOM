@extends('layouts.app')

@section('titulo', 'Alertas')

@section('contenido')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px">
        <div>
            <h2 style="color:#2c3e50; margin-bottom:4px">🔔 Alertas Activas</h2>
            <p style="color:#888; font-size:13px">
                📋 Cola FIFO: <strong style="color:#e74c3c">{{ $total_en_cola }}</strong> alertas — se procesan en orden de llegada
            </p>
        </div>
        @if($total_en_cola > 0)
        <div style="background:#fff3cd; border:1px solid #ffc107; border-radius:8px; padding:8px 15px; font-size:13px">
            ⚠️ <strong>{{ $total_en_cola }}</strong> alerta(s) pendiente(s)
        </div>
        @else
        <div style="background:#d5f5e3; border:1px solid #27ae60; border-radius:8px; padding:8px 15px; font-size:13px">
            ✅ Sin alertas pendientes
        </div>
        @endif
    </div>

    <div style="overflow-x:auto">
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
                <td style="font-weight:bold; color:#888">{{ $alerta->id }}</td>
                <td style="font-weight:bold; color:#e74c3c; font-size:12px">{{ $alerta->id_encomienda }}</td>
                <td>
                    <span class="badge" style="background:{{ $alerta->tipo === 'tiempo_excedido' ? '#8e44ad' : '#e74c3c' }}; color:white">
                        {{ strtoupper(str_replace('_', ' ', $alerta->tipo)) }}
                    </span>
                </td>
                <td>
                    <span class="badge" style="background:
                        {{ $alerta->estado === 'generada' ? '#f39c12' : 
                        ($alerta->estado === 'notificada' ? '#17a2b8' : 
                        ($alerta->estado === 'atendida' ? '#27ae60' : '#7f8c8d')) }};
                        color:white">
                        {{ strtoupper($alerta->estado) }}
                    </span>
                </td>
                <td style="font-size:12px; color:#888">
                    {{ \Carbon\Carbon::parse($alerta->fecha_generada)->format('d/m/Y H:i') }}
                </td>
                <td>
                    <form action="{{ route('alertas.atender', $alerta->id) }}" method="POST"
                          style="display:flex; gap:5px; align-items:center; flex-wrap:wrap">
                        @csrf
                        <select name="accion" required
                                style="width:auto; margin-bottom:0; font-size:13px; padding:6px">
                            <option value="atendida">Atendida</option>
                            <option value="resuelta">Resuelta</option>
                        </select>
                        <input type="text" name="observacion" placeholder="Observación..."
                               style="width:150px; margin-bottom:0; font-size:13px; padding:6px">
                        <button type="submit" class="btn btn-success"
                                style="font-size:13px; padding:6px 12px">
                            Actualizar
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; padding:30px; color:#888">
                    ✅ No hay alertas activas en este momento.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection