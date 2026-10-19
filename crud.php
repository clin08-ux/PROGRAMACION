<?php
require_once __DIR__ . '/config.php';

// Iniciar sesión si no está iniciada (necesario para el token CSRF)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Funciones auxiliares de seguridad y manejo de datos
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('csrfField')) {
    function csrfField() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return '<input type="hidden" name="csrf_token" value="' . e($_SESSION['csrf_token']) . '">';
    }
}

if (!function_exists('verifyCsrfToken')) {
    function verifyCsrfToken() {
        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            throw new RuntimeException('Token CSRF inválido o expirado. Por favor, recargue la página.');
        }
    }
}

if (!function_exists('getProductInput')) {
    function getProductInput() {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);

        if ($name === '' || $description === '' || $price === false || $stock === false) {
            throw new RuntimeException('Por favor, completa todos los campos correctamente.');
        }
        return [$name, $description, $price, $stock];
    }
}

if (!function_exists('saveUploadedPhoto')) {
    function saveUploadedPhoto($inputName, $required = false) {
        if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] === UPLOAD_ERR_NO_FILE) {
            if ($required) {
                throw new RuntimeException("La foto es obligatoria.");
            }
            return '';
        }
        $file = $_FILES[$inputName];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException("Error al subir la imagen.");
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new RuntimeException("La imagen excede el límite de 5 MB.");
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = ['image/jpeg' => 'jpeg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($allowed[$mime])) {
            throw new RuntimeException("Formato de imagen no válido. Solo se permite JPG, PNG o WEBP.");
        }
        $ext = $allowed[$mime];
        $filename = uniqid('prod_', true) . '.' . $ext;
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $destination = $uploadDir . $filename;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException("No se pudo guardar la imagen en el servidor.");
        }
        return 'uploads/' . $filename;
    }
}

if (!function_exists('removePhotos')) {
    function removePhotos(array $photos) {
        foreach ($photos as $photo) {
            if (!empty($photo)) {
                $path = __DIR__ . '/' . $photo;
                if (file_exists($path)) {
                    @unlink($path);
                }
            }
        }
    }
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$savedPhotos = array();
	try {
		verifyCsrfToken();
		list($name, $description, $price, $stock) = getProductInput();
		$savedPhotos[] = saveUploadedPhoto('photo1', true);
		$savedPhotos[] = saveUploadedPhoto('photo2', true);
		
		$statement = $db->prepare("INSERT INTO products (name, description, price, stock, photo1, photo2, photo3) VALUES (?, ?, ?, ?, ?, ?, '')");
		if (!$statement) {
			throw new RuntimeException('No se pudo preparar el producto.');
		}
		$statement->bind_param('sssiss', $name, $description, $price, $stock, $savedPhotos[0], $savedPhotos[1]);
		if (!$statement->execute()) {
			throw new RuntimeException('No se pudo guardar el producto.');
		}
		$statement->close();
		$message = 'Producto agregado correctamente.';
	} catch (RuntimeException $exception) {
		removePhotos($savedPhotos);
		$error = $exception->getMessage();
	}
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Agregar producto</title>
	<style>
		body{font-family:Arial,sans-serif;background:#f2f5f4;color:#20302d;margin:0;padding:32px 16px}
		main{max-width:900px;margin:auto;background:#fff;padding:28px;border-radius:8px}
		h1{margin-top:0;color:#176b58}label{display:block;font-weight:bold;margin:18px 0 7px}
		input[type=text],textarea,input[type=file]{box-sizing:border-box;width:100%;padding:11px;border:1px solid #bdcbc7;border-radius:4px}
		textarea{min-height:110px;resize:vertical}.actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:22px}
		button,a.button{font:inherit;background:#176b58;color:#fff;border:0;border-radius:4px;padding:11px 16px;text-decoration:none;cursor:pointer}
		a.button{background:#e5efec;color:#176b58}.message{padding:12px;background:#e4f3eb;color:#205c3b}.error{padding:12px;background:#fde9e7;color:#8a2d25}
		.carousel{position:relative;max-width:620px;margin:22px auto 34px;overflow:hidden;aspect-ratio:16/9;background:#20302d;border-radius:6px;color:#fff}
		.slides,.slide{position:absolute;inset:0}.slide{margin:0;opacity:0;visibility:hidden;transition:opacity .45s ease}.slide.is-active{opacity:1;visibility:visible}
		.slide img{display:block;width:100%;height:100%;object-fit:contain}.carousel-control{position:absolute;top:50%;z-index:2;transform:translateY(-50%);width:42px;height:42px;padding:0;border:0;border-radius:50%;background:rgba(20,35,31,.78);color:#fff;font-size:26px;line-height:1;cursor:pointer}
		.carousel-control:hover,.carousel-control:focus-visible{background:#176b58}.carousel-control.previous{left:14px}.carousel-control.next{right:14px}
		.carousel-footer{position:absolute;z-index:2;right:0;bottom:0;left:0;display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:linear-gradient(transparent,rgba(0,0,0,.72))}
		.carousel-dots{display:flex;gap:8px}.carousel-dot{width:10px;height:10px;padding:0;border:1px solid #fff;border-radius:50%;background:transparent;cursor:pointer}.carousel-dot[aria-current=true]{background:#fff}
		.carousel-count{font-size:14px}.product-form{max-width:680px;margin:auto}
		@media(max-width:600px){body{padding:16px 10px}main{padding:20px 16px}.carousel{aspect-ratio:3/2;margin-top:18px}.carousel-control{width:38px;height:38px}}
		@media(prefers-reduced-motion:reduce){.slide{transition:none}}
	</style>
</head>
<body>
<main>
	<section class="carousel" aria-label="Carrusel de fotos">
		<div class="slides" aria-live="polite">
			<figure class="slide is-active"><img src="foto1.jpeg" alt="Foto 1"></figure>
			<figure class="slide"><img src="foto2.jpeg" alt="Foto 2"></figure>
			<figure class="slide"><img src="foto3.jpeg" alt="Foto 3"></figure>
			<figure class="slide"><img src="foto7.jpeg" alt="Foto 7"></figure>
			<figure class="slide"><img src="foto8.jpeg" alt="Foto 8"></figure>
			<figure class="slide"><img src="foto9.jpeg" alt="Foto 9"></figure>
		</div>
		<button class="carousel-control previous" type="button" aria-label="Foto anterior" title="Foto anterior">&#8249;</button>
		<button class="carousel-control next" type="button" aria-label="Foto siguiente" title="Foto siguiente">&#8250;</button>
		<div class="carousel-footer">
			<div class="carousel-dots" aria-label="Elegir foto">
				<button class="carousel-dot" type="button" aria-label="Mostrar foto 1" aria-current="true"></button>
				<button class="carousel-dot" type="button" aria-label="Mostrar foto 2" aria-current="false"></button>
				<button class="carousel-dot" type="button" aria-label="Mostrar foto 3" aria-current="false"></button>
				<button class="carousel-dot" type="button" aria-label="Mostrar foto 7" aria-current="false"></button>
				<button class="carousel-dot" type="button" aria-label="Mostrar foto 8" aria-current="false"></button>
				<button class="carousel-dot" type="button" aria-label="Mostrar foto 9" aria-current="false"></button>
			</div>
			<span class="carousel-count" aria-hidden="true">1 / 3</span>
		</div>
	</section>
	<section class="product-form">
		<h1>Agregar producto</h1>
		<p>Completa los datos y selecciona dos fotos JPG, PNG o WEBP (máximo 5 MB cada una).</p>
		<?php if ($message !== ''): ?><p class="message"><?php echo e($message); ?></p><?php endif; ?>
		<?php if ($error !== ''): ?><p class="error"><?php echo e($error); ?></p><?php endif; ?>
		<form method="post" enctype="multipart/form-data">
		<?php echo csrfField(); ?>
		<label for="name">Nombre</label>
		<input id="name" name="name" type="text" maxlength="150" required>
		<label for="description">Descripción</label>
		<textarea id="description" name="description" required></textarea>
		<label for="price">Precio</label>
		<input id="price" name="price" type="number" min="0" max="99999999.99" step="0.01" required>
		<label for="stock">Existencias</label>
		<input id="stock" name="stock" type="number" min="0" step="1" required>
		<label for="photo1">Foto 1</label>
		<input id="photo1" name="photo1" type="file" accept="image/jpeg,image/png,image/webp" required>
		<label for="photo2">Foto 2</label>
		<input id="photo2" name="photo2" type="file" accept="image/jpeg,image/png,image/webp" required>
		<div class="actions">
			<button type="submit">Guardar</button>
			<a class="button" href="index.php">Página principal</a>
			<a class="button" href="administrar.php">Modificar o borrar registros</a>
			<a class="button" href="maps.php">Maps</a>
			<a class="button" href="encuesta.php">Encuesta UPVM</a>
			<a class="button" href="https://f7kc9fmh-5000.usw3.devtunnels.ms/">Chat</a>
		</div>
		</form>
	</section>
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
