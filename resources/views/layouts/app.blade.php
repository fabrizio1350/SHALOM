<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Shalom — @yield('titulo')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .navbar {
            background: #1a1a2e; color: white; padding: 15px 30px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .navbar a { color: white; text-decoration: none; margin-right: 20px; }
        .navbar a:hover { color: #e94560; }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .card {
            background: white; border-radius: 8px;
            padding: 25px; margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .btn {
            padding: 8px 16px; border: none; border-radius: 4px;
            cursor: pointer; text-decoration: none; display: inline-block;
        }
        .btn-primary { background: #1a1a2e; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger  { background: #dc3545; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #1a1a2e; color: white; }
        tr:hover { background: #f5f5f5; }
        .alert-success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .alert-error   { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .badge {
            padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold;
        }
        .badge-recibido      { background: #17a2b8; color: white; }
        .badge-clasificado   { background: #007bff; color: white; }
        .badge-en_espera     { background: #ffc107; color: black; }
        .badge-despachado    { background: #28a745; color: white; }
        .badge-daniado       { background: #dc3545; color: white; }
        .badge-tiempo_excedido { background: #6f42c1; color: white; }
        input, select, textarea {
            width: 100%; padding: 8px; border: 1px solid #ddd;
            border-radius: 4px; margin-bottom: 15px;
        }
        label { font-weight: bold; margin-bottom: 5px; display: block; }
        .form-group { margin-bottom: 15px; }
    </style>
</head>
<body>
    @auth
    <nav class="navbar">
        <div>
            <strong>🚚 Shalom</strong>
            <a href="{{ route('encomiendas.index') }}" style="margin-left:20px">Encomiendas</a>
            @if(auth()->user()->rol === 'supervisor')
                <a href="{{ route('alertas.index') }}">Alertas</a>
                <a href="{{ route('reportes.index') }}">Reportes</a>
            @endif
            @if(auth()->user()->rol === 'administrador')
                <a href="{{ route('zonas.index') }}">Zonas</a>
                <a href="{{ route('usuarios.index') }}">Usuarios</a>
                <a href="{{ route('configuracion') }}">Configuración</a>
            @endif
        </div>
        <div>
            <span>{{ auth()->user()->name }} ({{ auth()->user()->rol }})</span>
            <form action="{{ route('logout') }}" method="POST" style="display:inline">
                @csrf
                <button type="submit" class="btn btn-danger" style="margin-left:15px">Salir</button>
            </form>
        </div>
    </nav>
    @endauth

    <div class="container">
        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif

        @yield('contenido')
    </div>
</body>
</html>