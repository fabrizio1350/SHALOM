@extends('layouts.app')

@section('titulo', 'Almacén 2D')

@section('contenido')
<div class="card">
    <h2 style="margin-bottom:5px">🏭 Vista 2D del Almacén</h2>
    <p style="color:#666; margin-bottom:25px">Los paquetes se distribuyen automáticamente por peso en cada estante</p>

    <div style="display:flex; gap:20px; flex-wrap:wrap">
        @foreach($arbol['hijos'] as $zona)
        <div style="flex:1; min-width:280px; border:2px solid #1a1a2e; border-radius:8px; overflow:hidden">
            
            {{-- Cabecera de zona --}}
            <div style="background:#1a1a2e; color:white; padding:10px 15px; display:flex; justify-content:space-between">
                <strong>{{ $zona['nombre'] }}</strong>
                <span class="badge" style="background:
                    {{ $zona['estado'] === 'disponible' ? '#28a745' : 
                      ($zona['estado'] === 'parcialmente_ocupada' ? '#ffc107' : 
                      ($zona['estado'] === 'llena' ? '#dc3545' : '#6c757d')) }};
                    color:{{ $zona['estado'] === 'parcialmente_ocupada' ? 'black' : 'white' }}">
                    {{ strtoupper(str_replace('_', ' ', $zona['estado'])) }}
                </span>
            </div>

            {{-- 3 estantes --}}
            @foreach($zona['hijos'] as $estante)
            <div style="border-bottom:2px solid #1a1a2e; padding:10px; min-height:80px;
                background:{{ $loop->last ? '#f8f0e3' : ($loop->first ? '#f0f8ff' : '#f5f5f5') }}">
                
                {{-- Etiqueta del estante --}}
                <div style="font-size:11px; color:#666; margin-bottom:8px; font-weight:bold">
                    {{ $estante['nombre'] }}
                </div>

                {{-- Paquetes como cuadritos --}}
                <div style="display:flex; flex-wrap:wrap; gap:5px">
                    @forelse($estante['paquetes'] as $paquete)
                    @php
                        $dims = explode('x', strtolower(str_replace(' ', '', $paquete['dimensiones'] ?? '10x10x10')));
                        $w = isset($dims[0]) ? max(30, min(80, (int)$dims[0] * 2)) : 40;
                        $h = isset($dims[1]) ? max(25, min(60, (int)$dims[1] * 2)) : 35;
                    @endphp
                    <a href="{{ route('encomiendas.ver', $paquete['id_encomienda']) }}"
                       title="{{ $paquete['id_encomienda'] }} | {{ $paquete['remitente'] }} → {{ $paquete['destinatario'] }} | {{ $paquete['peso'] }}kg"
                       style="display:flex; align-items:center; justify-content:center;
                              width:{{ $w }}px; height:{{ $h }}px;
                              background:{{ $paquete['estado'] === 'tiempo_excedido' ? '#6f42c1' : 
                                          ($paquete['estado'] === 'daniado' ? '#dc3545' : '#007bff') }};
                              color:white; border-radius:4px; font-size:9px;
                              text-decoration:none; text-align:center; padding:2px;
                              border:1px solid rgba(0,0,0,0.2)">
                        {{ substr($paquete['id_encomienda'], -3) }}
                    </a>
                    @empty
                    <span style="color:#aaa; font-size:12px; font-style:italic">vacío</span>
                    @endforelse
                </div>
            </div>
            @endforeach

        </div>
        @endforeach
    </div>

    {{-- Leyenda --}}
    <div style="margin-top:20px; display:flex; gap:15px; flex-wrap:wrap">
        <span><span style="background:#007bff; color:white; padding:3px 8px; border-radius:4px">■</span> Normal</span>
        <span><span style="background:#6f42c1; color:white; padding:3px 8px; border-radius:4px">■</span> Tiempo Excedido</span>
        <span><span style="background:#dc3545; color:white; padding:3px 8px; border-radius:4px">■</span> Dañado</span>
        <p style="color:#666; margin-top:5px; font-size:12px">💡 Pasa el mouse sobre un paquete para ver detalles. Click para abrir.</p>
    </div>
</div>
@endsection