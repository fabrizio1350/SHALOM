<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Shalom — @yield('titulo')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary:      #E8272A;
            --primary-dark: #B71C1C;
            --primary-light:#FFF5F5;
            --accent:       #FF6B35;
            --dark:         #0F172A;
            --dark2:        #1E293B;
            --surface:      #F8FAFC;
            --white:        #FFFFFF;
            --border:       #E2E8F0;
            --text:         #0F172A;
            --muted:        #64748B;
            --success:      #10B981;
            --warning:      #F59E0B;
            --info:         #3B82F6;
            --shadow-sm:    0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.06);
            --shadow:       0 4px 6px rgba(0,0,0,0.07), 0 2px 4px rgba(0,0,0,0.06);
            --shadow-lg:    0 10px 40px rgba(0,0,0,0.1), 0 4px 16px rgba(0,0,0,0.06);
            --radius:       12px;
            --radius-lg:    16px;
        }

        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--surface);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* ══════════════════════════════════════
           NAVBAR
        ══════════════════════════════════════ */
        .navbar {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            padding: 0 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 68px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(232,39,42,0.10);
        }

        .navbar::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--accent), var(--primary));
            background-size: 200% auto;
            animation: shimmer 3s linear infinite;
        }
        @keyframes shimmer {
            0%   { background-position: 0% center; }
            100% { background-position: 200% center; }
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }
        .brand-logo {
            width: 44px; height: 44px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 4px 12px rgba(232,39,42,0.35);
            transition: transform 0.2s;
        }
        .brand-logo:hover { transform: scale(1.05); }
        .brand-text {
            display: flex;
            flex-direction: column;
        }
        .brand-name {
            font-size: 18px;
            font-weight: 800;
            color: var(--dark);
            letter-spacing: -0.5px;
            line-height: 1;
        }
        .brand-name span { color: var(--primary); }
        .brand-sub {
            font-size: 10px;
            color: var(--muted);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        .navbar-links {
            display: flex;
            align-items: center;
            gap: 2px;
        }
        .nav-link {
            color: var(--muted);
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 500;
            transition: all 0.18s ease;
            display: flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            position: relative;
        }
        .nav-link:hover {
            background: var(--primary-light);
            color: var(--primary);
            transform: translateY(-1px);
        }
        .nav-link i { font-size: 13px; }
        .nav-link-active {
            background: var(--primary-light) !important;
            color: var(--primary) !important;
            font-weight: 700;
        }
        .nav-link-active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            transform: translateX(-50%);
            width: 60%;
            height: 2px;
            background: var(--primary);
            border-radius: 2px;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .user-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--surface);
            border: 1px solid var(--border);
            padding: 7px 14px;
            border-radius: 40px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            cursor: default;
        }
        .user-avatar {
            width: 28px; height: 28px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            font-weight: 700;
        }
        .user-role {
            background: var(--primary-light);
            color: var(--primary);
            font-size: 10px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .btn-logout {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.18s;
            box-shadow: 0 2px 8px rgba(232,39,42,0.3);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-logout:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(232,39,42,0.4);
        }

        /* ══════════════════════════════════════
           CONTENIDO
        ══════════════════════════════════════ */
        .container { max-width: 1240px; margin: 28px auto; padding: 0 24px; }

        /* ══════════════════════════════════════
           CARDS
        ══════════════════════════════════════ */
        .card {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 28px 32px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }
        .card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }

        /* ══════════════════════════════════════
           BOTONES
        ══════════════════════════════════════ */
        .btn {
            padding: 9px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 13.5px;
            font-weight: 600;
            font-family: inherit;
            transition: all 0.18s ease;
            white-space: nowrap;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 2px 8px rgba(232,39,42,0.3);
        }
        .btn-primary:hover { box-shadow: 0 4px 16px rgba(232,39,42,0.4); }
        .btn-success {
            background: linear-gradient(135deg, #10B981, #059669);
            color: white;
            box-shadow: 0 2px 8px rgba(16,185,129,0.3);
        }
        .btn-success:hover { box-shadow: 0 4px 16px rgba(16,185,129,0.4); }
        .btn-danger {
            background: linear-gradient(135deg, #EF4444, #DC2626);
            color: white;
            box-shadow: 0 2px 8px rgba(239,68,68,0.3);
        }
        .btn-warning {
            background: linear-gradient(135deg, #F59E0B, #D97706);
            color: white;
            box-shadow: 0 2px 8px rgba(245,158,11,0.3);
        }
        .btn-secondary {
            background: var(--surface);
            color: var(--muted);
            border: 1px solid var(--border);
        }
        .btn-secondary:hover { background: var(--border); color: var(--text); }

        /* ══════════════════════════════════════
           TABLA
        ══════════════════════════════════════ */
        .table-wrapper { overflow-x: auto; border-radius: var(--radius); border: 1px solid var(--border); }
        table { width: 100%; border-collapse: collapse; }
        thead { background: linear-gradient(135deg, var(--dark), var(--dark2)); }
        th {
            color: rgba(255,255,255,0.9);
            padding: 14px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        td {
            padding: 13px 16px;
            border-bottom: 1px solid var(--border);
            font-size: 13.5px;
            color: var(--text);
        }
        tr:last-child td { border-bottom: none; }
        tbody tr { transition: background 0.15s; }
        tbody tr:hover td { background: var(--primary-light); }

        /* ══════════════════════════════════════
           ALERTAS SISTEMA
        ══════════════════════════════════════ */
        .alert-success {
            background: #F0FDF4;
            color: #166534;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #BBF7D0;
            border-left: 4px solid var(--success);
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        }
        .alert-error {
            background: #FFF5F5;
            color: #9B1C1C;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #FED7D7;
            border-left: 4px solid var(--primary);
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { opacity:0; transform: translateY(-8px); }
            to   { opacity:1; transform: translateY(0); }
        }

        /* ══════════════════════════════════════
           BADGES
        ══════════════════════════════════════ */
        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            display: inline-block;
            white-space: nowrap;
        }
        .badge-recibido        { background:#DBEAFE; color:#1D4ED8; }
        .badge-clasificado     { background:#EDE9FE; color:#5B21B6; }
        .badge-en_espera       { background:#FEF3C7; color:#92400E; }
        .badge-despachado      { background:#D1FAE5; color:#065F46; }
        .badge-daniado         { background:#FEE2E2; color:#991B1B; }
        .badge-tiempo_excedido { background:#F3E8FF; color:#6B21A8; }

        /* ══════════════════════════════════════
           FORMULARIOS
        ══════════════════════════════════════ */
        input, select, textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            margin-bottom: 0;
            font-size: 14px;
            font-family: inherit;
            color: var(--text);
            background: var(--white);
            transition: all 0.18s;
        }
        input:focus, select:focus, textarea:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(232,39,42,0.08);
        }
        input::placeholder, textarea::placeholder { color: #CBD5E1; }
        label {
            font-weight: 600;
            margin-bottom: 7px;
            display: block;
            color: var(--dark2);
            font-size: 13px;
        }
        .form-group { margin-bottom: 20px; }

        /* ══════════════════════════════════════
           STATS CARDS
        ══════════════════════════════════════ */
        .stat-card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 20px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            position: relative;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }
        .stat-number { font-size: 32px; font-weight: 800; line-height: 1; margin-bottom: 4px; }
        .stat-label  { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.75; }
        .stat-icon   { font-size: 28px; position: absolute; top: 16px; right: 16px; opacity: 0.2; }

        /* ══════════════════════════════════════
           RESPONSIVE
        ══════════════════════════════════════ */
        @media (max-width: 900px) {
            .navbar { flex-wrap: wrap; height: auto; padding: 12px 16px; gap: 10px; }
            .navbar-links { justify-content: center; flex-wrap: wrap; order: 3; width: 100%; }
            .container { padding: 0 16px; margin: 16px auto; }
        }
        @media (max-width: 560px) {
            .card { padding: 18px; }
            .nav-link { padding: 7px 10px; font-size: 12px; }
            .btn { padding: 8px 14px; font-size: 13px; }
            .stat-number { font-size: 26px; }
        }
    </style>
</head>
<body>

@auth
<nav class="navbar">
    <a href="{{ route('encomiendas.index') }}" class="navbar-brand">
        <div class="brand-logo">
            <i class="fas fa-truck-fast" style="color:white; font-size:20px"></i>
        </div>
        <div class="brand-text">
            <span class="brand-name">Sha<span>lom</span></span>
            <span class="brand-sub">Sistema de Almacén</span>
        </div>
    </a>

    <div class="navbar-links">
        <a href="{{ route('encomiendas.index') }}"
           class="nav-link {{ request()->routeIs('encomiendas.*') || request()->routeIs('dashboard') ? 'nav-link-active' : '' }}">
            <i class="fas fa-box"></i> Encomiendas
        </a>

        @if(auth()->user()->rol === 'supervisor')
            <a href="{{ route('alertas.index') }}"
               class="nav-link {{ request()->routeIs('alertas.*') ? 'nav-link-active' : '' }}">
                <i class="fas fa-bell"></i> Alertas
            </a>
            <a href="{{ route('reportes.index') }}"
               class="nav-link {{ request()->routeIs('reportes.*') ? 'nav-link-active' : '' }}">
                <i class="fas fa-chart-bar"></i> Reportes
            </a>
        @endif

        @if(auth()->user()->rol === 'administrador')
            <a href="{{ route('zonas.index') }}"
               class="nav-link {{ request()->routeIs('zonas.*') ? 'nav-link-active' : '' }}">
                <i class="fas fa-warehouse"></i> Zonas
            </a>
            <a href="{{ route('usuarios.index') }}"
               class="nav-link {{ request()->routeIs('usuarios.*') ? 'nav-link-active' : '' }}">
                <i class="fas fa-users"></i> Usuarios
            </a>
            <a href="{{ route('configuracion') }}"
               class="nav-link {{ request()->routeIs('configuracion*') ? 'nav-link-active' : '' }}">
                <i class="fas fa-cog"></i> Config
            </a>
        @endif

        @if(in_array(auth()->user()->rol, ['operario', 'supervisor', 'administrador']))
            <a href="{{ route('almacen') }}"
               class="nav-link {{ request()->routeIs('almacen') ? 'nav-link-active' : '' }}">
                <i class="fas fa-industry"></i> Almacén 2D
            </a>
        @endif
    </div>

    <div class="navbar-right">
        <div class="user-pill">
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            {{ auth()->user()->name }}
            <span class="user-role">{{ auth()->user()->rol }}</span>
        </div>
        <form action="{{ route('logout') }}" method="POST" style="display:inline">
            @csrf
            <button type="submit" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Salir
            </button>
        </form>
    </div>
</nav>
@endauth

<div class="container">
    @if(session('success'))
        <div class="alert-success">
            <i class="fas fa-check-circle" style="color:#10B981; font-size:16px"></i>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert-error">
            <i class="fas fa-exclamation-circle" style="color:var(--primary); font-size:16px"></i>
            {{ session('error') }}
        </div>
    @endif
    @yield('contenido')
</div>

</body>
</html>