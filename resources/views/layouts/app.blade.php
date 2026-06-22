<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Shalom — @yield('titulo')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }

        /* ── NAVBAR ── */
        .navbar {
            background: linear-gradient(135deg, #c0392b, #e74c3c);
            color: white; padding: 0 30px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            min-height: 60px;
        }
        .navbar-brand {
            display: flex; align-items: center; gap: 10px;
            font-size: 20px; font-weight: bold; color: white;
            text-decoration: none;
        }
        .navbar-brand span {
            background: #f39c12; color: white;
            padding: 4px 10px; border-radius: 20px;
            font-size: 12px; font-weight: bold;
        }
        .navbar-links { display: flex; align-items: center; gap: 5px; flex-wrap: wrap; }
        .navbar-links a {
            color: white; text-decoration: none;
            padding: 8px 14px; border-radius: 4px;
            font-size: 14px; transition: background 0.2s;
        }
        .navbar-links a:hover { background: rgba(255,255,255,0.2); }
        .navbar-right { display: flex; align-items: center; gap: 10px; }
        .navbar-user {
            color: white; font-size: 13px;
            background: rgba(255,255,255,0.15);
            padding: 6px 12px; border-radius: 20px;
        }

        /* ── CONTAINER ── */
        .container { max-width: 1200px; margin: 25px auto; padding: 0 20px; }

        /* ── CARD ── */
        .card {
            background: white; border-radius: 10px;
            padding: 25px; margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-top: 3px solid #e74c3c;
        }

        /* ── BOTONES ── */
        .btn {
            padding: 8px 18px; border: none; border-radius: 5px;
            cursor: pointer; text-decoration: none; display: inline-block;
            font-size: 14px; font-weight: bold; transition: opacity 0.2s;
        }
        .btn:hover { opacity: 0.85; }
        .btn-primary { background: #e74c3c; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-danger  { background: #c0392b; color: white; }
        .btn-warning { background: #f39c12; color: white; }
        .btn-secondary { background: #7f8c8d; color: white; }

        /* ── TABLA ── */
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 10px; text-align: left; border-bottom: 1px solid #ecf0f1; }
        th { background: linear-gradient(135deg, #c0392b, #e74c3c); color: white; font-size: 13px; }
        tr:hover { background: #fef9f9; }

        /* ── ALERTAS ── */
        .alert-success {
            background: #d5f5e3; color: #1e8449;
            padding: 12px 15px; border-radius: 6px;
            margin-bottom: 15px; border-left: 4px solid #27ae60;
        }
        .alert-error {
            background: #fadbd8; color: #922b21;
            padding: 12px 15px; border-radius: 6px;
            margin-bottom: 15px; border-left: 4px solid #e74c3c;
        }

        /* ── BADGES ── */
        .badge {
            padding: 4px 10px; border-radius: 12px;
            font-size: 11px; font-weight: bold;
        }
        .badge-recibido        { background: #17a2b8; color: white; }
        .badge-clasificado     { background: #2980b9; color: white; }
        .badge-en_espera       { background: #f39c12; color: white; }
        .badge-despachado      { background: #27ae60; color: white; }
        .badge-daniado         { background: #e74c3c; color: white; }
        .badge-tiempo_excedido { background: #8e44ad; color: white; }

        /* ── FORMULARIOS ── */
        input, select, textarea {
            width: 100%; padding: 10px 12px;
            border: 1px solid #ddd; border-radius: 6px;
            margin-bottom: 15px; font-size: 14px;
            transition: border-color 0.2s;
        }
        input:focus, select:focus, textarea:focus {
            border-color: #e74c3c; outline: none;
            box-shadow: 0 0 0 2px rgba(231,76,60,0.1);
        }
        label { font-weight: bold; margin-bottom: 5px; display: block; color: #2c3e50; }
        .form-group { margin-bottom: 18px; }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .navbar { flex-direction: column; padding: 10px 15px; gap: 8px; }
            .navbar-links { justify-content: center; }
            .navbar-right { justify-content: center; }
            .container { padding: 0 10px; margin: 15px auto; }
            .card { padding: 15px; }
            table { font-size: 12px; }
            th, td { padding: 8px 6px; }
            .btn { padding: 6px 12px; font-size: 13px; }
        }
        @media (max-width: 480px) {
            .navbar-links a { padding: 6px 8px; font-size: 12px; }
            table { display: block; overflow-x: auto; }
        }
    </style>
</head>
<body>
    @auth
    <nav class="navbar">
        <a href="{{ route('encomiendas.index') }}" class="navbar-brand">
            🚚 Shalom <span>ALMACÉN</span>
        </a>
        <div class="navbar-links">
            <a href="{{ route('encomiendas.index') }}">📦 Encomiendas</a>
            @if(auth()->user()->rol === 'supervisor')
                <a href="{{ route('alertas.index') }}">🔔 Alertas</a>
                <a href="{{ route('reportes.index') }}">📊 Reportes</a>
            @endif
            @if(auth()->user()->rol === 'administrador')
                <a href="{{ route('zonas.index') }}">🏢 Zonas</a>
                <a href="{{ route('usuarios.index') }}">👥 Usuarios</a>
                <a href="{{ route('configuracion') }}">⚙️ Config</a>
            @endif
            @if(auth()->user()->rol === 'operario' || auth()->user()->rol === 'administrador')
                <a href="{{ route('almacen') }}">🏭 Almacén 2D</a>
            @endif
        </div>
        <div class="navbar-right">
            <span class="navbar-user">👤 {{ auth()->user()->name }} · {{ auth()->user()->rol }}</span>
            <form action="{{ route('logout') }}" method="POST" style="display:inline">
                @csrf
                <button type="submit" class="btn btn-danger">Salir</button>
            </form>
        </div>
    </nav>
    @endauth

    <div class="container">
        @if(session('success'))
            <div class="alert-success">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert-error">❌ {{ session('error') }}</div>
        @endif

        @yield('contenido')
    </div>
</body>
</html>