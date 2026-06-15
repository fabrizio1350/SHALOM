@extends('layouts.app')

@section('titulo', 'Crear Usuario')

@section('contenido')
<div class="card" style="max-width:500px; margin:0 auto">
    <h2 style="margin-bottom:25px">👤 Nuevo Usuario</h2>

    @if($errors->any())
        <div class="alert-error">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('usuarios.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label>Nombre completo</label>
            <input type="text" name="name" value="{{ old('name') }}" required>
        </div>
        <div class="form-group">
            <label>Correo electrónico</label>
            <input type="email" name="email" value="{{ old('email') }}" required>
        </div>
        <div class="form-group">
            <label>Contraseña</label>
            <input type="password" name="password" required>
        </div>
        <div class="form-group">
            <label>Rol</label>
            <select name="rol" required>
                <option value="">Seleccionar...</option>
                <option value="operario" {{ old('rol') === 'operario' ? 'selected' : '' }}>Operario</option>
                <option value="supervisor" {{ old('rol') === 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                <option value="administrador" {{ old('rol') === 'administrador' ? 'selected' : '' }}>Administrador</option>
            </select>
        </div>
        <div style="display:flex; gap:10px">
            <button type="submit" class="btn btn-success">Crear Usuario</button>
            <a href="{{ route('usuarios.index') }}" class="btn btn-warning">Cancelar</a>
        </div>
    </form>
</div>
@endsection