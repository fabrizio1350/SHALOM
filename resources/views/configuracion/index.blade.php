@extends('layouts.app')

@section('titulo', 'Configuración')

@section('contenido')
<div class="card" style="max-width:500px; margin:0 auto">
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
        <div class="form-group">
            <label>Tiempo máximo de almacenamiento (días)</label>
            <input type="number" name="tiempo_maximo_dias" min="1"
                value="{{ $config ? $config->tiempo_maximo_dias : 7 }}" required>
            <small style="color:#666">Días máximos que puede estar un paquete en el almacén antes de generar alerta.</small>
        </div>
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
            <small style="color:#666">Zona donde se reubicarán automáticamente los paquetes con tiempo excedido.</small>
        </div>
        <div style="display:flex; gap:10px">
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