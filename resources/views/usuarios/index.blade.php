@extends('layouts.app')

@section('titulo', 'Usuarios')

@section('contenido')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px">
        <h2>👥 Usuarios del Sistema</h2>
        <a href="{{ route('usuarios.crear') }}" class="btn btn-primary">+ Nuevo Usuario</a>
    </div>

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
            <tr>
                <td>{{ $usuario->id }}</td>
                <td>{{ $usuario->name }}</td>
                <td>{{ $usuario->email }}</td>
                <td>
                    <span class="badge" style="background:
                        {{ $usuario->rol === 'administrador' ? '#1a1a2e' :
                          ($usuario->rol === 'supervisor' ? '#007bff' : '#28a745') }};
                        color:white">
                        {{ strtoupper($usuario->rol) }}
                    </span>
                </td>
                <td>
                    <span class="badge" style="background:{{ $usuario->estado === 'activo' ? '#28a745' : '#dc3545' }}; color:white">
                        {{ strtoupper($usuario->estado) }}
                    </span>
                </td>
                <td>
                    @if($usuario->id !== auth()->id())
                    <form action="{{ route('usuarios.estado', $usuario->id) }}" method="POST" style="display:inline">
                        @csrf
                        <button type="submit" class="btn {{ $usuario->estado === 'activo' ? 'btn-danger' : 'btn-success' }}"
                            onclick="return confirm('¿Cambiar estado de este usuario?')">
                            {{ $usuario->estado === 'activo' ? 'Desactivar' : 'Activar' }}
                        </button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; padding:20px">No hay usuarios registrados.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection