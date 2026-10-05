<?php
session_start();

$surprise = isset($_SESSION['survey_surprise']) ? $_SESSION['survey_surprise'] : null;
unset($_SESSION['survey_surprise']);

function escapeSurprise($value)
{
	return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Una sorpresa para ti</title>
	<style>
		:root{color-scheme:light;--ink:#17352d;--green:#176b58;--paper:#f4f3ed;--accent:#e5a65c}
		*{box-sizing:border-box}
		body{min-height:100vh;margin:0;padding:32px;background:var(--paper);color:var(--ink);font-family:Georgia,"Times New Roman",serif;display:grid;place-items:center}
		main{width:min(100%,1040px)}
		.surprise{display:grid;grid-template-columns:1.15fr .85fr;min-height:520px;background:#fff;box-shadow:0 18px 50px rgba(23,53,45,.12);animation:arrive .6s ease both}
		.photo{min-height:520px;background:#17352d}
		.photo img{display:block;width:100%;height:100%;min-height:520px;object-fit:cover}
		.message{display:flex;flex-direction:column;justify-content:center;align-items:flex-start;padding:clamp(28px,6vw,68px)}
		.kicker{margin:0 0 24px;color:var(--green);font-family:Arial,sans-serif;font-size:12px;font-weight:bold;letter-spacing:1.5px;text-transform:uppercase}
		h1{margin:0 0 24px;font-size:clamp(34px,5vw,56px);font-weight:normal;line-height:1.08}
		blockquote{margin:0;border-left:3px solid var(--accent);padding-left:18px;color:#53645c;font-size:21px;line-height:1.5}
		.actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:36px;font-family:Arial,sans-serif}
		a{display:inline-block;padding:12px 15px;background:var(--green);color:#fff;font-size:14px;text-decoration:none}
		a.secondary{background:#e7eee9;color:var(--green)}
		.empty{padding:44px;background:#fff;text-align:center;font-family:Arial,sans-serif}
		.empty h1{font-family:Georgia,"Times New Roman",serif}
		@keyframes arrive{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
		@media(max-width:680px){body{padding:16px}.surprise{grid-template-columns:1fr;min-height:0}.photo,.photo img{min-height:0;height:auto;aspect-ratio:4/3}.message{padding:28px 24px 32px}h1{font-size:38px}.actions{margin-top:28px}}
		@media(prefers-reduced-motion:reduce){.surprise{animation:none}}
	</style>
</head>
<body>
<main>
	<?php if ($surprise !== null): ?>
		<article class="surprise">
			<div class="photo"><img src="<?php echo escapeSurprise($surprise['image']); ?>" alt="Una imagen sorpresa para ti"></div>
			<div class="message">
				<p class="kicker">UPVM</p>
				<h1>GRACIASS BRO POR AYUDARME A RESPONDER</h1>
				<blockquote><?php echo escapeSurprise($surprise['quote']); ?></blockquote>
				<nav class="actions" aria-label="Navegación">
					<a href="index.php" onclick="window.location.href='index.php'; return false;">Página principal</a>
					<a class="secondary" href="encuesta.php" onclick="window.location.href='encuesta.php'; return false;">Volver a la encuesta</a>
				</nav>
			</div>
		</article>
	<?php else: ?>
		<section class="empty">
			<p class="kicker">UPVM</p>
			<h1>La sorpresa te espera al terminar la encuesta.</h1>
			<a href="encuesta.php" onclick="window.location.href='encuesta.php'; return false;">Ir a la encuesta</a>
		</section>
	<?php endif; ?>
</main>
</body>
</html>