<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Maps | San Pedro y Museo Soumaya</title>
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
	<style>
		body{font-family:Arial,sans-serif;background:#f2f5f4;color:#20302d;margin:0;padding:32px 16px}
		main{max-width:1100px;margin:auto;background:#fff;padding:28px;border-radius:8px}
		h1,h2{color:#176b58}h1{margin-top:0}.maps-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:24px;margin:24px 0}
		.map-panel{min-width:0}.map-frame,#mapa-soumaya{display:block;box-sizing:border-box;width:100%;height:450px;border:0;background:#e5efec}
		.actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:22px}
		a.button{font:inherit;display:inline-block;background:#176b58;color:#fff;border-radius:4px;padding:11px 16px;text-decoration:none}
		a.button.secondary{background:#e5efec;color:#176b58}
		@media(max-width:700px){body{padding:16px 10px}main{padding:20px 16px}.maps-grid{grid-template-columns:1fr;gap:12px}.map-frame,#mapa-soumaya{height:320px}}
	</style>
</head>
<body>
<main>
	<h1>Google Maps</h1>
	<div class="maps-grid">
		<section class="map-panel" aria-labelledby="san-pedro-title">
			<h2 id="san-pedro-title">San Pedro Garza García, N.L.</h2>
			<iframe
				class="map-frame"
				src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d35483.223806864284!2d-100.38443787279898!3d25.646793220170995!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8662bda0f12f71a9%3A0xe44e63fb073ffae!2sSan%20Pedro%20Garza%20Garc%C3%ADa%2C%20N.L.!5e0!3m2!1ses-419!2smx!4v1790780769155!5m2!1ses-419!2smx"
				allowfullscreen
				loading="lazy"
				referrerpolicy="strict-origin-when-cross-origin"
				 title="Mapa de San Pedro Garza García">
			</iframe>
		</section>
		<section class="map-panel" aria-labelledby="soumaya-title">
			<h2 id="soumaya-title">Museo Soumaya</h2>
			<div id="mapa-soumaya" role="application" aria-label="Mapa interactivo del Museo Soumaya"></div>
		</section>
	</div>
	<div class="actions">
		<a class="button secondary" href="index.php">Página principal</a>
		<a class="button" href="https://www.google.com/maps/search/?api=1&amp;query=Museo+Soumaya" target="_blank" rel="noopener noreferrer">Abrir Museo Soumaya en Google Maps</a>
	</div>
</main>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
	var mapa = L.map('mapa-soumaya').setView([19.4406, -99.2046], 16);

	L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
		attribution: '&copy; OpenStreetMap'
	}).addTo(mapa);

	L.marker([19.4406, -99.2046]).addTo(mapa)
		.bindPopup('Museo Soumaya')
		.openPopup();
</script>
</body>
</html>