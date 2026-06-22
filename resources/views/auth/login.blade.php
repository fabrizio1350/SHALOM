@extends('layouts.app')

@section('titulo', 'Iniciar Sesión')

@section('contenido')
<div style="max-width:420px; margin:60px auto;">
    <div style="background:white; border-radius:12px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.15);">
        
        {{-- Header rojo --}}
        <div style="background:linear-gradient(135deg, #c0392b, #e74c3c); padding:30px; text-align:center">
            <div style="font-size:48px; margin-bottom:10px">🚚</div>
            <h2 style="color:white; font-size:22px; margin-bottom:5px">Sistema Shalom</h2>
            <p style="color:rgba(255,255,255,0.8); font-size:13px">Gestión de Almacén</p>
        </div>

        {{-- Formulario --}}
        <div style="padding:30px">
            <h3 style="text-align:center; margin-bottom:25px; color:#2c3e50; font-size:18px">
                Iniciar Sesión
            </h3>

            @if($errors->any())
                <div class="alert-error">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Correo electrónico</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           placeholder="ejemplo@shalom.com" required>
                </div>
                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-primary"
                        style="width:100%; padding:12px; font-size:15px; margin-top:5px">
                    Ingresar al Sistema
                </button>
            </form>

            <div style="text-align:center; margin-top:20px">
                <a href="{{ route('tracking') }}"
                   style="color:#e74c3c; font-size:13px; text-decoration:none">
                    📦 Consultar estado de mi encomienda
                </a>
            </div>
        </div>

        {{-- Footer --}}
        <div style="background:#f8f9fa; padding:12px; text-align:center; border-top:1px solid #ecf0f1">
            <p style="font-size:12px; color:#95a5a6">
                TECSUP III Ciclo 2026 — Grupo E
            </p>
        </div>
    </div>
</div>
@endsection