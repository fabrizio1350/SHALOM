<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking — Shalom</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: Arial, sans-serif; background:#f4f4f4; }
        .header {
            background:#1a1a2e; color:white; padding:20px 30px;
            display:flex; align-items:center; gap:15px;
        }
        .header h1 { font-size:24px; }
        .header p  { font-size:14px; color:#aaa; }
        .container { max-width:700px; margin:50px auto; padding:0 20px; }
        .card {
            background:white; border-radius:8px; padding:30px;
            box-shadow:0 2px 8px rgba(0,0,0,0.1);
        }
        .form-group { margin-bottom:20px; }
        label { font-weight:bold; display:block; margin-bottom:8px; }
        input {
            width:100%; padding:12px; border:2px solid #ddd;
            border-radius:4px; font-size:16px; margin-bottom:0;
        }
        input:focus { border-color:#1a1a2e; outline:none; }
        .btn {
            width:100%; padding:12px; background:#1a1a2e; color:white;
            border:none; border-radius:4px; font-size:16px; cursor:pointer;
        }
        .btn:hover { background:#2c2c4e; }
        .resultado { margin-top:25px; display:none; }
        .resultado.visible { display:block; }
        .badge {
            padding:5px 12px; border-radius:12px; font-size:13px;
            font-weight:bold; display:inline-block;
        }
        .badge-clasificado   { background:#007bff; color:white; }
        .badge-en_espera     { background:#ffc107; color:black; }
        .badge-despachado    { background:#28a745; color:white; }
        .badge-daniado       { background:#dc3545; color:white; }
        .badge-tiempo_excedido { background:#6f42c1; color:white; }
        .badge-recibido      { background:#17a2b8; color:white; }
        .info-grid {
            display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-top:20px;
        }
        .info-item { background:#f8f9fa; padding:12px; border-radius:6px; }
        .info-item .label { font-size:12px; color:#666; margin-bottom:4px; }
        .info-item .valor { font-weight:bold; color:#1a1a2e; }
        .error-box {
            background:#f8d7da; color:#721c24; padding:15px;
            border-radius:6px; margin-top:20px; display:none;
        }
        .error-box.visible { display:block; }
        .loading { text-align:center; padding:20px; color:#666; display:none; }
        .loading.visible { display:block; }
        .footer {
            text-align:center; margin-top:30px; color:#999; font-size:13px;
        }
        .back-link {
            display:inline-block; margin-bottom:20px; color:#1a1a2e;
            text-decoration:none; font-size:14px;
        }
        .back-link:hover { text-decoration:underline; }
    </style>
</head>
<body>

<div class="header">
    <div>
        <h1>🚚 Shalom — Tracking de Encomiendas</h1>
        <p>Consulta el estado de tu paquete ingresando el código de seguimiento</p>
    </div>
</div>

<div class="container">
    <a href="{{ route('login') }}" class="back-link">← Ir al sistema</a>

    <div class="card">
        <h2 style="margin-bottom:20px; color:#1a1a2e">📦 Consultar Estado de Encomienda</h2>

        <div class="form-group">
            <label>Código de Seguimiento</label>
            <input type="text" id="codigo" placeholder="Ej: SHL-2026-06-000001"
                   style="text-transform:uppercase">
        </div>
        <button class="btn" onclick="buscarEncomienda()">🔍 Consultar</button>

        <div class="loading" id="loading">⏳ Buscando encomienda...</div>
        <div class="error-box" id="error"></div>

        <div class="resultado" id="resultado">
            <hr style="margin:20px 0">
            <div style="display:flex; justify-content:space-between; align-items:center">
                <h3 id="res-codigo" style="color:#1a1a2e"></h3>
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
        Sistema de Gestión de Almacén Shalom — TECSUP III Ciclo 2026
    </div>
</div>

<script>
async function buscarEncomienda() {
    const codigo   = document.getElementById('codigo').value.trim().toUpperCase();
    const loading  = document.getElementById('loading');
    const error    = document.getElementById('error');
    const resultado = document.getElementById('resultado');

    // Limpiar resultados anteriores
    error.classList.remove('visible');
    resultado.classList.remove('visible');

    if (!codigo) {
        error.textContent = 'Por favor ingresa un código de seguimiento.';
        error.classList.add('visible');
        return;
    }

    // Mostrar loading
    loading.classList.add('visible');

    try {
        // Consumir la API REST
        const response = await fetch(`/api/encomienda/${codigo}`);
        const data     = await response.json();

        loading.classList.remove('visible');

        if (!data.success) {
            error.textContent = data.mensaje || 'Encomienda no encontrada.';
            error.classList.add('visible');
            return;
        }

        // Mostrar resultados
        document.getElementById('res-codigo').textContent      = data.codigo;
        document.getElementById('res-remitente').textContent   = data.remitente;
        document.getElementById('res-destinatario').textContent = data.destinatario;
        document.getElementById('res-ciudad').textContent      = data.ciudad_destino;
        document.getElementById('res-peso').textContent        = data.peso + ' kg';
        document.getElementById('res-zona').textContent        = data.zona;
        document.getElementById('res-fecha').textContent       = new Date(data.fecha_ingreso).toLocaleString('es-PE');

        // Badge de estado
        const estadoBadge = document.getElementById('res-estado');
        estadoBadge.textContent  = data.estado.replace('_', ' ').toUpperCase();
        estadoBadge.className    = 'badge badge-' + data.estado;

        resultado.classList.add('visible');

    } catch (err) {
        loading.classList.remove('visible');
        error.textContent = 'Error al conectar con el servidor.';
        error.classList.add('visible');
    }
}

// Permitir buscar con Enter
document.getElementById('codigo').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') buscarEncomienda();
});
</script>

</body>
</html>