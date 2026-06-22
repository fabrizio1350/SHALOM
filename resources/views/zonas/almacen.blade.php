@extends('layouts.app')

@section('titulo', 'Almacén 2D')

@section('contenido')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px; flex-wrap:wrap; gap:10px">
        <div>
            <h2 style="color:#2c3e50; margin-bottom:4px">🏭 Vista 2D del Almacén</h2>
            <p style="color:#888; font-size:13px">Los paquetes se distribuyen automáticamente por peso en cada estante</p>
        </div>
        <div style="background:#fef9f9; border:1px solid #fad5d5; border-radius:8px; padding:8px 15px; font-size:13px">
            📊 Total nodos árbol: <strong style="color:#e74c3c">{{ $totalNodos }}</strong>
        </div>
    </div>

    <hr style="border:none; border-top:1px solid #ecf0f1; margin:15px 0 20px">

    <div style="display:flex; gap:15px; flex-wrap:wrap">
        @foreach($arbol['hijos'] as $zona)
        <div style="flex:1; min-width:260px; border:2px solid #e74c3c; border-radius:10px; overflow:hidden; box-shadow:0 2px 6px rgba(0,0,0,0.08)">

            {{-- Cabecera de zona --}}
            <div style="background:linear-gradient(135deg, #c0392b, #e74c3c); color:white; padding:10px 15px; display:flex; justify-content:space-between; align-items:center">
                <strong style="font-size:15px">{{ $zona['nombre'] }}</strong>
                <span style="padding:3px 10px; border-radius:12px; font-size:11px; font-weight:bold;
                    background:{{ $zona['estado'] === 'disponible' ? '#27ae60' : 
                      ($zona['estado'] === 'parcialmente_ocupada' ? '#f39c12' : 
                      ($zona['estado'] === 'llena' ? '#c0392b' : '#7f8c8d')) }};
                    color:white">
                    {{ strtoupper(str_replace('_', ' ', $zona['estado'])) }}
                </span>
            </div>

            {{-- 3 estantes --}}
            @foreach($zona['hijos'] as $estante)
            <div style="border-bottom:1px solid #fad5d5; padding:10px; min-height:75px;
                background:{{ $loop->last ? '#fff8f0' : ($loop->first ? '#f0f8ff' : '#fafafa') }}">

                {{-- Etiqueta del estante --}}
                <div style="font-size:11px; font-weight:bold; margin-bottom:8px;
                    color:{{ $loop->last ? '#e67e22' : ($loop->first ? '#2980b9' : '#666') }}">
                    {{ $estante['nombre'] }}
                </div>

                {{-- Paquetes --}}
                <div style="display:flex; flex-wrap:wrap; gap:5px">
                    @forelse($estante['paquetes'] as $paquete)
                    @php
                        $dims = explode('x', strtolower(str_replace(' ', '', $paquete['dimensiones'] ?? '10x10x10')));
                        $w = isset($dims[0]) ? max(35, min(80, (int)$dims[0] * 2)) : 40;
                        $h = isset($dims[1]) ? max(30, min(60, (int)$dims[1] * 2)) : 35;
                        $bgColor = $paquete['estado'] === 'tiempo_excedido' ? '#8e44ad' :
                                  ($paquete['estado'] === 'daniado' ? '#e74c3c' : '#2980b9');
                    @endphp
                    <div style="position:relative; display:inline-block">
                        <a href="{{ route('encomiendas.ver', $paquete['id_encomienda']) }}"
                           style="display:flex; align-items:center; justify-content:center;
                                  width:{{ $w }}px; height:{{ $h }}px;
                                  background:{{ $bgColor }};
                                  color:white; border-radius:6px; font-size:9px; font-weight:bold;
                                  text-decoration:none; text-align:center; padding:2px;
                                  border:2px solid rgba(255,255,255,0.3);
                                  overflow:hidden; position:relative;
                                  box-shadow:0 2px 4px rgba(0,0,0,0.2)"
                           title="{{ $paquete['id_encomienda'] }} | {{ $paquete['remitente'] }} | {{ $paquete['peso'] }}kg">
                            @if(!empty($paquete['imagen']))
                                <img src="{{ asset('storage/' . $paquete['imagen']) }}"
                                     style="position:absolute; top:0; left:0; width:100%; height:100%; object-fit:cover; opacity:0.5">
                            @endif
                            <span style="position:relative; z-index:1">{{ substr($paquete['id_encomienda'], -3) }}</span>
                        </a>
                        <div style="display:none; position:absolute; bottom:110%; left:50%; transform:translateX(-50%);
                                    background:white; border:1px solid #e74c3c; border-radius:8px; padding:10px;
                                    min-width:160px; box-shadow:0 4px 12px rgba(0,0,0,0.15); z-index:100"
                             class="tooltip-enc">
                            @if(!empty($paquete['imagen']))
                                <img src="{{ asset('storage/' . $paquete['imagen']) }}"
                                     style="width:100%; border-radius:4px; margin-bottom:8px">
                            @endif
                            <p style="font-size:11px; margin:0; color:#e74c3c; font-weight:bold">{{ $paquete['id_encomienda'] }}</p>
                            <p style="font-size:11px; margin:2px 0; color:#555">{{ $paquete['remitente'] }} → {{ $paquete['destinatario'] }}</p>
                            <p style="font-size:11px; margin:0; color:#888">⚖️ {{ $paquete['peso'] }}kg</p>
                        </div>
                    </div>
                    @empty
                    <span style="color:#bbb; font-size:11px; font-style:italic">vacío</span>
                    @endforelse
                </div>
            </div>
            @endforeach

        </div>
        @endforeach
    </div>

    {{-- Leyenda --}}
    <div style="margin-top:20px; padding:12px 15px; background:#f8f9fa; border-radius:8px; display:flex; gap:20px; flex-wrap:wrap; align-items:center">
        <span style="font-size:13px; font-weight:bold; color:#555">Leyenda:</span>
        <span style="font-size:13px"><span style="background:#2980b9; color:white; padding:3px 10px; border-radius:10px; font-size:11px">■</span> Normal</span>
        <span style="font-size:13px"><span style="background:#8e44ad; color:white; padding:3px 10px; border-radius:10px; font-size:11px">■</span> Tiempo Excedido</span>
        <span style="font-size:13px"><span style="background:#e74c3c; color:white; padding:3px 10px; border-radius:10px; font-size:11px">■</span> Dañado</span>
        <span style="color:#888; font-size:12px">💡 Pasa el mouse para ver detalles • Click para abrir</span>
    </div>
</div>

<style>
div:hover > .tooltip-enc { display:block !important; }
@media (max-width: 768px) {
    div[style*="min-width:260px"] { min-width: 100% !important; }
}
</style>
@endsection