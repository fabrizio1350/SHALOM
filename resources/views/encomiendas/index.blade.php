@extends('layouts.app')

@section('titulo', 'Encomiendas')

@section('contenido')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px">
        <h2>📦 Encomiendas</h2>
        @if(auth()->user()->rol === 'operario' || auth()->user()->rol === 'administrador')
            <a href="{{ route('encomiendas.crear') }}" class="btn btn-primary">+ Nueva Encomienda</a>
        @endif
    </div>

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
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($encomiendas as $enc)
            <tr>
                <td>{{ $enc->id_encomienda }}</td>
                <td>{{ $enc->remitente }}</td>
                <td>{{ $enc->destinatario }}</td>
                <td>{{ $enc->ciudad_destino }}</td>
                <td>{{ $enc->peso }} kg</td>
                <td>{{ $enc->zona ? $enc->zona->nombre : 'Sin zona' }}</td>
                <td>
                    <span class="badge badge-{{ $enc->estado }}">
                        {{ strtoupper(str_replace('_', ' ', $enc->estado)) }}
                    </span>
                </td>
                <td>{{ \Carbon\Carbon::parse($enc->fecha_ingreso)->format('d/m/Y H:i') }}</td>
                <td>
                    <a href="{{ route('encomiendas.ver', $enc->id_encomienda) }}" class="btn btn-primary">Ver</a>
                    @if($enc->estado !== 'despachado' && auth()->user()->rol === 'operario')
                        <form action="{{ route('encomiendas.despachar', $enc->id_encomienda) }}" method="POST" style="display:inline">
                            @csrf
                            <button type="submit" class="btn btn-success"
                                onclick="return confirm('¿Despachar esta encomienda?')">
                                Despachar
                            </button>
                        </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align:center; padding:20px">No hay encomiendas registradas.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection