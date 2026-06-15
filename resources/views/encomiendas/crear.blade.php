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
            <input type="text" name="ciudad_destino" value="{{ old('ciudad_destino') }}" required>
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