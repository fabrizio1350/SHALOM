@extends('layouts.app')

@section('titulo', 'Iniciar Sesión')

@section('contenido')
<div style="max-width: 400px; margin: 80px auto;">
    <div class="card">
        <h2 style="text-align:center; margin-bottom:25px; color:#1a1a2e">
            🚚 Sistema Shalom
        </h2>
        <h3 style="text-align:center; margin-bottom:25px">Iniciar Sesión</h3>

        @if($errors->any())
            <div class="alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Correo electrónico</label>
                <input type="email" name="email" value="{{ old('email') }}" required>
            </div>
            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; padding:12px">
                Ingresar
            </button>
        </form>
    </div>
</div>
@endsection