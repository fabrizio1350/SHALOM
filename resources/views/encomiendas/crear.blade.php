@extends('layouts.app')

@section('titulo', 'Registrar Encomienda')

@section('contenido')
<div class="card" style="max-width:720px; margin:0 auto">

    {{-- Header --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px">
        <div>
            <h2 style="color:var(--dark); font-size:22px; font-weight:800; margin-bottom:3px">
                <i class="fas fa-box" style="color:var(--primary)"></i> Registrar Nueva Encomienda
            </h2>
            <p style="color:var(--muted); font-size:13px">Complete todos los campos obligatorios</p>
        </div>
        <a href="{{ route('encomiendas.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    @if($errors->any())
        <div class="alert-error">
            <i class="fas fa-exclamation-triangle" style="font-size:16px"></i>
            <div>
                @foreach($errors->all() as $error)
                    <p style="margin:2px 0">{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    <form action="{{ route('encomiendas.registrar') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Sección remitente/destinatario --}}
        <div style="background:var(--primary-light); border:1px solid rgba(232,39,42,0.15);
                    border-radius:12px; padding:20px; margin-bottom:20px">
            <h4 style="color:var(--primary); margin-bottom:15px; font-size:14px; font-weight:700;
                       display:flex; align-items:center; gap:8px">
                <i class="fas fa-user"></i> Datos del Envío
            </h4>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px">
                <div class="form-group" style="margin-bottom:0">
                    <label>Remitente</label>
                    <input type="text" name="remitente" value="{{ old('remitente') }}"
                           placeholder="Nombre completo" required>
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label>Destinatario</label>
                    <input type="text" name="destinatario" value="{{ old('destinatario') }}"
                           placeholder="Nombre completo" required>
                </div>
            </div>
            <div class="form-group" style="margin-top:15px; margin-bottom:0">
                <label>Ciudad Destino</label>
                <select name="ciudad_destino" required>
                    <option value="">Seleccionar ciudad...</option>
                    @foreach(['Abancay','Arequipa','Ayacucho','Bagua','Cajamarca','Callao',
                              'Chiclayo','Chimbote','Cusco','Huancayo','Huanuco','Huaraz',
                              'Ica','Ilo','Iquitos','Juliaca','Lima','Moquegua','Moyobamba',
                              'Nazca','Piura','Pucallpa','Puerto Maldonado','Puno','Sullana',
                              'Tacna','Tarapoto','Tarma','Trujillo','Tumbes','Yurimaguas'] as $ciudad)
                        <option value="{{ $ciudad }}" {{ old('ciudad_destino') == $ciudad ? 'selected' : '' }}>
                            {{ $ciudad }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Sección paquete --}}
        <div style="background:#F0F9FF; border:1px solid rgba(59,130,246,0.2);
                    border-radius:12px; padding:20px; margin-bottom:20px">
            <h4 style="color:#2563EB; margin-bottom:15px; font-size:14px; font-weight:700;
                       display:flex; align-items:center; gap:8px">
                <i class="fas fa-ruler-combined"></i> Datos del Paquete
            </h4>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px">
                <div class="form-group" style="margin-bottom:0">
                    <label>Peso (kg)</label>
                    <input type="number" name="peso" step="0.01" min="0.1" max="70"
                           value="{{ old('peso') }}" placeholder="Ej: 5.5" required>
                    <small style="color:var(--muted); font-size:11px; margin-top:4px; display:block">
                        <i class="fas fa-info-circle"></i> Máximo 70 kg
                    </small>
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label>Dimensiones (LxAxH cm)</label>
                    <input type="text" name="dimensiones" value="{{ old('dimensiones') }}"
                           placeholder="Ej: 30x20x15">
                    <small style="color:var(--muted); font-size:11px; margin-top:4px; display:block">
                        <i class="fas fa-info-circle"></i> Máximo 120x80x80 cm
                    </small>
                </div>
            </div>
            <div class="form-group" style="margin-top:15px; margin-bottom:0">
                <label>Descripción del contenido</label>
                <textarea name="descripcion" rows="2"
                          placeholder="Ej: Ropa, libros, electrodomésticos...">{{ old('descripcion') }}</textarea>
            </div>
        </div>

        {{-- Imagen --}}
        <div style="background:var(--surface); border:1.5px dashed var(--border);
                    border-radius:12px; padding:20px; margin-bottom:24px">
            <h4 style="color:var(--muted); margin-bottom:12px; font-size:14px; font-weight:700;
                       display:flex; align-items:center; gap:8px">
                <i class="fas fa-image"></i> Imagen del Paquete
                <span style="font-weight:400; font-size:12px">(opcional)</span>
            </h4>
            <input type="file" name="imagen" accept="image/*"
                   style="padding:8px; background:white; cursor:pointer">
            <small style="color:var(--muted); display:block; margin-top:6px; font-size:11px">
                <i class="fas fa-info-circle"></i> Formatos: JPG, PNG. Máximo 2MB.
            </small>
            @error('imagen')
                <p style="color:var(--primary); font-size:13px; margin-top:5px">
                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Botones --}}
        <div style="display:flex; gap:10px; justify-content:flex-end">
            <a href="{{ route('encomiendas.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-success" style="padding:10px 28px">
                <i class="fas fa-check"></i> Registrar Encomienda
            </button>
        </div>
    </form>
</div>

<style>
@media (max-width: 600px) {
    div[style*="grid-template-columns:1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>
@endsection