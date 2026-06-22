<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking — Shalom</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: Arial, sans-serif; background:#f5f5f5; }

        .header {
            background:linear-gradient(135deg, #c0392b, #e74c3c);
            color:white; padding:20px 30px;
            display:flex; align-items:center; gap:15px;
            box-shadow:0 2px 8px rgba(0,0,0,0.2);
        }
        .header-icon { font-size:36px; }
        .header h1 { font-size:22px; font-weight:bold; }
        .header p  { font-size:13px; color:rgba(255,255,255,0.8); margin-top:2px; }

        .container { max-width:680px; margin:40px auto; padding:0 20px; }

        .back-link {
            display:inline-flex; align-items:center; gap:5px;
            margin-bottom:20px; color:#e74c3c;
            text-decoration:none; font-size:14px; font-weight:bold;
        }
        .back-link:hover { text-decoration:underline; }

        .card {
            background:white; border-radius:12px; padding:30px;
            box-shadow:0 4px 15px rgba(0,0,0,0.1);
            border-top:4px solid #e74c3c;
        }

        .card h2 { color:#2c3e50; margin-bottom:20px; font-size:18px; }

        .form-group { margin-bottom:15px; }
        label { font-weight:bold; display:block; margin-bottom:8px; color:#2c3e50; font-size:14px; }

        input[type=text] {
            width:100%; padding:12px 15px;
            border:2px solid #ddd; border-radius:8px;
            font-size:15px; margin-bottom:0;
            transition:border-color 0.2s;
        }
        input[type=text]:focus { border-color:#e74c3c; outline:none; }

        .btn-buscar {
            width:100%; padding:13px;
            background:linear-gradient(135deg, #c0392b, #e74c3c);
            color:white; border:none; border-radius:8px;
            font-size:15px; font-weight:bold; cursor:pointer;
            margin-top:10px; transition:opacity 0.2s;
        }
        .btn-buscar:hover { opacity:0.9; }

        .resultado { margin-top:25px; display:none; }
        .resultado.visible { display:block; }

        .badge {
            padding:5px 14px; border-radius:20px;
            font-size:12px; font-weight:bold; display:inline-block;
        }
        .badge-clasificado     { background:#2980b9; color:white; }
        .badge-en_espera       { background:#f39c12; color:white; }
        .badge-despachado      { background:#27ae60; color:white; }
        .badge-daniado         { background:#e74c3c; color:white; }
        .badge-tiempo_excedido { background:#8e44ad; color:white; }
        .badge-recibido        { background:#17a2b8; color:white; }

        .info-grid {
            display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:15px;
        }
        .info-item {
            background:#fef9f9; padding:12px 15px;
            border-radius:8px; border-left:3px solid #e74c3c;
        }
        .info-item .label { font-size:11px; color:#888; margin-bottom:4px; text-transform:uppercase; }
        .info-item .valor { font-weight:bold; color:#2c3e50; font-size:14px; }

        .error-box {
            background:#fadbd8; color:#c0392b; padding:15px;
            border-radius:8px; margin-top:20px; display:none;
            border-left:4px solid #e74c3c; font-size:14px;
        }
        .error-box.visible { display:block; }

        .loading {
            text-align:center; padding:20px; color:#888;
            display:none; font-size:14px;
        }
        .loading.visible { display:block; }

        .footer {
            text-align:center; margin-top:25px;
            color:#aaa; font-size:12px;
        }

        @media (max-width: 480px) {
            .info-grid { grid-template-columns:1fr; }
            .header { padding:15px 20px; }
            .container { margin:20px auto; }
        }
    </style>
</head>
<body>

<div class="header">
    <div class="header-icon">🚚</div>
    <div>
        <h1>Shalom — Tracking de Encomiendas</h1>
        <p>Consulta el estado de tu paquete ingresando el código de seguimiento</p>
    </div>
</div>

<div class="container">
    <a href="{{ route('login') }}" class="back-link">← Ir al sistema</a>

    <div class="card">
        <h2>📦 Consultar Estado de Encomienda</h2>

        <div class="form-group">
            <label>Código de Seguimiento</label>
            <input type="text" id="codigo"
                   placeholder="Ej: SHL-2026-06-000001"
                   style="text-transform:uppercase">
        </div>
        <button class="btn-buscar" onclick="buscarEncomienda()">🔍 Consultar Estado</button>

        <div class="loading" id="loading">⏳ Buscando encomienda...</div>
        <div class="error-box" id="error"></div>

        <div class="resultado" id="resultado">
            <hr style="border:none; border-top:1px solid #ecf0f1; margin:20px 0">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px">
                <h3 id="res-codigo" style="color:#e74c3c; font-size:16px"></h3>
                <span id="res-estado" class="badge"></span>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <div class="label">Remitente</div>
                    <div class="valor" id="res-remitente"></div>
                </div>
                <div class="info-item">
                    <div class="label">Destinatario</div>
                    <div class="valor" id="res-destinatario"></div>
                </div>
                <div class="info-item">
                    <div class="label">Ciudad Destino</div>
                    <div class="valor" id="res-ciudad"></div>
                </div>
                <div class="info-item">
                    <div class="label">Peso</div>
                    <div class="valor" id="res-peso"></div>
                </div>
                <div class="info-item">
                    <div class="label">Zona en Almacén</div>
                    <div class="valor" id="res-zona"></div>
                </div>
                <div class="info-item">
                    <div class="label">Fecha de Ingreso</div>
                    <div class="valor" id="res-fecha"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        Sistema de Gestión de Almacén Shalom — TECSUP III Ciclo 2026 — Grupo E
    </div>
</div>

<script>
async function buscarEncomienda() {
    const codigo    = document.getElementById('codigo').value.trim().toUpperCase();
    const loading   = document.getElementById('loading');
    const error     = document.getElementById('error');
    const resultado = document.getElementById('resultado');

    error.classList.remove('visible');
    resultado.classList.remove('visible');

    if (!codigo) {
        error.textContent = 'Por favor ingresa un código de seguimiento.';
        error.classList.add('visible');
        return;
    }

    loading.classList.add('visible');

    try {
        const response = await fetch(`/api/encomienda/${codigo}`);
        const data     = await response.json();
        loading.classList.remove('visible');

        if (!data.success) {
            error.textContent = '❌ ' + (data.mensaje || 'Encomienda no encontrada.');
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

        const estadoBadge       = document.getElementById('res-estado');
        estadoBadge.textContent = data.estado.replace('_', ' ').toUpperCase();
        estadoBadge.className   = 'badge badge-' + data.estado;

        resultado.classList.add('visible');

    } catch (err) {
        loading.classList.remove('visible');
        error.textContent = '❌ Error al conectar con el servidor.';
        error.classList.add('visible');
    }
}

document.getElementById('codigo').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') buscarEncomienda();
});
</script>

</body>
</html>