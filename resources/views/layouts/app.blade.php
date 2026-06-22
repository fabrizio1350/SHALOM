<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Shalom — @yield('titulo')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --red:      #E8272A;
            --red-dark: #C0201E;
            --red-light:#FFF0F0;
            --dark:     #1A1A2E;
            --gray:     #F4F6F9;
            --text:     #2D3436;
            --muted:    #636E72;
            --white:    #FFFFFF;
            --shadow:   0 4px 20px rgba(0,0,0,0.08);
            --radius:   12px;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter',Arial,sans-serif; background:var(--gray); color:var(--text); }

        /* ══ NAVBAR ══ */
        .navbar {
            background:var(--white);
            border-bottom: 3px solid var(--red);
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 64px;
            box-shadow: 0 2px 12px rgba(232,39,42,0.12);
            position: sticky;
            top: 0;
            z-index: 999;
        }
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .navbar-brand-icon {
            background: var(--red);
            color: white;
            width: 38px; height: 38px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
        }
        .navbar-brand-text {
            font-size: 18px;
            font-weight: 800;
            color: var(--dark);
            letter-spacing: -0.5px;
        }
        .navbar-brand-text span {
            color: var(--red);
        }
        .navbar-links {
            display: flex;
            align-items: center;
            gap: 2px;
        }
        .navbar-links a {
            color: var(--muted);
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .navbar-links a:hover {
            background: var(--red-light);
            color: var(--red);
        }
        .navbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .navbar-user {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--gray);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
        }
        .navbar-user-dot {
            width: 8px; height: 8px;
            background: #27AE60;
            border-radius: 50%;
        }
        .btn-logout {
            background: var(--red);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-logout:hover { background: var(--red-dark); }

        /* ══ CONTAINER ══ */
        .container { max-width: 1200px; margin: 28px auto; padding: 0 24px; }

        /* ══ CARD ══ */
        .card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 28px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(0,0,0,0.04);
        }

        /* ══ BOTONES ══ */
        .btn {
            padding: 9px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            transition: all 0.2s;
        }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .btn-primary   { background: var(--red);    color: white; }
        .btn-success   { background: #27AE60;        color: white; }
        .btn-danger    { background: var(--red-dark); color: white; }
        .btn-warning   { background: #F39C12;        color: white; }
        .btn-secondary { background: #636E72;        color: white; }

        /* ══ TABLA ══ */
        table { width: 100%; border-collapse: collapse; }
        th {
            background: linear-gradient(135deg, var(--red-dark), var(--red));
            color: white;
            padding: 13px 14px;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        th:first-child { border-radius: 8px 0 0 0; }
        th:last-child  { border-radius: 0 8px 0 0; }
        td { padding: 13px 14px; border-bottom: 1px solid #F0F3F7; font-size: 14px; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: var(--red-light); }

        /* ══ ALERTAS ══ */
        .alert-success {
            background: #EAFAF1;
            color: #1E8449;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 18px;
            border-left: 4px solid #27AE60;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .alert-error {
            background: #FDEDEC;
            color: #922B21;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 18px;
            border-left: 4px solid var(--red);
            font-size: 14px;
            font-weight: 500;
        }

        /* ══ BADGES ══ */
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-recibido        { background: #D6EAF8; color: #1A5276; }
        .badge-clasificado     { background: #D6EAF8; color: #1F618D; }
        .badge-en_espera       { background: #FEF9E7; color: #9A7D0A; }
        .badge-despachado      { background: #EAFAF1; color: #1E8449; }
        .badge-daniado         { background: #FDEDEC; color: #922B21; }
        .badge-tiempo_excedido { background: #F5EEF8; color: #6C3483; }

        /* ══ FORMULARIOS ══ */
        input, select, textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #DFE6E9;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
            font-family: inherit;
            color: var(--text);
            transition: border-color 0.2s, box-shadow 0.2s;
            background: white;
        }
        input:focus, select:focus, textarea:focus {
            border-color: var(--red);
            outline: none;
            box-shadow: 0 0 0 3px rgba(232,39,42,0.1);
        }
        label {
            font-weight: 600;
            margin-bottom: 6px;
            display: block;
            color: var(--text);
            font-size: 13px;
        }
        .form-group { margin-bottom: 18px; }

        /* ══ RESPONSIVE ══ */
        @media (max-width: 768px) {
            .navbar { flex-wrap: wrap; height: auto; padding: 10px 15px; gap: 8px; }
            .navbar-links { justify-content: center; flex-wrap: wrap; }
            .container { padding: 0 12px; margin: 16px auto; }
            .card { padding: 16px; }
            table { font-size: 12px; }
            th, td { padding: 10px 8px; }
        }
        @media (max-width: 480px) {
            .navbar-links a { padding: 6px 10px; font-size: 12px; }
            table { display: block; overflow-x: auto; }
            .btn { padding: 8px 14px; font-size: 13px; }
        }
    </style>
</head>
<body>

@auth
<nav class="navbar">
    <a href="{{ route('encomiendas.index') }}" class="navbar-brand">
        <div class="navbar-brand-icon">🚚</div>
        <span class="navbar-brand-text">Sha<span>lom</span></span>
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
        <div class="navbar-user">
            <div class="navbar-user-dot"></div>
            {{ auth()->user()->name }}
            <span style="color:var(--muted); font-weight:400">· {{ auth()->user()->rol }}</span>
        </div>
        <form action="{{ route('logout') }}" method="POST" style="display:inline">
            @csrf
            <button type="submit" class="btn-logout">Salir</button>
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