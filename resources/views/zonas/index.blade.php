@extends('layouts.app')

@section('titulo', 'Zonas')

@section('contenido')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px">
        <h2>🏭 Zonas del Almacén</h2>
        <a href="{{ route('zonas.crear') }}" class="btn btn-primary">+ Nueva Zona</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Capacidad</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($zonas as $zona)
            <tr>
                <td>{{ $zona->id }}</td>
                <td>{{ $zona->nombre }}</td>
                <td>{{ $zona->capacidad }} paquetes</td>
                <td>
                    <span class="badge" style="background:
                        {{ $zona->estado === 'disponible' ? '#28a745' :
                          ($zona->estado === 'parcialmente_ocupada' ? '#ffc107' :
                          ($zona->estado === 'llena' ? '#dc3545' : '#6c757d')) }};
                        color: {{ $zona->estado === 'parcialmente_ocupada' ? 'black' : 'white' }}">
                        {{ strtoupper(str_replace('_', ' ', $zona->estado)) }}
                    </span>
                </td>
                <td>
                    <form action="{{ route('zonas.estado', $zona->id) }}" method="POST" style="display:inline">
                        @csrf
                        <button type="submit" class="btn btn-danger"
                            onclick="return confirm('¿Eliminar esta zona?')">
                            Eliminar
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center; padding:20px">No hay zonas registradas.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection