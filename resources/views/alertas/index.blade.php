@extends('layouts.app')

@section('titulo', 'Alertas')

@section('contenido')
<div class="card">
    <h2 style="margin-bottom:20px">🔔 Alertas Activas</h2>

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
                <td>{{ $alerta->id }}</td>
                <td>{{ $alerta->id_encomienda }}</td>
                <td>
                    <span class="badge" style="background:{{ $alerta->tipo === 'tiempo_excedido' ? '#6f42c1' : '#dc3545' }}; color:white">
                        {{ strtoupper(str_replace('_', ' ', $alerta->tipo)) }}
                    </span>
                </td>
                <td>
                    <span class="badge" style="background:{{ $alerta->estado === 'generada' ? '#ffc107' : '#17a2b8' }}; color:{{ $alerta->estado === 'generada' ? 'black' : 'white' }}">
                        {{ strtoupper($alerta->estado) }}
                    </span>
                </td>
                <td>{{ \Carbon\Carbon::parse($alerta->fecha_generada)->format('d/m/Y H:i') }}</td>
                <td>
                    <form action="{{ route('alertas.atender', $alerta->id) }}" method="POST">
                        @csrf
                        <select name="accion" required style="width:auto; margin-bottom:0">
                            <option value="atendida">Atendida</option>
                            <option value="resuelta">Resuelta</option>
                        </select>
                        <input type="text" name="observacion" placeholder="Observación" style="width:auto; margin-bottom:0">
                        <button type="submit" class="btn btn-success">Actualizar</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; padding:20px">No hay alertas activas.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection