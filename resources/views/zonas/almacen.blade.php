@extends('layouts.app')

@section('titulo', 'Almacén 2D')

@section('contenido')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px; flex-wrap:wrap; gap:10px">
        <div>
            <h2 style="color:var(--dark); margin-bottom:4px; font-size:22px; font-weight:800">
                <i class="fas fa-industry" style="color:var(--primary)"></i> Vista 2D del Almacén
            </h2>
            <p style="color:var(--muted); font-size:13px">
                Los paquetes se distribuyen automáticamente por peso en cada estante
            </p>
        </div>
        <div style="background:var(--primary-light); border:1px solid rgba(232,39,42,0.2);
                    border-radius:10px; padding:10px 18px; font-size:13px; display:flex;
                    align-items:center; gap:8px">
            <i class="fas fa-sitemap" style="color:var(--primary)"></i>
            <span style="color:var(--muted)">Total nodos árbol:</span>
            <strong style="color:var(--primary); font-size:16px">{{ $totalNodos }}</strong>
        </div>
    </div>

    <hr style="border:none; border-top:1px solid var(--border); margin:15px 0 20px">

    <div style="display:flex; gap:16px; flex-wrap:wrap">
        @foreach($arbol['hijos'] as $zona)
        <div style="flex:1; min-width:270px;
                    border:1.5px solid var(--border);
                    border-radius:14px;
                    overflow:hidden;
                    box-shadow:var(--shadow);
                    transition: transform 0.2s, box-shadow 0.2s"
             onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.12)'"
             onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='var(--shadow)'">

            {{-- Cabecera de zona --}}
            <div style="background:linear-gradient(135deg, var(--primary-dark), var(--primary));
                        color:white; padding:12px 16px;
                        display:flex; justify-content:space-between; align-items:center">
                <div style="display:flex; align-items:center; gap:8px">
                    <i class="fas fa-warehouse" style="font-size:14px; opacity:0.8"></i>
                    <strong style="font-size:15px; font-weight:700">{{ $zona['nombre'] }}</strong>
                </div>
                @php
                    $estadoColors = [
                        'disponible'          => '#10B981',
                        'parcialmente_ocupada'=> '#F59E0B',
                        'llena'               => '#EF4444',
                        'eliminada'           => '#6B7280',
                    ];
                    $estadoColor = $estadoColors[$zona['estado']] ?? '#6B7280';
                @endphp
                <span style="padding:4px 10px; border-radius:20px; font-size:10px; font-weight:700;
                             background:rgba(255,255,255,0.2); color:white;
                             border:1px solid rgba(255,255,255,0.3); white-space:nowrap">
                    {{ strtoupper(str_replace('_', ' ', $zona['estado'])) }}
                </span>
            </div>

            {{-- 3 estantes --}}
            @foreach($zona['hijos'] as $estante)
            @php
                $estanteBg = $loop->first ? '#F0F8FF' : ($loop->last ? '#FFF8F0' : '#FAFAFA');
                $estanteColor = $loop->first ? '#2980b9' : ($loop->last ? '#e67e22' : '#64748B');
                $estanteIcon = $loop->first ? 'fa-arrow-up' : ($loop->last ? 'fa-arrow-down' : 'fa-minus');
            @endphp
            <div style="border-bottom:1px solid var(--border); padding:12px;
                        min-height:80px; background:{{ $estanteBg }}">

                {{-- Etiqueta del estante --}}
                <div style="font-size:11px; font-weight:700; margin-bottom:10px;
                            color:{{ $estanteColor }}; display:flex; align-items:center; gap:5px">
                    <i class="fas {{ $estanteIcon }}" style="font-size:9px"></i>
                    {{ $estante['nombre'] }}
                </div>

                {{-- Paquetes --}}
                <div style="display:flex; flex-wrap:wrap; gap:6px">
                    @forelse($estante['paquetes'] as $paquete)
                    @php
                        $dims = explode('x', strtolower(str_replace(' ', '', $paquete['dimensiones'] ?? '10x10x10')));
                        $w = isset($dims[0]) ? max(38, min(80, (int)$dims[0] * 2)) : 42;
                        $h = isset($dims[1]) ? max(32, min(60, (int)$dims[1] * 2)) : 36;
                        $bgColor = $paquete['estado'] === 'tiempo_excedido' ? '#7C3AED' :
                                  ($paquete['estado'] === 'daniado' ? '#DC2626' : '#2563EB');
                        $borderColor = $paquete['estado'] === 'tiempo_excedido' ? '#6D28D9' :
                                      ($paquete['estado'] === 'daniado' ? '#B91C1C' : '#1D4ED8');
                    @endphp
                    <div style="position:relative; display:inline-block">
                        <a href="{{ route('encomiendas.ver', $paquete['id_encomienda']) }}"
                           style="display:flex; align-items:center; justify-content:center;
                                  width:{{ $w }}px; height:{{ $h }}px;
                                  background:{{ $bgColor }};
                                  color:white; border-radius:8px; font-size:9px; font-weight:800;
                                  text-decoration:none; text-align:center; padding:2px;
                                  border:2px solid {{ $borderColor }};
                                  overflow:hidden; position:relative;
                                  box-shadow:0 2px 6px rgba(0,0,0,0.25);
                                  transition: transform 0.15s, box-shadow 0.15s"
                           onmouseover="this.style.transform='scale(1.1)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.3)'"
                           onmouseout="this.style.transform='scale(1)';this.style.boxShadow='0 2px 6px rgba(0,0,0,0.25)'"
                           title="{{ $paquete['id_encomienda'] }}">
                            @if(!empty($paquete['imagen']))
                                <img src="{{ asset('storage/' . $paquete['imagen']) }}"
                                     style="position:absolute; top:0; left:0; width:100%; height:100%;
                                            object-fit:cover; opacity:0.4">
                            @endif
                            <span style="position:relative; z-index:1; letter-spacing:0.5px">
                                {{ substr($paquete['id_encomienda'], -3) }}
                            </span>
                        </a>

                        {{-- Tooltip --}}
                        <div style="display:none; position:absolute; bottom:110%; left:50%;
                                    transform:translateX(-50%);
                                    background:white; border:1px solid var(--border);
                                    border-radius:10px; padding:12px;
                                    min-width:170px; box-shadow:var(--shadow-lg); z-index:100"
                             class="tooltip-enc">
                            @if(!empty($paquete['imagen']))
                                <img src="{{ asset('storage/' . $paquete['imagen']) }}"
                                     style="width:100%; border-radius:6px; margin-bottom:8px;
                                            object-fit:cover; height:80px">
                            @endif
                            <p style="font-size:11px; margin:0 0 4px; color:var(--primary);
                                      font-weight:700">{{ $paquete['id_encomienda'] }}</p>
                            <p style="font-size:11px; margin:0 0 3px; color:var(--text)">
                                <i class="fas fa-user" style="color:var(--muted); width:12px"></i>
                                {{ $paquete['remitente'] }}
                            </p>
                            <p style="font-size:11px; margin:0 0 3px; color:var(--text)">
                                <i class="fas fa-arrow-right" style="color:var(--muted); width:12px"></i>
                                {{ $paquete['destinatario'] }}
                            </p>
                            <p style="font-size:11px; margin:0; color:var(--muted)">
                                <i class="fas fa-weight-hanging" style="width:12px"></i>
                                {{ $paquete['peso'] }} kg
                            </p>
                        </div>
                    </div>
                    @empty
                    <span style="color:#CBD5E1; font-size:11px; font-style:italic;
                                 display:flex; align-items:center; gap:4px">
                        <i class="fas fa-inbox" style="font-size:10px"></i> vacío
                    </span>
                    @endforelse
                </div>
            </div>
            @endforeach

        </div>
        @endforeach
    </div>

    {{-- Leyenda --}}
    <div style="margin-top:20px; padding:14px 18px; background:var(--surface);
                border-radius:10px; border:1px solid var(--border);
                display:flex; gap:20px; flex-wrap:wrap; align-items:center">
        <span style="font-size:12px; font-weight:700; color:var(--muted); text-transform:uppercase;
                     letter-spacing:0.5px">Leyenda</span>
        <span style="display:flex; align-items:center; gap:6px; font-size:13px">
            <span style="background:#2563EB; width:12px; height:12px; border-radius:3px; display:inline-block"></span>
            Normal
        </span>
        <span style="display:flex; align-items:center; gap:6px; font-size:13px">
            <span style="background:#7C3AED; width:12px; height:12px; border-radius:3px; display:inline-block"></span>
            Tiempo Excedido
        </span>
        <span style="display:flex; align-items:center; gap:6px; font-size:13px">
            <span style="background:#DC2626; width:12px; height:12px; border-radius:3px; display:inline-block"></span>
            Dañado
        </span>
        <span style="color:var(--muted); font-size:12px; margin-left:auto;
                     display:flex; align-items:center; gap:5px">
            <i class="fas fa-lightbulb" style="color:var(--warning)"></i>
            Pasa el mouse para ver detalles • Click para abrir
        </span>
    </div>
</div>

<style>
div:hover > .tooltip-enc { display:block !important; }
@media (max-width: 768px) {
    div[style*="min-width:270px"] { min-width: 100% !important; }
}
</style>
@endsection