@extends('layouts.app')

@section('titulo', 'Usuarios')

@section('contenido')
<div class="card">

    {{-- Header --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px">
        <div>
            <h2 style="color:var(--dark); font-size:22px; font-weight:800; margin-bottom:3px">
                <i class="fas fa-users" style="color:var(--primary)"></i> Usuarios del Sistema
            </h2>
            <p style="color:var(--muted); font-size:13px">Gestión de cuentas del personal</p>
        </div>
        <a href="{{ route('usuarios.crear') }}" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Nuevo Usuario
        </a>
    </div>

    {{-- Tabla --}}
    <div style="overflow-x:auto; border-radius:var(--radius); border:1px solid var(--border)">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($usuarios as $usuario)
            @php
                $rolStyles = [
                    'administrador' => ['bg'=>'#0F172A', 'color'=>'#FFFFFF', 'icon'=>'fa-crown'],
                    'supervisor'    => ['bg'=>'#DBEAFE', 'color'=>'#1D4ED8', 'icon'=>'fa-user-tie'],
                    'operario'      => ['bg'=>'#D1FAE5', 'color'=>'#065F46', 'icon'=>'fa-user-hard-hat'],
                ];
                $rol = $rolStyles[$usuario->rol] ?? ['bg'=>'#F1F5F9', 'color'=>'#64748B', 'icon'=>'fa-user'];
            @endphp
            <tr>
                <td>
                    <span style="font-size:12px; font-weight:700; color:var(--muted)">#{{ $usuario->id }}</span>
                </td>
                <td>
                    <div style="display:flex; align-items:center; gap:8px">
                        <div style="width:32px; height:32px; border-radius:50%;
                                    background:linear-gradient(135deg, var(--primary), var(--accent));
                                    display:flex; align-items:center; justify-content:center;
                                    color:white; font-size:12px; font-weight:700; flex-shrink:0">
                            {{ strtoupper(substr($usuario->name, 0, 1)) }}
                        </div>
                        <span style="font-weight:600; color:var(--dark)">{{ $usuario->name }}</span>
                        @if($usuario->id === auth()->id())
                            <span style="background:var(--primary-light); color:var(--primary);
                                         font-size:10px; font-weight:700; padding:1px 7px;
                                         border-radius:10px">Tú</span>
                        @endif
                    </div>
                </td>
                <td style="color:var(--muted); font-size:13px">
                    <i class="fas fa-envelope" style="font-size:10px"></i>
                    {{ $usuario->email }}
                </td>
                <td>
                    <span class="badge" style="background:{{ $rol['bg'] }}; color:{{ $rol['color'] }}">
                        <i class="fas {{ $rol['icon'] }}" style="font-size:9px"></i>
                        {{ strtoupper($usuario->rol) }}
                    </span>
                </td>
                <td>
                    <span class="badge" style="background:{{ $usuario->estado === 'activo' ? '#D1FAE5' : '#FEE2E2' }};
                                               color:{{ $usuario->estado === 'activo' ? '#065F46' : '#991B1B' }}">
                        <i class="fas {{ $usuario->estado === 'activo' ? 'fa-circle' : 'fa-circle' }}"
                           style="font-size:7px"></i>
                        {{ strtoupper($usuario->estado) }}
                    </span>
                </td>
                <td>
                    @if($usuario->id !== auth()->id())
                    <form action="{{ route('usuarios.estado', $usuario->id) }}" method="POST" style="display:inline">
                        @csrf
                        <button type="submit"
                                class="btn {{ $usuario->estado === 'activo' ? 'btn-danger' : 'btn-success' }}"
                                style="font-size:12px; padding:5px 12px"
                                onclick="return confirm('¿Cambiar estado de este usuario?')">
                            <i class="fas {{ $usuario->estado === 'activo' ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                            {{ $usuario->estado === 'activo' ? 'Desactivar' : 'Activar' }}
                        </button>
                    </form>
                    @else
                    <span style="color:var(--muted); font-size:12px; font-style:italic">
                        Cuenta actual
                    </span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; padding:40px; color:var(--muted)">
                    <i class="fas fa-users" style="font-size:24px; opacity:0.3; display:block; margin-bottom:8px"></i>
                    No hay usuarios registrados.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection