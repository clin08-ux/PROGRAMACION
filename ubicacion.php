<?php
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $latitude = isset($_POST['latitude']) ? filter_var($_POST['latitude'], FILTER_VALIDATE_FLOAT) : false;
    $longitude = isset($_POST['longitude']) ? filter_var($_POST['longitude'], FILTER_VALIDATE_FLOAT) : false;
    $address = isset($_POST['address']) ? trim((string) $_POST['address']) : '';

    if ($latitude === false || $longitude === false || $latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
        echo json_encode(array(
            'success' => false,
            'message' => 'La ubicación no es válida. Intenta de nuevo.'
        ));
        exit;
    }

    $address = $address !== '' ? $address : 'Ubicación actual';

    $statement = $db->prepare('INSERT INTO ubicaciones (latitude, longitude, address) VALUES (?, ?, ?)');
    if ($statement === false) {
        echo json_encode(array(
            'success' => false,
            'message' => 'No se pudo preparar la consulta de guardado.'
        ));
        exit;
    }

    $statement->bind_param('dds', $latitude, $longitude, $address);
    if (!$statement->execute()) {
        echo json_encode(array(
            'success' => false,
            'message' => 'No se pudo guardar la ubicación en la base de datos.'
        ));
        exit;
    }

    echo json_encode(array(
        'success' => true,
        'message' => 'Ubicación guardada correctamente.',
        'location' => array(
            'latitude' => number_format($latitude, 8, '.', ''),
            'longitude' => number_format($longitude, 8, '.', ''),
            'address' => $address
        )
    ));
    exit;
}

$ubicaciones = array();
$result = $db->query('SELECT latitude, longitude, address, created_at FROM ubicaciones ORDER BY id DESC LIMIT 5');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $ubicaciones[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#173b32">
    <title>Ubicación | UPVM</title>
    <style>
        :root{--forest:#173b32;--leaf:#286653;--paper:#f3f1e9;--ink:#202b27;--muted:#65716b;--rust:#d76c45;--line:#d8d9cf}
        *{box-sizing:border-box}
        body{margin:0;background-color:var(--paper);background-image:repeating-linear-gradient(115deg,rgba(23,59,50,.025) 0,rgba(23,59,50,.025) 1px,transparent 1px,transparent 9px);color:var(--ink);font-family:Georgia,"Times New Roman",serif}
        a{color:inherit}
        .site-header{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:20px clamp(20px,6vw,88px);background:var(--forest);color:#fff}
        .brand{font-size:17px;font-weight:bold;text-decoration:none;letter-spacing:0}
        .header-note{font-family:Arial,sans-serif;font-size:12px;color:#c7d6cf;text-transform:uppercase;letter-spacing:1px}
        main{max-width:1200px;margin:auto;padding:clamp(24px,5vw,64px) clamp(20px,6vw,88px) 56px}
        .page-shell{display:grid;grid-template-columns:1.2fr .8fr;gap:24px}
        .panel{background:rgba(255,255,255,.72);border:1px solid var(--line);padding:28px 26px}
        .eyebrow{margin:0 0 12px;color:var(--leaf);font-family:Arial,sans-serif;font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:1.4px}
        h1{margin:0 0 16px;color:var(--forest);font-size:clamp(38px,4vw,54px);font-weight:normal;line-height:1.1}
        .lead{margin:0 0 26px;color:#4b5851;font-family:Arial,sans-serif;font-size:17px;line-height:1.65}
        .button-row{display:flex;flex-wrap:wrap;gap:12px;margin-top:18px}
        .primary-button,.secondary-button{display:inline-flex;align-items:center;justify-content:center;padding:14px 18px;border:none;border-radius:0;font-family:Arial,sans-serif;font-size:14px;font-weight:bold;cursor:pointer;text-decoration:none;transition:transform .2s ease,opacity .2s ease;}
        .primary-button{background:var(--rust);color:#fff}
        .secondary-button{background:var(--forest);color:#fff}
        .primary-button:hover,.secondary-button:hover{transform:translateY(-2px)}
        .primary-button:disabled{opacity:.6;cursor:wait}
        .status{min-height:24px;margin-top:18px;padding:12px 14px;background:#eef5f2;border:1px solid #cfe2d8;color:var(--forest);font-family:Arial,sans-serif;font-size:14px}
        .card-stack{display:grid;gap:18px}
        .mini-card{background:rgba(255,255,255,.72);border:1px solid var(--line);padding:18px 20px}
        .mini-card h2{margin:0 0 12px;color:var(--forest);font-size:24px;font-weight:normal}
        .meta{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .meta-box{padding:14px;border:1px solid var(--line);background:#f7f5f1}
        .meta-box span{display:block;color:var(--muted);font-family:Arial,sans-serif;font-size:11px;text-transform:uppercase;letter-spacing:1px}
        .meta-box strong{display:block;margin-top:6px;font-size:18px;color:var(--forest)}
        .list{list-style:none;padding:0;margin:18px 0 0}
        .list li{padding:10px 0;border-top:1px solid var(--line);font-family:Arial,sans-serif}
        .list li:first-child{border-top:none;padding-top:0}
        .list strong{display:block;color:var(--forest);margin-bottom:4px}
        .list small{color:var(--muted)}
        .site-footer{display:flex;justify-content:space-between;gap:16px;margin-top:44px;border-top:1px solid var(--line);padding-top:16px;color:var(--muted);font-family:Arial,sans-serif;font-size:12px}
        @media(max-width:840px){.page-shell{grid-template-columns:1fr}.site-footer{flex-direction:column}}
        @media(max-width:460px){.site-header{align-items:flex-start;flex-direction:column;gap:7px}.button-row{flex-direction:column}.meta{grid-template-columns:1fr}}
    </style>
</head>
<body>
    <header class="site-header">
        <a class="brand" href="index.php">UPVM <span aria-hidden="true">/</span> ig: _colngc_</a>
        <span class="header-note">Proyecto escolar · Dani Daniel</span>
    </header>
    <main>
        <div class="page-shell">
            <section class="panel" aria-labelledby="titulo-ubicacion">
                <p class="eyebrow">Ubicación</p>
                <h1 id="titulo-ubicacion">Guarda tu ubicación actual</h1>
                <p class="lead">Pulsa el botón para obtener tu posición y almacenarla en la base de datos del proyecto.</p>
                <label for="address" style="display:block;margin-bottom:10px;color:var(--forest);font-family:Arial,sans-serif;font-size:13px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;">Nombre o detalle de la ubicación</label>
                <input id="address" type="text" placeholder="Ej. Casa, Universidad, Trabajo..." style="width:100%;padding:12px 14px;border:1px solid var(--line);background:#fff;color:var(--ink);font:inherit;">
                <div class="button-row">
                    <button id="saveLocation" class="primary-button" type="button">Guardar ubicación</button>
                    <a class="secondary-button" href="index.php">Volver al menú</a>
                </div>
                <div id="status" class="status" aria-live="polite">Esperando ubicación...</div>
            </section>
            <aside class="card-stack">
                <div class="mini-card">
                    <h2>Últimas ubicaciones</h2>
                    <?php if (!empty($ubicaciones)): ?>
                        <ul class="list">
                            <?php foreach ($ubicaciones as $ubicacion): ?>
                                <li>
                                    <strong><?php echo htmlspecialchars($ubicacion['address'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <small><?php echo htmlspecialchars($ubicacion['latitude'], ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars($ubicacion['longitude'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <br>
                                    <small><?php echo htmlspecialchars($ubicacion['created_at'], ENT_QUOTES, 'UTF-8'); ?></small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p style="margin:0;color:var(--muted);font-family:Arial,sans-serif;">Aún no hay ubicaciones guardadas.</p>
                    <?php endif; ?>
                </div>
                <div class="mini-card">
                    <h2>Información</h2>
                    <div class="meta">
                        <div class="meta-box">
                            <span>Latitud</span>
                            <strong id="latitudValue">--</strong>
                        </div>
                        <div class="meta-box">
                            <span>Longitud</span>
                            <strong id="longitudValue">--</strong>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
        <footer class="site-footer">
            <span>UPVM · ig: _colngc_</span>
            <span style="display:inline-block; margin-left:32px; padding-left:22px; border-left:1px solid rgba(23,59,50,0.3);">Creado por Dani Daniel</span>
        </footer>
    </main>
    <script>
        const saveLocationButton = document.getElementById('saveLocation');
        const addressInput = document.getElementById('address');
        const statusElement = document.getElementById('status');
        const latitudeValue = document.getElementById('latitudValue');
        const longitudeValue = document.getElementById('longitudValue');

        saveLocationButton.addEventListener('click', function () {
            if (!navigator.geolocation) {
                statusElement.textContent = 'Tu navegador no soporta la geolocalización.';
                return;
            }

            saveLocationButton.disabled = true;
            saveLocationButton.textContent = 'Guardando...';
            statusElement.textContent = 'Solicitando tu ubicación...';

            navigator.geolocation.getCurrentPosition(function (position) {
                const latitude = position.coords.latitude.toFixed(8);
                const longitude = position.coords.longitude.toFixed(8);
                const label = addressInput.value.trim() || 'Ubicación actual';

                latitudeValue.textContent = latitude;
                longitudeValue.textContent = longitude;

                const params = new URLSearchParams({
                    latitude: latitude,
                    longitude: longitude,
                    address: label
                });

                fetch('ubicacion.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    body: params.toString()
                })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    if (data.success) {
                        statusElement.textContent = data.message;
                        setTimeout(function () {
                            window.location.reload();
                        }, 900);
                    } else {
                        statusElement.textContent = data.message;
                    }
                })
                .catch(function () {
                    statusElement.textContent = 'No se pudo guardar la ubicación. Intenta otra vez.';
                })
                .finally(function () {
                    saveLocationButton.disabled = false;
                    saveLocationButton.textContent = 'Guardar ubicación';
                });
            }, function () {
                statusElement.textContent = 'No se pudo obtener la ubicación. Revisa los permisos del navegador.';
                saveLocationButton.disabled = false;
                saveLocationButton.textContent = 'Guardar ubicación';
            }, {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0
            });
        });
    </script>
</body>
</html>
