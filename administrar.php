<?php
require_once __DIR__ . '/config.php';

$message = '';
$error = '';

function saveCarouselPhoto($field, $filename)
{
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return false;
    }

    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No se pudo recibir ' . $filename . '. Inténtalo de nuevo.');
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('Cada foto del carrusel debe pesar 5 MB o menos.');
    }

    $imageInfo = @getimagesize($file['tmp_name']);
    if (!$imageInfo || $imageInfo['mime'] !== 'image/jpeg') {
        throw new RuntimeException('Las fotos del carrusel deben ser imágenes JPG o JPEG.');
    }

    if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/' . $filename)) {
        throw new RuntimeException('No se pudo guardar ' . $filename . '. Revisa los permisos de htdocs.');
    }

    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPhotos = array();

    try {
        verifyCsrfToken();
        $action = isset($_POST['action']) ? $_POST['action'] : '';
        if ($action === 'upload-carousel') {
            $uploaded = false;
            foreach (array('carousel4' => 'foto4.jpeg', 'carousel5' => 'foto5.jpeg', 'carousel6' => 'foto6.jpeg') as $field => $filename) {
                $uploaded = saveCarouselPhoto($field, $filename) || $uploaded;
            }
            if (!$uploaded) {
                throw new RuntimeException('Selecciona al menos una foto para reemplazar.');
            }
            $message = 'Fotos del carrusel actualizadas correctamente.';
        } else {
            $id = filter_var(isset($_POST['id']) ? $_POST['id'] : null, FILTER_VALIDATE_INT);
            if (!$id || $id < 1) {
                throw new RuntimeException('El producto seleccionado no es válido.');
            }

            if ($action === 'delete') {
            $find = $db->prepare('SELECT photo1, photo2, photo3 FROM products WHERE id = ?');
            $find->bind_param('i', $id);
            $find->execute();
            $find->bind_result($photo1, $photo2, $photo3);
            if (!$find->fetch()) {
                throw new RuntimeException('El producto ya no existe.');
            }
            $find->close();

            $delete = $db->prepare('DELETE FROM products WHERE id = ?');
            $delete->bind_param('i', $id);
            if (!$delete->execute()) {
                throw new RuntimeException('No se pudo borrar el producto.');
            }
            $delete->close();
            removePhotos(array($photo1, $photo2, $photo3));
            $message = 'Producto borrado correctamente.';
            } elseif ($action === 'update') {
            list($name, $description, $price, $stock) = getProductInput();

            $find = $db->prepare('SELECT photo1, photo2, photo3 FROM products WHERE id = ?');
            $find->bind_param('i', $id);
            $find->execute();
            $find->bind_result($oldPhoto1, $oldPhoto2, $oldPhoto3);
            if (!$find->fetch()) {
                throw new RuntimeException('El producto ya no existe.');
            }
            $find->close();

            $oldPhotos = array($oldPhoto1, $oldPhoto2, $oldPhoto3);
            $photo1 = saveUploadedPhoto('photo1', false);
            if ($photo1) { $newPhotos[] = $photo1; } else { $photo1 = $oldPhotos[0]; }
            $photo2 = saveUploadedPhoto('photo2', false);
            if ($photo2) { $newPhotos[] = $photo2; } else { $photo2 = $oldPhotos[1]; }
            $photo3 = saveUploadedPhoto('photo3', false);
            if ($photo3) { $newPhotos[] = $photo3; } else { $photo3 = $oldPhotos[2]; }

            $update = $db->prepare('UPDATE products SET name = ?, description = ?, price = ?, stock = ?, photo1 = ?, photo2 = ?, photo3 = ? WHERE id = ?');
            if (!$update) {
                throw new RuntimeException('No se pudo preparar la modificación del producto.');
            }
            $update->bind_param('sssisssi', $name, $description, $price, $stock, $photo1, $photo2, $photo3, $id);
            if (!$update->execute()) {
                throw new RuntimeException('No se pudo modificar el producto.');
            }
            $update->close();

            foreach ($oldPhotos as $index => $oldPhoto) {
                if ($oldPhoto !== array($photo1, $photo2, $photo3)[$index]) {
                    removePhotos(array($oldPhoto));
                }
            }
            $newPhotos = array();
            $message = 'Producto modificado correctamente.';
            } else {
                throw new RuntimeException('La acción solicitada no es válida.');
            }
        }
    } catch (RuntimeException $exception) {
        removePhotos($newPhotos);
        $error = $exception->getMessage();
    }
}

$editingRecord = null;
$editId = filter_var(isset($_GET['edit']) ? $_GET['edit'] : null, FILTER_VALIDATE_INT);
if ($editId && $editId > 0) {
    $edit = $db->prepare('SELECT id, name, description, price, stock, photo1, photo2, photo3 FROM products WHERE id = ?');
    $edit->bind_param('i', $editId);
    $edit->execute();
    $result = $edit->get_result();
    $editingRecord = $result->fetch_assoc();
    $edit->close();
}

$products = $db->query('SELECT id, name, description, price, stock, photo1, photo2, photo3 FROM products ORDER BY id DESC');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Administrar productos</title>
    <style>
        body{font-family:Arial,sans-serif;background:#f2f5f4;color:#20302d;margin:0;padding:32px 16px}
        main{max-width:900px;margin:auto;background:#fff;padding:28px;border-radius:8px}
        h1,h2{color:#176b58}h1{margin-top:0}label{display:block;font-weight:bold;margin:16px 0 7px}
        input[type=text],textarea,input[type=file]{box-sizing:border-box;width:100%;padding:11px;border:1px solid #bdcbc7;border-radius:4px}
        textarea{min-height:100px;resize:vertical}.record{padding:18px 0;border-top:1px solid #dce5e2}
        .photos{display:flex;gap:10px;flex-wrap:wrap;margin:12px 0}.photos img{width:120px;height:100px;object-fit:cover;border-radius:4px;background:#e5efec}
        .carousel{position:relative;max-width:620px;margin:22px auto 34px;overflow:hidden;aspect-ratio:16/9;background:#20302d;border-radius:6px;color:#fff}
        .slides,.slide{position:absolute;inset:0}.slide{margin:0;opacity:0;visibility:hidden;transition:opacity .45s ease}.slide.is-active{opacity:1;visibility:visible}
        .slide img{display:block;width:100%;height:100%;object-fit:contain}.carousel-control{position:absolute;top:50%;z-index:2;transform:translateY(-50%);width:42px;height:42px;padding:0;border:0;border-radius:50%;background:rgba(20,35,31,.78);color:#fff;font-size:26px;line-height:1;cursor:pointer}
        .carousel-control:hover,.carousel-control:focus-visible{background:#176b58}.carousel-control.previous{left:14px}.carousel-control.next{right:14px}
        .carousel-footer{position:absolute;z-index:2;right:0;bottom:0;left:0;display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:linear-gradient(transparent,rgba(0,0,0,.72))}
        .carousel-dots{display:flex;gap:8px}.carousel-dot{width:10px;height:10px;padding:0;border:1px solid #fff;border-radius:50%;background:transparent;cursor:pointer}.carousel-dot[aria-current=true]{background:#fff}
        .carousel-count{font-size:14px}
        .slide-missing{display:flex;width:100%;height:100%;flex-direction:column;align-items:center;justify-content:center;gap:12px;color:#fff;font-size:16px}
        .slide-missing a{padding:9px 13px;border-radius:4px;background:#176b58;color:#fff;text-decoration:none;font-size:14px}
        .actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-top:14px}
        button,a.button{font:inherit;background:#176b58;color:#fff;border:0;border-radius:4px;padding:10px 14px;text-decoration:none;cursor:pointer}
        button.delete{background:#a7372d}a.button{background:#e5efec;color:#176b58}.message{padding:12px;background:#e4f3eb;color:#205c3b}.error{padding:12px;background:#fde9e7;color:#8a2d25}
        .muted{color:#65736f}.edit-form{padding:18px 0 24px;border-bottom:2px solid #dce5e2}
        @media(max-width:600px){body{padding:16px 10px}main{padding:20px 16px}.carousel{aspect-ratio:3/2;margin-top:18px}.carousel-control{width:38px;height:38px}}
        @media(prefers-reduced-motion:reduce){.slide{transition:none}}
    </style>
</head>
<body>
<main>
    <h1>Administrar productos</h1>
    <?php if ($message !== ''): ?><p class="message"><?php echo e($message); ?></p><?php endif; ?>
    <?php if ($error !== ''): ?><p class="error"><?php echo e($error); ?></p><?php endif; ?>

    <section class="carousel" aria-label="Carrusel de fotos">
        <div class="slides" aria-live="polite">
            <figure class="slide is-active"><img src="./foto4.jpeg?v=<?php echo e((string) @filemtime(__DIR__ . '/foto4.jpeg')); ?>" alt="Foto 4"></figure>
            <figure class="slide"><img src="./foto5.jpeg?v=<?php echo e((string) @filemtime(__DIR__ . '/foto5.jpeg')); ?>" alt="Foto 5"></figure>
            <figure class="slide"><img src="./foto6.jpeg?v=<?php echo e((string) @filemtime(__DIR__ . '/foto6.jpeg')); ?>" alt="Foto 6"></figure>
        </div>
        <button class="carousel-control previous" type="button" aria-label="Foto anterior" title="Foto anterior">&#8249;</button>
        <button class="carousel-control next" type="button" aria-label="Foto siguiente" title="Foto siguiente">&#8250;</button>
        <div class="carousel-footer">
            <div class="carousel-dots" aria-label="Elegir foto">
                <button class="carousel-dot" type="button" aria-label="Mostrar foto 4" aria-current="true"></button>
                <button class="carousel-dot" type="button" aria-label="Mostrar foto 5" aria-current="false"></button>
                <button class="carousel-dot" type="button" aria-label="Mostrar foto 6" aria-current="false"></button>
            </div>
            <span class="carousel-count" aria-hidden="true">1 / 3</span>
        </div>
    </section>

    <?php if ($editingRecord): ?>
        <section class="edit-form">
            <h2>Modificar producto #<?php echo e($editingRecord['id']); ?></h2>
            <form method="post" enctype="multipart/form-data">
                <?php echo csrfField(); ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?php echo e($editingRecord['id']); ?>">
                <label for="name">Nombre</label>
                <input id="name" name="name" type="text" maxlength="150" value="<?php echo e($editingRecord['name']); ?>" required>
                <label for="description">Descripción</label>
                <textarea id="description" name="description" required><?php echo e($editingRecord['description']); ?></textarea>
                <label for="price">Precio</label>
                <input id="price" name="price" type="number" min="0" max="99999999.99" step="0.01" value="<?php echo e($editingRecord['price']); ?>" required>
                <label for="stock">Existencias</label>
                <input id="stock" name="stock" type="number" min="0" step="1" value="<?php echo e($editingRecord['stock']); ?>" required>
                <div class="photos">
                    <?php foreach (array('photo1', 'photo2') as $photo): ?>
                        <img src="<?php echo 'uploads/' . rawurlencode($editingRecord[$photo]); ?>" alt="Foto del registro">
                    <?php endforeach; ?>
                </div>
                <?php foreach (array('photo1' => 'Foto 1', 'photo2' => 'Foto 2') as $field => $label): ?>
                    <label for="<?php echo e($field); ?>"><?php echo e($label); ?> (opcional, para reemplazar)</label>
                    <input id="<?php echo e($field); ?>" name="<?php echo e($field); ?>" type="file" accept="image/jpeg,image/png,image/webp">
                <?php endforeach; ?>
                <div class="actions">
                    <button type="submit">Guardar cambios</button>
                    <a class="button" href="administrar.php">Cancelar</a>
                </div>
            </form>
        </section>
    <?php endif; ?>

    <h2>Productos guardados</h2>
    <?php if ($products && $products->num_rows > 0): ?>
        <?php while ($record = $products->fetch_assoc()): ?>
            <article class="record">
                <h3><?php echo e($record['name']); ?></h3>
                <p><?php echo nl2br(e($record['description'])); ?></p>
                <p><strong>Precio:</strong> $<?php echo number_format((float) $record['price'], 2); ?> | <strong>Existencias:</strong> <?php echo e($record['stock']); ?></p>
                <div class="photos">
                    <?php foreach (array('photo1', 'photo2') as $photo): ?>
                        <img src="<?php echo 'uploads/' . rawurlencode($record[$photo]); ?>" alt="Foto del registro">
                    <?php endforeach; ?>
                </div>
                <div class="actions">
                    <a class="button" href="administrar.php?edit=<?php echo e($record['id']); ?>">Modificar</a>
                    <form method="post" onsubmit="return confirm('¿Seguro que quieres borrar este registro?');">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo e($record['id']); ?>">
                        <button class="delete" type="submit">Borrar producto</button>
                    </form>
                </div>
            </article>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="muted">Todavía no hay productos.</p>
    <?php endif; ?>
    <p><a class="button" href="crud.php">Agregar un producto</a></p>
</main>
<script>
(() => {
    const carousel = document.querySelector('.carousel');
    const slides = Array.from(carousel.querySelectorAll('.slide'));
    const dots = Array.from(carousel.querySelectorAll('.carousel-dot'));
    const count = carousel.querySelector('.carousel-count');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let activeIndex = 0;
    let timer;

    function showSlide(index) {
        activeIndex = (index + slides.length) % slides.length;
        slides.forEach((slide, slideIndex) => {
            slide.classList.toggle('is-active', slideIndex === activeIndex);
            slide.setAttribute('aria-hidden', slideIndex === activeIndex ? 'false' : 'true');
            dots[slideIndex].setAttribute('aria-current', slideIndex === activeIndex ? 'true' : 'false');
        });
        count.textContent = (activeIndex + 1) + ' / ' + slides.length;
    }

    function startTimer() {
        window.clearInterval(timer);
        if (!reducedMotion) timer = window.setInterval(() => showSlide(activeIndex + 1), 5000);
    }

    carousel.querySelector('.previous').addEventListener('click', () => showSlide(activeIndex - 1));
    carousel.querySelector('.next').addEventListener('click', () => showSlide(activeIndex + 1));
    dots.forEach((dot, index) => dot.addEventListener('click', () => showSlide(index)));
    carousel.addEventListener('mouseenter', () => window.clearInterval(timer));
    carousel.addEventListener('mouseleave', startTimer);
    carousel.addEventListener('focusin', () => window.clearInterval(timer));
    carousel.addEventListener('focusout', event => {
        if (!carousel.contains(event.relatedTarget)) startTimer();
    });
    carousel.addEventListener('keydown', event => {
        if (event.key === 'ArrowLeft') showSlide(activeIndex - 1);
        if (event.key === 'ArrowRight') showSlide(activeIndex + 1);
    });
    showSlide(0);
    startTimer();
})();
</script>
</body>
</html>