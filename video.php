<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#173b32">
	<title>Video | UPVM</title>
	<style>
		:root{--forest:#173b32;--leaf:#286653;--paper:#f3f1e9;--ink:#202b27;--muted:#65716b;--rust:#d76c45;--line:#d8d9cf}
		*{box-sizing:border-box}
		body{margin:0;background-color:var(--paper);background-image:repeating-linear-gradient(115deg,rgba(23,59,50,.025) 0,rgba(23,59,50,.025) 1px,transparent 1px,transparent 9px);color:var(--ink);font-family:Georgia,"Times New Roman",serif}
		a{color:inherit}
		.site-header{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:20px clamp(20px,6vw,88px);background:var(--forest);color:#fff}
		.brand{font-size:17px;font-weight:bold;text-decoration:none}
		.header-note{font-family:Arial,sans-serif;font-size:12px;color:#c7d6cf;text-transform:uppercase;letter-spacing:1px}
		main{max-width:1100px;margin:auto;padding:clamp(32px,7vw,80px) clamp(20px,6vw,88px)}
		.eyebrow{margin:0 0 14px;color:var(--leaf);font-family:Arial,sans-serif;font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:1.4px}
		h1{margin:0;color:var(--forest);font-size:clamp(42px,6vw,68px);font-weight:normal}
		.intro{margin:14px 0 28px;color:var(--muted);font-family:Arial,sans-serif;font-size:16px;line-height:1.6}
		.video-frame{overflow:hidden;width:100%;aspect-ratio:16/9;background:#15241f;box-shadow:0 12px 36px rgba(23,59,50,.14)}
		.video-frame iframe{display:block;width:100%;height:100%;border:0}
		.actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:24px}
		.button{display:inline-block;padding:12px 16px;background:var(--forest);color:#fff;font-family:Arial,sans-serif;font-size:14px;text-decoration:none;transition:background .2s ease}
		.button:hover{background:var(--leaf)}
		.button.secondary{background:transparent;color:var(--forest);border:1px solid var(--line)}
		.button.secondary:hover{background:#fff}
		@media(max-width:460px){.site-header{align-items:flex-start;flex-direction:column;gap:7px}}
		@media(prefers-reduced-motion:reduce){.button{transition:none}}
	</style>
</head>
<body>
<header class="site-header">
	<a class="brand" href="index.php">UPVM <span aria-hidden="true">/</span> ig: _colngc_</a>
	<span class="header-note">Proyecto escolar · Dani Daniel</span>
</header>
<main>
	<p class="eyebrow">Video seleccionado</p>
	<h1>Video</h1>
	<p class="intro">Reproduce aquí el video de YouTube.</p>
	<div class="video-frame">
		<iframe
			src="https://www.youtube-nocookie.com/embed/CMyu2H56DBk"
			title="Video de YouTube"
			allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
			referrerpolicy="strict-origin-when-cross-origin"
			allowfullscreen>
		</iframe>
	</div>
	<div class="actions">
		<a class="button secondary" href="index.php">Página principal</a>
		<a class="button" href="https://f7kc9fmh-5000.usw3.devtunnels.ms/">Chat</a>
		<a class="button" href="https://www.youtube.com/watch?v=CMyu2H56DBk&amp;list=RDCMyu2H56DBk&amp;start_radio=1" target="_blank" rel="noopener noreferrer">Abrir en YouTube</a>
	</div>
</main>
</body>
</html>
