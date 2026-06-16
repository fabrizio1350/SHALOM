@extends('layouts.app')

@section('titulo', 'Configuración')

@section('contenido')
<div class="card" style="max-width:600px; margin:0 auto">
    <h2 style="margin-bottom:25px">⚙️ Configuración del Sistema</h2>

    @if($errors->any())
        <div class="alert-error">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('configuracion.guardar') }}" method="POST">
        @csrf

        <h3 style="margin-bottom:15px; color:#1a1a2e">⏱ Tiempo de Almacenamiento</h3>
        <div class="form-group">
            <label>Tiempo máximo de almacenamiento (días)</label>
            <input type="number" name="tiempo_maximo_dias" min="1"
                value="{{ $config ? $config->tiempo_maximo_dias : 7 }}" required>
            <small style="color:#666">Días máximos antes de generar alerta de tiempo excedido.</small>
        </div>

        <h3 style="margin-bottom:15px; margin-top:20px; color:#1a1a2e">⚖️ Criterios de Peso por Zona</h3>
        <div class="form-group">
            <label>Peso máximo paquete pequeño (kg) → Zona A</label>
            <input type="number" name="peso_maximo_pequeno" step="0.1" min="0.1"
                value="{{ $config ? $config->peso_maximo_pequeno : 5 }}" required>
            <small style="color:#666">Paquetes con peso menor o igual van a Zona A, Estante 1.</small>
        </div>
        <div class="form-group">
            <label>Peso máximo paquete mediano (kg) → Zona B</label>
            <input type="number" name="peso_maximo_mediano" step="0.1" min="0.1"
                value="{{ $config ? $config->peso_maximo_mediano : 20 }}" required>
            <small style="color:#666">Paquetes entre peso pequeño y este valor van a Zona B, Estante 2. Los mayores van a Zona C, Estante 3.</small>
        </div>

        <h3 style="margin-bottom:15px; margin-top:20px; color:#1a1a2e">📍 Zona de Reubicación</h3>
        <div class="form-group">
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
            <small style="color:#666">Zona donde se reubicarán los paquetes con tiempo excedido.</small>
        </div>

        <div style="display:flex; gap:10px; margin-top:20px">
            <button type="submit" class="btn btn-success">Guardar Configuración</button>
        </div>
    </form>

    @if($config)
    <div style="margin-top:25px; padding-top:20px; border-top:1px solid #ddd">
        <p><strong>Última actualización:</strong> {{ \Carbon\Carbon::parse($config->fecha_actualizacion)->format('d/m/Y H:i') }}</p>
    </div>
    @endif
</div>
@endsection