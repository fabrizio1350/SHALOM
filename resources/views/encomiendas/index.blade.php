@extends('layouts.app')

@section('titulo', 'Encomiendas')

@section('contenido')
<div class="card">

    {{-- Header --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px">
        <div>
            <h2 style="color:var(--dark); font-size:22px; font-weight:800; margin-bottom:3px">
                <i class="fas fa-box" style="color:var(--primary)"></i> Encomiendas
            </h2>
            <p style="color:var(--muted); font-size:13px">Gestión y seguimiento de paquetes en almacén</p>
        </div>
        @if(auth()->user()->rol === 'operario' || auth()->user()->rol === 'administrador')
            <a href="{{ route('encomiendas.crear') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Encomienda
            </a>
        @endif
    </div>

    {{-- Buscador BST --}}
    <form method="GET" action="{{ route('encomiendas.index') }}"
          style="display:flex; gap:10px; margin-bottom:10px; flex-wrap:wrap">
        <div style="position:relative; flex:1; max-width:380px">
            <i class="fas fa-search" style="position:absolute; left:12px; top:50%;
               transform:translateY(-50%); color:var(--muted); font-size:13px"></i>
            <input type="text" name="busqueda"
                   placeholder="Buscar por nombre del remitente..."
                   value="{{ $busqueda ?? '' }}"
                   style="padding-left:36px; margin-bottom:0">
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> Buscar
        </button>
        @if($busqueda)
            <a href="{{ route('encomiendas.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Limpiar
            </a>
        @endif
    </form>

    {{-- Info BST --}}
    <div style="display:flex; align-items:center; gap:8px; margin-bottom:16px;
                background:var(--surface); border:1px solid var(--border);
                border-radius:8px; padding:8px 14px; width:fit-content">
        <i class="fas fa-tree" style="color:var(--success); font-size:13px"></i>
        <span style="color:var(--muted); font-size:12px">
            <strong style="color:var(--text)">Árbol Binario de Búsqueda (BST)</strong>
            — {{ $totalBST }} nodos en el árbol
            @if($busqueda)
                — Resultados para: <strong style="color:var(--primary)">"{{ $busqueda }}"</strong>
            @endif
        </span>
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
                $estLabel = [
                    'recibido'        => 'RECIBIDO',
                    'clasificado'     => 'CLASIFICADO',
                    'en_espera'       => 'EN ESPERA',
                    'despachado'      => 'DESPACHADO',
                    'daniado'         => 'DAÑADO',
                    'tiempo_excedido' => 'TIEMPO EXCEDIDO',
                ][$est] ?? strtoupper(str_replace('_', ' ', $est));
            @endphp
            <tr>
                <td>
                    <span style="font-size:11px; font-weight:700; color:var(--primary);
                                 font-family:monospace; letter-spacing:0.3px">{{ $id }}</span>
                </td>
                <td style="font-weight:500">{{ $rem }}</td>
                <td style="color:var(--muted)">{{ $des }}</td>
                <td>
                    <span style="display:flex; align-items:center; gap:4px; font-size:13px">
                        <i class="fas fa-map-marker-alt" style="color:var(--primary); font-size:10px"></i>
                        {{ $ciu }}
                    </span>
                </td>
                <td>
                    <strong style="color:var(--dark)">{{ $pes }}</strong>
                    <span style="color:var(--muted); font-size:11px"> kg</span>
                </td>
                <td>
                    <span style="background:var(--primary-light); border:1px solid rgba(232,39,42,0.2);
                                 color:var(--primary); padding:3px 10px; border-radius:20px;
                                 font-size:11px; font-weight:600; white-space:nowrap">
                        <i class="fas fa-warehouse" style="font-size:9px"></i>
                        {{ $zon }}
                    </span>
                </td>
                <td>
                    <span class="badge badge-{{ $est }}">{{ $estLabel }}</span>
                </td>
                <td style="font-size:12px; color:var(--muted); white-space:nowrap">
                    <i class="fas fa-calendar-alt" style="font-size:10px"></i>
                    {{ \Carbon\Carbon::parse($fec)->format('d/m/Y H:i') }}
                </td>
                <td style="white-space:nowrap">
                    <a href="{{ route('encomiendas.ver', $id) }}" class="btn btn-primary"
                       style="font-size:12px; padding:5px 12px">
                        <i class="fas fa-eye"></i> Ver
                    </a>
                    @if($est !== 'despachado' && auth()->user()->rol === 'operario')
                        <form action="{{ route('encomiendas.despachar', $id) }}" method="POST"
                              style="display:inline">
                            @csrf
                            <button type="submit" class="btn btn-success"
                                    style="font-size:12px; padding:5px 12px"
                                    onclick="return confirm('¿Despachar esta encomienda?')">
                                <i class="fas fa-check"></i> Despachar
                            </button>
                        </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align:center; padding:40px; color:var(--muted)">
                    @if($busqueda)
                        <i class="fas fa-search" style="font-size:24px; margin-bottom:10px; display:block; opacity:0.3"></i>
                        No se encontraron encomiendas para "<strong>{{ $busqueda }}</strong>"
                    @else
                        <i class="fas fa-inbox" style="font-size:24px; margin-bottom:10px; display:block; opacity:0.3"></i>
                        No hay encomiendas registradas
                    @endif
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection