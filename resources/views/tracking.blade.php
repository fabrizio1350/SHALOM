<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking — Shalom</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary:      #E8272A;
            --primary-dark: #B71C1C;
            --primary-light:#FFF5F5;
            --accent:       #FF6B35;
            --dark:         #0F172A;
            --surface:      #F8FAFC;
            --white:        #FFFFFF;
            --border:       #E2E8F0;
            --muted:        #64748B;
            --success:      #10B981;
            --shadow:       0 4px 24px rgba(0,0,0,0.08);
        }

        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--surface);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* HEADER */
        .header {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: white;
            padding: 0 32px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 20px rgba(232,39,42,0.3);
            position: relative;
        }
        .header::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, rgba(255,255,255,0.3), rgba(255,255,255,0.1), rgba(255,255,255,0.3));
        }
        .header-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .header-logo {
            width: 42px; height: 42px;
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            backdrop-filter: blur(4px);
        }
        .header-title { font-size: 18px; font-weight: 800; letter-spacing: -0.3px; }
        .header-sub   { font-size: 11px; opacity: 0.75; margin-top: 1px; }
        .header-link {
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 8px;
            transition: all 0.18s;
        }
        .header-link:hover {
            background: rgba(255,255,255,0.15);
            color: white;
        }

        /* MAIN */
        .main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .wrapper { width: 100%; max-width: 620px; }

        /* HERO */
        .hero {
            text-align: center;
            margin-bottom: 28px;
        }
        .hero-icon {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            box-shadow: 0 8px 24px rgba(232,39,42,0.3);
            font-size: 28px;
        }
        .hero h1 {
            font-size: 26px;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 6px;
            letter-spacing: -0.5px;
        }
        .hero p { color: var(--muted); font-size: 14px; }

        /* CARD */
        .card {
            background: var(--white);
            border-radius: 16px;
            padding: 32px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        label {
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
            color: var(--dark);
            font-size: 13px;
        }

        .input-wrapper {
            position: relative;
            margin-bottom: 14px;
        }
        .input-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: 14px;
        }
        input[type=text] {
            width: 100%;
            padding: 13px 14px 13px 42px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            color: var(--dark);
            transition: all 0.18s;
            background: var(--white);
        }
        input[type=text]:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(232,39,42,0.08);
        }
        input[type=text]::placeholder { color: #CBD5E1; }

        .btn-buscar {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            transition: all 0.18s;
            box-shadow: 0 4px 12px rgba(232,39,42,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-buscar:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(232,39,42,0.4);
        }

        /* ESTADOS */
        .loading {
            text-align: center;
            padding: 20px;
            color: var(--muted);
            display: none;
            font-size: 14px;
        }
        .loading.visible { display: block; }

        .error-box {
            background: #FFF5F5;
            color: #9B1C1C;
            padding: 14px 16px;
            border-radius: 10px;
            margin-top: 16px;
            display: none;
            border: 1px solid #FED7D7;
            border-left: 4px solid var(--primary);
            font-size: 14px;
            font-weight: 500;
        }
        .error-box.visible { display: flex; align-items: center; gap: 10px; }

        /* RESULTADO */
        .resultado { margin-top: 24px; display: none; }
        .resultado.visible { display: block; }

        .resultado-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 16px;
        }
        .resultado-codigo {
            font-size: 15px;
            font-weight: 800;
            color: var(--primary);
            font-family: monospace;
            letter-spacing: 0.5px;
        }

        .badge {
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .badge-clasificado     { background:#EDE9FE; color:#5B21B6; }
        .badge-en_espera       { background:#FEF3C7; color:#92400E; }
        .badge-despachado      { background:#D1FAE5; color:#065F46; }
        .badge-daniado         { background:#FEE2E2; color:#991B1B; }
        .badge-tiempo_excedido { background:#F3E8FF; color:#6B21A8; }
        .badge-recibido        { background:#DBEAFE; color:#1D4ED8; }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .info-item {
            background: var(--surface);
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid var(--border);
        }
        .info-label {
            font-size: 10px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 600;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .info-valor {
            font-weight: 700;
            color: var(--dark);
            font-size: 14px;
        }

        /* FOOTER */
        .footer {
            text-align: center;
            padding: 20px;
            color: var(--muted);
            font-size: 12px;
            border-top: 1px solid var(--border);
            background: var(--white);
        }

        @media (max-width: 480px) {
            .info-grid { grid-template-columns: 1fr; }
            .header { padding: 0 16px; }
            .card { padding: 20px; }
            .hero h1 { font-size: 22px; }
        }
    </style>
</head>
<body>

<div class="header">
    <div class="header-brand">
        <div class="header-logo">
            <i class="fas fa-truck-fast" style="color:white"></i>
        </div>
        <div>
            <div class="header-title">Shalom</div>
            <div class="header-sub">Sistema de Gestión de Almacén</div>
        </div>
    </div>
    <a href="{{ route('login') }}" class="header-link">
        <i class="fas fa-sign-in-alt"></i> Ir al sistema
    </a>
</div>

<div class="main">
    <div class="wrapper">

        <div class="hero">
            <div class="hero-icon">
                <i class="fas fa-search-location" style="color:white"></i>
            </div>
            <h1>Rastrea tu Encomienda</h1>
            <p>Ingresa tu código de seguimiento para conocer el estado de tu paquete</p>
        </div>

        <div class="card">
            <label>
                <i class="fas fa-barcode" style="color:var(--primary)"></i>
                Código de Seguimiento
            </label>
            <div class="input-wrapper">
                <i class="fas fa-search"></i>
                <input type="text" id="codigo"
                       placeholder="Ej: SHL-2026-06-000001"
                       style="text-transform:uppercase">
            </div>
            <button class="btn-buscar" onclick="buscarEncomienda()">
                <i class="fas fa-search"></i> Consultar Estado
            </button>

            <div class="loading" id="loading">
                <i class="fas fa-spinner fa-spin" style="margin-right:6px"></i>
                Buscando encomienda...
            </div>

            <div class="error-box" id="error">
                <i class="fas fa-exclamation-circle" style="font-size:16px"></i>
                <span id="error-text"></span>
            </div>

            <div class="resultado" id="resultado">
                <hr style="border:none; border-top:1px solid var(--border); margin:20px 0">

                <div class="resultado-header">
                    <div>
                        <p style="font-size:11px; color:var(--muted); text-transform:uppercase;
                                  letter-spacing:0.5px; margin-bottom:4px">Encomienda encontrada</p>
                        <span class="resultado-codigo" id="res-codigo"></span>
                    </div>
                    <span id="res-estado" class="badge"></span>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-user"></i> Remitente
                        </div>
                        <div class="info-valor" id="res-remitente"></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-user-check"></i> Destinatario
                        </div>
                        <div class="info-valor" id="res-destinatario"></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-map-marker-alt"></i> Ciudad Destino
                        </div>
                        <div class="info-valor" id="res-ciudad"></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-weight-hanging"></i> Peso
                        </div>
                        <div class="info-valor" id="res-peso"></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-warehouse"></i> Zona en Almacén
                        </div>
                        <div class="info-valor" id="res-zona"></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-calendar-alt"></i> Fecha de Ingreso
                        </div>
                        <div class="info-valor" id="res-fecha"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="footer">
    <i class="fas fa-truck-fast" style="color:var(--primary)"></i>
    Sistema de Gestión de Almacén Shalom — TECSUP III Ciclo 2026 — Grupo E
</div>

<script>
async function buscarEncomienda() {
    const codigo    = document.getElementById('codigo').value.trim().toUpperCase();
    const loading   = document.getElementById('loading');
    const error     = document.getElementById('error');
    const errorText = document.getElementById('error-text');
    const resultado = document.getElementById('resultado');

    error.classList.remove('visible');
    resultado.classList.remove('visible');

    if (!codigo) {
        errorText.textContent = 'Por favor ingresa un código de seguimiento.';
        error.classList.add('visible');
        return;
    }

    loading.classList.add('visible');

    try {
        const response = await fetch(`/api/encomienda/${codigo}`);
        const data     = await response.json();
        loading.classList.remove('visible');

        if (!data.success) {
            errorText.textContent = data.mensaje || 'Encomienda no encontrada.';
            error.classList.add('visible');
            return;
        }

        document.getElementById('res-codigo').textContent       = data.codigo;
        document.getElementById('res-remitente').textContent    = data.remitente;
        document.getElementById('res-destinatario').textContent = data.destinatario;
        document.getElementById('res-ciudad').textContent       = data.ciudad_destino;
        document.getElementById('res-peso').textContent         = data.peso + ' kg';
        document.getElementById('res-zona').textContent         = data.zona;
        document.getElementById('res-fecha').textContent        = new Date(data.fecha_ingreso).toLocaleString('es-PE');

        const estadoLabels = {
            'recibido':        'RECIBIDO',
            'clasificado':     'CLASIFICADO',
            'en_espera':       'EN ESPERA',
            'despachado':      'DESPACHADO',
            'daniado':         'DAÑADO',
            'tiempo_excedido': 'TIEMPO EXCEDIDO',
        };

        const estadoBadge       = document.getElementById('res-estado');
        estadoBadge.textContent = estadoLabels[data.estado] || data.estado.replace('_', ' ').toUpperCase();
        estadoBadge.className   = 'badge badge-' + data.estado;

        resultado.classList.add('visible');

    } catch (err) {
        loading.classList.remove('visible');
        errorText.textContent = 'Error al conectar con el servidor.';
        error.classList.add('visible');
    }
}

document.getElementById('codigo').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') buscarEncomienda();
});
</script>

</body>
</html>