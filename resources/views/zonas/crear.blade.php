@extends('layouts.app')

@section('titulo', 'Crear Zona')

@section('contenido')
<div class="card" style="max-width:500px; margin:0 auto">
    <h2 style="margin-bottom:25px">🏭 Nueva Zona</h2>

    @if($errors->any())
        <div class="alert-error">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('zonas.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label>Nombre de la Zona</label>
            <input type="text" name="nombre" value="{{ old('nombre') }}"
                placeholder="Ej: Zona A, Zona B" required>
        </div>
        <div class="form-group">
            <label>Capacidad máxima (paquetes)</label>
            <input type="number" name="capacidad" value="{{ old('capacidad') }}"
                min="1" required>
        </div>
        <div style="display:flex; gap:10px">
            <button type="submit" class="btn btn-success">Crear Zona</button>
            <a href="{{ route('zonas.index') }}" class="btn btn-warning">Cancelar</a>
        </div>
    </form>
</div>
@endsection