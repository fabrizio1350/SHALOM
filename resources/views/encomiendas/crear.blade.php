@extends('layouts.app')

@section('titulo', 'Registrar Encomienda')

@section('contenido')
<div class="card">
    <h2 style="margin-bottom:25px">📦 Registrar Nueva Encomienda</h2>

    @if($errors->any())
        <div class="alert-error">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('encomiendas.registrar') }}" method="POST">
        @csrf
        <div class="form-group">
            <label>Remitente</label>
            <input type="text" name="remitente" value="{{ old('remitente') }}" required>
        </div>
        <div class="form-group">
            <label>Destinatario</label>
            <input type="text" name="destinatario" value="{{ old('destinatario') }}" required>
        </div>
        <div class="form-group">
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
        <div class="form-group">
            <label>Peso (kg)</label>
            <input type="number" name="peso" step="0.01" min="0.1" value="{{ old('peso') }}" required>
        </div>
        <div class="form-group">
            <label>Dimensiones (ej: 30x20x15 cm)</label>
            <input type="text" name="dimensiones" value="{{ old('dimensiones') }}">
        </div>
        <div class="form-group">
            <label>Descripción del contenido</label>
            <textarea name="descripcion" rows="3">{{ old('descripcion') }}</textarea>
        </div>

        <div style="display:flex; gap:10px">
            <button type="submit" class="btn btn-success">Registrar Encomienda</button>
            <a href="{{ route('encomiendas.index') }}" class="btn btn-warning">Cancelar</a>
        </div>
    </form>
</div>
@endsection