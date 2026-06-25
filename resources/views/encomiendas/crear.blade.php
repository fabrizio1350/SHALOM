@extends('layouts.app')

@section('titulo', 'Registrar Encomienda')

@section('contenido')
<div class="card" style="max-width:700px; margin:0 auto">

    {{-- Header --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px">
        <div>
            <h2 style="color:#2c3e50; margin-bottom:4px">📦 Registrar Nueva Encomienda</h2>
            <p style="color:#888; font-size:13px">Complete todos los campos obligatorios</p>
        </div>
        <a href="{{ route('encomiendas.index') }}" class="btn btn-secondary">← Volver</a>
    </div>

    @if($errors->any())
        <div class="alert-error">
            @foreach($errors->all() as $error)
                <p>⚠️ {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('encomiendas.registrar') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Sección remitente/destinatario --}}
        <div style="background:#fef9f9; border:1px solid #fad5d5; border-radius:8px; padding:15px; margin-bottom:20px">
            <h4 style="color:#e74c3c; margin-bottom:15px">👤 Datos del Envío</h4>
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
                    <option value="Abancay">Abancay</option>
                    <option value="Arequipa">Arequipa</option>
                    <option value="Ayacucho">Ayacucho</option>
                    <option value="Bagua">Bagua</option>
                    <option value="Cajamarca">Cajamarca</option>
                    <option value="Callao">Callao</option>
                    <option value="Chiclayo">Chiclayo</option>
                    <option value="Chimbote">Chimbote</option>
                    <option value="Cusco">Cusco</option>
                    <option value="Huancayo">Huancayo</option>
                    <option value="Huanuco">Huánuco</option>
                    <option value="Huaraz">Huaraz</option>
                    <option value="Ica">Ica</option>
                    <option value="Ilo">Ilo</option>
                    <option value="Iquitos">Iquitos</option>
                    <option value="Juliaca">Juliaca</option>
                    <option value="Lima">Lima</option>
                    <option value="Moquegua">Moquegua</option>
                    <option value="Moyobamba">Moyobamba</option>
                    <option value="Nazca">Nazca</option>
                    <option value="Piura">Piura</option>
                    <option value="Pucallpa">Pucallpa</option>
                    <option value="Puerto Maldonado">Puerto Maldonado</option>
                    <option value="Puno">Puno</option>
                    <option value="Sullana">Sullana</option>
                    <option value="Tacna">Tacna</option>
                    <option value="Tarapoto">Tarapoto</option>
                    <option value="Tarma">Tarma</option>
                    <option value="Trujillo">Trujillo</option>
                    <option value="Tumbes">Tumbes</option>
                    <option value="Yurimaguas">Yurimaguas</option>
                </select>
            </div>
        </div>

        {{-- Sección paquete --}}
        <div style="background:#f0f9ff; border:1px solid #bde0f7; border-radius:8px; padding:15px; margin-bottom:20px">
            <h4 style="color:#2980b9; margin-bottom:15px">📏 Datos del Paquete</h4>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px">
                <div class="form-group" style="margin-bottom:0">
                    <label>Peso (kg)</label>
                    <input type="number" name="peso" step="0.01" min="1" max="70"
                           value="{{ old('peso') }}" placeholder="Ej: 5.5" required>
                    <small style="color:#888; font-size:11px">Máximo 70 kg</small>
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label>Dimensiones (LxAxH cm)</label>
                    <input type="text" name="dimensiones" value="{{ old('dimensiones') }}"
                           placeholder="Ej: 30x20x15">
                    <small style="color:#888; font-size:11px">Máximo 120x80x80 cm</small>
                </div>
            </div>
            <div class="form-group" style="margin-top:15px; margin-bottom:0">
                <label>Descripción del contenido</label>
                <textarea name="descripcion" rows="2"
                          placeholder="Ej: Ropa, libros, electrodomésticos...">{{ old('descripcion') }}</textarea>
            </div>
        </div>

        {{-- Imagen --}}
        <div style="background:#f9f9f9; border:1px solid #ddd; border-radius:8px; padding:15px; margin-bottom:20px">
            <h4 style="color:#666; margin-bottom:15px">🖼️ Imagen del Paquete (opcional)</h4>
            <input type="file" name="imagen" accept="image/*" style="padding:5px">
            <small style="color:#888; display:block; margin-top:5px">Formatos: JPG, PNG. Máximo 2MB.</small>
            @error('imagen')
                <p style="color:#e74c3c; font-size:13px; margin-top:5px">{{ $message }}</p>
            @enderror
        </div>

        <div style="display:flex; gap:10px; justify-content:flex-end">
            <a href="{{ route('encomiendas.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success" style="padding:10px 25px">
                ✅ Registrar Encomienda
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