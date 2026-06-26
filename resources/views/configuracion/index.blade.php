@extends('layouts.app')

@section('titulo', 'Configuración')

@section('contenido')
<div class="card" style="max-width:640px; margin:0 auto">

    {{-- Header --}}
    <div style="margin-bottom:24px">
        <h2 style="color:var(--dark); font-size:22px; font-weight:800; margin-bottom:3px">
            <i class="fas fa-cog" style="color:var(--primary)"></i> Configuración del Sistema
        </h2>
        <p style="color:var(--muted); font-size:13px">Parámetros globales que controlan el comportamiento del sistema</p>
    </div>

    @if($errors->any())
        <div class="alert-error">
            <i class="fas fa-exclamation-circle" style="font-size:16px"></i>
            <div>
                @foreach($errors->all() as $error)
                    <p style="margin:2px 0">{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    <form action="{{ route('configuracion.guardar') }}" method="POST">
        @csrf

        {{-- Tiempo de almacenamiento --}}
        <div style="background:var(--primary-light); border:1px solid rgba(232,39,42,0.15);
                    border-radius:12px; padding:20px; margin-bottom:16px">
            <h3 style="color:var(--primary); font-size:14px; font-weight:700; margin-bottom:14px;
                       display:flex; align-items:center; gap:8px">
                <i class="fas fa-clock"></i> Tiempo de Almacenamiento
            </h3>
            <div class="form-group" style="margin-bottom:0">
                <label>Tiempo máximo de almacenamiento (días)</label>
                <input type="number" name="tiempo_maximo_dias" min="1"
                       value="{{ $config ? $config->tiempo_maximo_dias : 7 }}" required>
                <small style="color:var(--muted); font-size:11px; margin-top:4px; display:block">
                    <i class="fas fa-info-circle"></i>
                    Días máximos antes de generar alerta de tiempo excedido.
                </small>
            </div>
        </div>

        {{-- Criterios de peso --}}
        <div style="background:#F0F9FF; border:1px solid rgba(59,130,246,0.2);
                    border-radius:12px; padding:20px; margin-bottom:16px">
            <h3 style="color:#2563EB; font-size:14px; font-weight:700; margin-bottom:14px;
                       display:flex; align-items:center; gap:8px">
                <i class="fas fa-weight-hanging"></i> Criterios de Peso por Estante
            </h3>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px">
                <div class="form-group" style="margin-bottom:0">
                    <label>
                        Peso máximo pequeño (kg)
                        <span style="background:#DBEAFE; color:#1D4ED8; font-size:10px;
                                     padding:1px 7px; border-radius:10px; font-weight:700;
                                     margin-left:4px">Estante 1 — Arriba</span>
                    </label>
                    <input type="number" name="peso_maximo_pequeno" step="0.1" min="0.1"
                           value="{{ $config ? $config->peso_maximo_pequeno : 5 }}" required>
                    <small style="color:var(--muted); font-size:11px; margin-top:4px; display:block">
                        <i class="fas fa-info-circle"></i> Paquetes ≤ este valor van arriba.
                    </small>
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label>
                        Peso máximo mediano (kg)
                        <span style="background:#EDE9FE; color:#5B21B6; font-size:10px;
                                     padding:1px 7px; border-radius:10px; font-weight:700;
                                     margin-left:4px">Estante 2 — Medio</span>
                    </label>
                    <input type="number" name="peso_maximo_mediano" step="0.1" min="0.1"
                           value="{{ $config ? $config->peso_maximo_mediano : 20 }}" required>
                    <small style="color:var(--muted); font-size:11px; margin-top:4px; display:block">
                        <i class="fas fa-info-circle"></i> Los mayores van al Estante 3 (Abajo).
                    </small>
                </div>
            </div>
        </div>

        {{-- Zona de reubicación --}}
        <div style="background:#F0FDF4; border:1px solid rgba(16,185,129,0.2);
                    border-radius:12px; padding:20px; margin-bottom:20px">
            <h3 style="color:#059669; font-size:14px; font-weight:700; margin-bottom:14px;
                       display:flex; align-items:center; gap:8px">
                <i class="fas fa-map-marker-alt"></i> Zona de Reubicación
            </h3>
            <div class="form-group" style="margin-bottom:0">
                <label>Zona de reubicación por defecto</label>
                <select name="id_zona_reubicacion">
                    <option value="">Sin zona de reubicación</option>
                    @foreach($zonas as $zona)
                        <option value="{{ $zona->id }}"
                            {{ $config && $config->id_zona_reubicacion == $zona->id ? 'selected' : '' }}>
                            {{ $zona->nombre }}
                        </option>
                    @endforeach
                </select>
                <small style="color:var(--muted); font-size:11px; margin-top:4px; display:block">
                    <i class="fas fa-info-circle"></i>
                    Zona donde se reubicarán los paquetes con tiempo excedido.
                </small>
            </div>
        </div>

        {{-- Botón --}}
        <button type="submit" class="btn btn-success" style="width:100%; padding:12px; font-size:15px">
            <i class="fas fa-save"></i> Guardar Configuración
        </button>
    </form>

    {{-- Última actualización --}}
    @if($config)
    <div style="margin-top:20px; padding-top:16px; border-top:1px solid var(--border);
                display:flex; align-items:center; gap:8px; color:var(--muted); font-size:13px">
        <i class="fas fa-history" style="color:var(--primary); font-size:12px"></i>
        <span>Última actualización:</span>
        <strong style="color:var(--dark)">
            {{ \Carbon\Carbon::parse($config->fecha_actualizacion)->format('d/m/Y H:i') }}
        </strong>
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