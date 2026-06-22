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

    {{-- Buscador BST --}}
    <form method="GET" action="{{ route('encomiendas.index') }}" style="display:flex; gap:10px; margin-bottom:15px">
        <input type="text" name="busqueda" placeholder="🔍 Buscar por nombre del remitente..."
            value="{{ $busqueda ?? '' }}" style="max-width:350px; margin-bottom:0">
        <button type="submit" class="btn btn-primary">Buscar</button>
        @if($busqueda)
            <a href="{{ route('encomiendas.index') }}" class="btn btn-warning">Limpiar</a>
        @endif
    </form>

    <p style="color:#666; font-size:13px; margin-bottom:15px">
        🌳 Búsqueda usando <strong>Árbol Binario de Búsqueda (BST)</strong> — 
        {{ $totalBST }} nodos en el árbol
        @if($busqueda)
            — Resultados para: <strong>"{{ $busqueda }}"</strong>
        @endif
    </p>

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
            @php
                $id  = is_array($enc) ? $enc['id_encomienda'] : $enc->id_encomienda;
                $rem = is_array($enc) ? $enc['remitente']      : $enc->remitente;
                $des = is_array($enc) ? $enc['destinatario']   : $enc->destinatario;
                $ciu = is_array($enc) ? $enc['ciudad_destino'] : $enc->ciudad_destino;
                $pes = is_array($enc) ? $enc['peso']           : $enc->peso;
                $est = is_array($enc) ? $enc['estado']         : $enc->estado;
                $fec = is_array($enc) ? $enc['fecha_ingreso']  : $enc->fecha_ingreso;
                $zon = is_array($enc) ? ($enc['zona']['nombre'] ?? 'Sin zona') : ($enc->zona ? $enc->zona->nombre : 'Sin zona');
            @endphp
            <tr>
                <td>{{ $id }}</td>
                <td>{{ $rem }}</td>
                <td>{{ $des }}</td>
                <td>{{ $ciu }}</td>
                <td>{{ $pes }} kg</td>
                <td>{{ $zon }}</td>
                <td>
                    <span class="badge badge-{{ $est }}">
                        {{ strtoupper(str_replace('_', ' ', $est)) }}
                    </span>
                </td>
                <td>{{ \Carbon\Carbon::parse($fec)->format('d/m/Y H:i') }}</td>
                <td>
                    <a href="{{ route('encomiendas.ver', $id) }}" class="btn btn-primary">Ver</a>
                    @if($est !== 'despachado' && auth()->user()->rol === 'operario')
                        <form action="{{ route('encomiendas.despachar', $id) }}" method="POST" style="display:inline">
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
                <td colspan="9" style="text-align:center; padding:20px">
                    @if($busqueda)
                        No se encontraron encomiendas para "{{ $busqueda }}".
                    @else
                        No hay encomiendas registradas.
                    @endif
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection