<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#173b32">
	<title>Inicio | UPVM</title>
	<style>
		:root{--forest:#173b32;--leaf:#286653;--paper:#f3f1e9;--ink:#202b27;--muted:#65716b;--rust:#d76c45;--line:#d8d9cf}
		*{box-sizing:border-box}
		body{margin:0;background-color:var(--paper);background-image:repeating-linear-gradient(115deg,rgba(23,59,50,.025) 0,rgba(23,59,50,.025) 1px,transparent 1px,transparent 9px);color:var(--ink);font-family:Georgia,"Times New Roman",serif}
		a{color:inherit}
		.site-header{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:20px clamp(20px,6vw,88px);background:var(--forest);color:#fff}
		.brand{font-size:17px;font-weight:bold;text-decoration:none;letter-spacing:0}
		.header-note{font-family:Arial,sans-serif;font-size:12px;color:#c7d6cf;text-transform:uppercase;letter-spacing:1px}
		main{max-width:1300px;margin:auto;padding:clamp(24px,5vw,64px) clamp(20px,6vw,88px) 56px}
		.hero{display:grid;grid-template-columns:1fr .9fr;align-items:center;gap:clamp(34px,7vw,100px);min-height:430px}
		.eyebrow{margin:0 0 20px;color:var(--leaf);font-family:Arial,sans-serif;font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:1.4px}
		h1{max-width:590px;margin:0;color:var(--forest);font-size:clamp(46px,6vw,76px);font-weight:normal;line-height:1.02}
		.intro{max-width:470px;margin:22px 0 28px;color:#4b5851;font-family:Arial,sans-serif;font-size:17px;line-height:1.65}
		.primary-link{display:inline-flex;align-items:center;gap:18px;padding:14px 18px;background:var(--rust);color:#fff;font-family:Arial,sans-serif;font-size:14px;font-weight:bold;text-decoration:none;transition:background .2s ease,transform .2s ease}
		.primary-link:hover{background:#b95132;transform:translateY(-2px)}
		.photo-composition{position:relative;display:grid;grid-template-columns:1fr .72fr;grid-template-rows:230px 155px;gap:12px;min-height:397px}
		.photo-composition figure{position:relative;overflow:hidden;margin:0;background:#d2d7cd}
		.photo-composition img{display:block;width:100%;height:100%;object-fit:cover}
		.photo-main{grid-row:1/3}
		.photo-top img{object-position:center 37%}
		.photo-bottom img{object-position:center 48%}
		.photo-caption{position:absolute;right:0;bottom:0;left:0;padding:28px 12px 10px;background:linear-gradient(transparent,rgba(18,35,29,.7));color:white;font-family:Arial,sans-serif;font-size:11px}
		.access-section{margin-top:clamp(50px,8vw,96px)}
		.section-heading{display:flex;align-items:end;justify-content:space-between;gap:24px;margin-bottom:18px;border-bottom:1px solid var(--line);padding-bottom:14px}
		h2{margin:0;color:var(--forest);font-size:28px;font-weight:normal}
		.section-heading p{margin:0;color:var(--muted);font-family:Arial,sans-serif;font-size:13px}
		.access-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
		.access-item{display:flex;min-height:210px;flex-direction:column;justify-content:space-between;padding:22px;border:1px solid var(--line);background:rgba(255,255,255,.56);text-decoration:none;transition:border-color .2s ease,background .2s ease,transform .2s ease}
		.access-item:hover{transform:translateY(-3px);border-color:var(--leaf);background:#fff}
		.access-top{display:flex;justify-content:space-between;gap:12px;color:var(--leaf);font-family:Arial,sans-serif;font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:1px}
		.access-arrow{color:var(--rust);font-size:22px;line-height:1}
		.access-item h3{margin:24px 0 8px;color:var(--forest);font-size:25px;font-weight:normal}
		.access-item p{max-width:280px;margin:0;color:var(--muted);font-family:Arial,sans-serif;font-size:14px;line-height:1.5}
		.site-footer{display:flex;justify-content:space-between;gap:16px;margin-top:44px;border-top:1px solid var(--line);padding-top:16px;color:var(--muted);font-family:Arial,sans-serif;font-size:12px}
		@media(max-width:760px){.hero{grid-template-columns:1fr;gap:34px}.photo-composition{grid-template-rows:minmax(200px,44vw) minmax(130px,28vw);min-height:auto}.access-grid{grid-template-columns:1fr}.access-item{min-height:170px}.access-section{margin-top:54px}}
		@media(max-width:460px){.site-header{align-items:flex-start;flex-direction:column;gap:7px}.section-heading{align-items:flex-start;flex-direction:column;gap:8px}.photo-composition{grid-template-columns:1fr .72fr;gap:8px}.photo-main{grid-row:1/3}.site-footer{flex-direction:column}}
		@media(prefers-reduced-motion:reduce){.primary-link,.access-item{transition:none}.primary-link:hover,.access-item:hover{transform:none}}
	</style>
</head>
<body>
<header class="site-header">
	<a class="brand" href="index.php">UPVM <span aria-hidden="true">/</span> ig: _colngc_</a>
	<span class="header-note">Proyecto escolar · Dani Daniel</span>
</header>
<main>
	<section class="hero" aria-labelledby="page-title">
		<div class="hero-copy">
			<p class="eyebrow">Bienvenido a la pagina de Colin Colinsito</p>
			<h1 id="page-title">El top global del salon</h1>
			<p class="intro">Este es unos de mis super trabajos de informatico, besos!</p>
			<a class="primary-link" href="#accesos">Explorar herramientas <span aria-hidden="true">&#8595;</span></a>
		</div>
		<div class="photo-composition" aria-label="Galería de fotos">
			<figure class="photo-main"><img src="foto1.jpeg?v=<?php echo filemtime(__DIR__ . '/foto1.jpeg'); ?>" alt="Retrato de Dani"><figcaption class="photo-caption">Un espacio hecho por Dani</figcaption></figure>
			<figure class="photo-top"><img src="foto4.jpeg?v=<?php echo filemtime(__DIR__ . '/foto4.jpeg'); ?>" alt="Foto divertida de Dani"></figure>
			<figure class="photo-bottom"><img src="foto7.jpeg?v=<?php echo filemtime(__DIR__ . '/foto7.jpeg'); ?>" alt="Dani con su gato"></figure>
		</div>
	</section>
	<section class="access-section" id="accesos" aria-labelledby="access-title">
		<div class="section-heading">
			<h2 id="access-title">¿A dónde vamos?</h2>
			<p>Elige una herramienta para continuar.</p>
		</div>
		<div class="access-grid">
			<a class="access-item" href="crud.php">
				<span class="access-top">01 <span class="access-arrow" aria-hidden="true">&#8599;</span></span>
				<span><h3>CRUD de productos</h3><p>Agrega productos y administra sus registros.</p></span>
			</a>
			<a class="access-item" href="maps.php">
				<span class="access-top">02 <span class="access-arrow" aria-hidden="true">&#8599;</span></span>
				<span><h3>Maps</h3><p>Consulta los mapas y ubicaciones disponibles.</p></span>
			</a>
			<a class="access-item" href="encuesta.php">
				<span class="access-top">03 <span class="access-arrow" aria-hidden="true">&#8599;</span></span>
				<span><h3>Encuesta UPVM</h3><p>Comparte tu experiencia y tus propuestas.</p></span>
			</a>
		</div>
	</section>
	<footer class="site-footer">
		<span>UPVM · ig: _colngc_</span>
		<span style="display:inline-block; margin-left:32px; padding-left:22px; border-left:1px solid rgba(23,59,50,0.3);">Creado por Dani Daniel</span>
	</footer>
</main>
</body>
</html>
