<?php
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $photoData = isset($_POST['photo']) ? (string) $_POST['photo'] : '';
    $caption = isset($_POST['caption']) ? trim((string) $_POST['caption']) : '';

    if ($photoData === '' || !preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,/', $photoData)) {
        echo json_encode(array(
            'success' => false,
            'message' => 'No se recibió una imagen válida.'
        ));
        exit;
    }

    $mimeType = preg_replace('/^data:(image\/[a-zA-Z0-9.+-]+);base64,.*/', '$1', $photoData);
    $extension = 'jpg';
    if ($mimeType === 'image/png') {
        $extension = 'png';
    } elseif ($mimeType === 'image/webp') {
        $extension = 'webp';
    }

    $base64 = preg_replace('/^data:image\/[a-zA-Z0-9.+-]+;base64,/', '', $photoData);
    $binary = base64_decode($base64, true);
    if ($binary === false) {
        echo json_encode(array(
            'success' => false,
            'message' => 'La imagen está corrupta o no se pudo procesar.'
        ));
        exit;
    }

    $directory = __DIR__ . '/uploads/camara';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        echo json_encode(array(
            'success' => false,
            'message' => 'No se pudo crear la carpeta para guardar las fotos.'
        ));
        exit;
    }

    $filename = 'foto_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $path = $directory . '/' . $filename;
    if (file_put_contents($path, $binary) === false) {
        echo json_encode(array(
            'success' => false,
            'message' => 'No se pudo guardar la imagen en el servidor.'
        ));
        exit;
    }

    $relativePath = 'uploads/camara/' . $filename;
    $statement = $db->prepare('INSERT INTO fotos_camara (photo_path, caption) VALUES (?, ?)');
    if ($statement === false) {
        unlink($path);
        echo json_encode(array(
            'success' => false,
            'message' => 'No se pudo preparar la base de datos.'
        ));
        exit;
    }

    $statement->bind_param('ss', $relativePath, $caption);
    if (!$statement->execute()) {
        unlink($path);
        echo json_encode(array(
            'success' => false,
            'message' => 'No se pudo guardar la foto en la base de datos.'
        ));
        exit;
    }

    echo json_encode(array(
        'success' => true,
        'message' => 'Foto guardada correctamente.',
        'image' => $relativePath,
        'caption' => $caption === '' ? 'Foto capturada' : $caption
    ));
    exit;
}

$photos = array();
$result = $db->query('SELECT photo_path, caption, created_at FROM fotos_camara ORDER BY id DESC LIMIT 6');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $photos[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#173b32">
    <title>Foto | UPVM</title>
    <style>
        :root{--forest:#173b32;--leaf:#286653;--paper:#f3f1e9;--ink:#202b27;--muted:#65716b;--rust:#d76c45;--line:#d8d9cf}
        *{box-sizing:border-box}
        body{margin:0;background-color:var(--paper);background-image:repeating-linear-gradient(115deg,rgba(23,59,50,.025) 0,rgba(23,59,50,.025) 1px,transparent 1px,transparent 9px);color:var(--ink);font-family:Georgia,"Times New Roman",serif}
        a{color:inherit}
        .site-header{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:20px clamp(20px,6vw,88px);background:var(--forest);color:#fff}
        .brand{font-size:17px;font-weight:bold;text-decoration:none;letter-spacing:0}
        .header-note{font-family:Arial,sans-serif;font-size:12px;color:#c7d6cf;text-transform:uppercase;letter-spacing:1px}
        main{max-width:1200px;margin:auto;padding:clamp(24px,5vw,64px) clamp(20px,6vw,88px) 56px}
        .page-shell{display:grid;grid-template-columns:1.25fr .75fr;gap:24px}
        .panel{background:rgba(255,255,255,.72);border:1px solid var(--line);padding:28px 26px}
        .eyebrow{margin:0 0 12px;color:var(--leaf);font-family:Arial,sans-serif;font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:1.4px}
        h1{margin:0 0 16px;color:var(--forest);font-size:clamp(38px,4vw,54px);font-weight:normal;line-height:1.1}
        .lead{margin:0 0 26px;color:#4b5851;font-family:Arial,sans-serif;font-size:17px;line-height:1.65}
        .camera-frame{position:relative;display:flex;align-items:center;justify-content:center;min-height:420px;background:#dfe9e5;border:1px solid var(--line);overflow:hidden}
        video,canvas{display:block;width:100%;max-height:420px;background:#0d1715;object-fit:cover}
        .video-hidden{display:none}
        .button-row{display:flex;flex-wrap:wrap;gap:10px;margin-top:18px}
        .primary-button,.secondary-button{display:inline-flex;align-items:center;justify-content:center;padding:14px 18px;border:none;border-radius:0;font-family:Arial,sans-serif;font-size:14px;font-weight:bold;cursor:pointer;text-decoration:none;transition:transform .2s ease,opacity .2s ease}
        .primary-button{background:var(--rust);color:#fff}
        .secondary-button{background:var(--forest);color:#fff}
        .primary-button:hover,.secondary-button:hover{transform:translateY(-2px)}
        .primary-button:disabled{opacity:.7;cursor:wait}
        .status{min-height:24px;margin-top:18px;padding:12px 14px;background:#eef5f2;border:1px solid #cfe2d8;color:var(--forest);font-family:Arial,sans-serif;font-size:14px}
        .mini-card{background:rgba(255,255,255,.72);border:1px solid var(--line);padding:18px 20px}
        .mini-card h2{margin:0 0 14px;color:var(--forest);font-size:24px;font-weight:normal}
        .photo-list{list-style:none;padding:0;margin:0;display:grid;gap:12px}
        .photo-list li{padding:12px;border:1px solid var(--line);background:#f7f5f1}
        .photo-list img{display:block;width:100%;height:160px;object-fit:cover;border:1px solid var(--line);margin-bottom:8px;background:#dfe9e5}
        .photo-list strong{display:block;color:var(--forest);margin-bottom:4px;font-family:Arial,sans-serif}
        .photo-list small{color:var(--muted);font-family:Arial,sans-serif}
        .caption-input{width:100%;margin-top:12px;padding:12px 14px;border:1px solid var(--line);background:#fff;color:var(--ink);font:inherit}
        .site-footer{display:flex;justify-content:space-between;gap:16px;margin-top:44px;border-top:1px solid var(--line);padding-top:16px;color:var(--muted);font-family:Arial,sans-serif;font-size:12px}
        @media(max-width:840px){.page-shell{grid-template-columns:1fr}.site-footer{flex-direction:column}}
        @media(max-width:460px){.site-header{align-items:flex-start;flex-direction:column;gap:7px}.button-row{flex-direction:column}.camera-frame{min-height:320px}}
    </style>
</head>
<body>
    <header class="site-header">
        <a class="brand" href="index.php">UPVM <span aria-hidden="true">/</span> ig: _colngc_</a>
        <span class="header-note">Proyecto escolar · Dani Daniel</span>
    </header>
    <main>
        <div class="page-shell">
            <section class="panel" aria-labelledby="titulo-foto">
                <p class="eyebrow">Foto</p>
                <h1 id="titulo-foto">Toma una foto con la cámara</h1>
                <p class="lead">Da permisos de cámara, toma la fotografía y guárdala automáticamente en la base de datos.</p>

                <div class="camera-frame">
                    <video id="video" autoplay playsinline muted></video>
                    <canvas id="canvas" class="video-hidden"></canvas>
                </div>

                <input id="caption" class="caption-input" type="text" maxlength="255" placeholder="Escribe un título para la foto (opcional)">

                <div class="button-row">
                    <button id="startCamera" class="primary-button" type="button">Activar cámara</button>
                    <button id="capturePhoto" class="secondary-button" type="button" disabled>Tomar foto</button>
                    <a class="secondary-button" href="index.php">Volver al menú</a>
                </div>
                <div id="status" class="status" aria-live="polite">La cámara está lista cuando la actives.</div>
            </section>

            <aside class="mini-card">
                <h2>Últimas fotos</h2>
                <?php if (!empty($photos)): ?>
                    <ul class="photo-list">
                        <?php foreach ($photos as $photo): ?>
                            <li>
                                <img src="<?php echo htmlspecialchars($photo['photo_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($photo['caption'] !== '' ? $photo['caption'] : 'Foto capturada', ENT_QUOTES, 'UTF-8'); ?>">
                                <strong><?php echo htmlspecialchars($photo['caption'] !== '' ? $photo['caption'] : 'Foto capturada', ENT_QUOTES, 'UTF-8'); ?></strong>
                                <small><?php echo htmlspecialchars($photo['created_at'], ENT_QUOTES, 'UTF-8'); ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p style="margin:0;color:var(--muted);font-family:Arial,sans-serif;">Todavía no hay fotos guardadas.</p>
                <?php endif; ?>
            </aside>
        </div>

        <footer class="site-footer">
            <span>UPVM · ig: _colngc_</span>
            <span style="display:inline-block; margin-left:32px; padding-left:22px; border-left:1px solid rgba(23,59,50,0.3);">Creado por Dani Daniel</span>
        </footer>
    </main>

    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const startCameraButton = document.getElementById('startCamera');
        const captureButton = document.getElementById('capturePhoto');
        const statusElement = document.getElementById('status');
        const captionInput = document.getElementById('caption');

        let stream = null;

        startCameraButton.addEventListener('click', async function () {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                statusElement.textContent = 'Tu navegador no soporta acceso a la cámara.';
                return;
            }

            try {
                startCameraButton.disabled = true;
                startCameraButton.textContent = 'Solicitando permiso...';
                statusElement.textContent = 'Pidiendo permiso para usar la cámara...';

                stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user' },
                    audio: false
                });

                video.srcObject = stream;
                await video.play();
                captureButton.disabled = false;
                statusElement.textContent = 'Cámara activa. Toma la foto cuando quieras.';
            } catch (error) {
                statusElement.textContent = 'No se pudo acceder a la cámara. Acepta el permiso e inténtalo de nuevo.';
            } finally {
                startCameraButton.disabled = false;
                startCameraButton.textContent = 'Activar cámara';
            }
        });

        captureButton.addEventListener('click', function () {
            if (!stream) {
                statusElement.textContent = 'Primero activa la cámara.';
                return;
            }

            const context = canvas.getContext('2d');
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            const imageData = canvas.toDataURL('image/jpeg', 0.92);
            const formData = new URLSearchParams();
            formData.append('photo', imageData);
            formData.append('caption', captionInput.value.trim());

            captureButton.disabled = true;
            captureButton.textContent = 'Guardando...';
            statusElement.textContent = 'Guardando la foto en la base de datos...';

            fetch('foto.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: formData.toString()
            })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (data.success) {
                    statusElement.textContent = data.message;
                    captionInput.value = '';
                    setTimeout(function () {
                        window.location.reload();
                    }, 900);
                } else {
                    statusElement.textContent = data.message;
                }
            })
            .catch(function () {
                statusElement.textContent = 'No se pudo guardar la foto. Intenta de nuevo.';
            })
            .finally(function () {
                captureButton.disabled = false;
                captureButton.textContent = 'Tomar foto';
            });
        });
    </script>
</body>
</html>
