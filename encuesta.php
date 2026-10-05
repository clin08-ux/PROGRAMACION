<?php
require_once __DIR__ . '/config.php';

$surveyError = '';

// Opciones de respuesta para las preguntas de la encuesta.
$surveyQuestions = array(
	'career' => array('label' => '¿De qué carrera eres?', 'options' => array('TICS', 'Administración', 'Logística', 'Otra')),
	'shift' => array('label' => '¿De qué turno eres?', 'options' => array('Matutino', 'Vespertino')),
	'teachers_opinion' => array('label' => '¿Qué piensas sobre tus maestros? ¿Crees que enseñan correctamente su profesión?', 'options' => array('Sí', 'No', 'Un poco', 'Para nada')),
	'improve' => array('label' => '¿Qué mejorarías de la universidad?', 'options' => array('Internet', 'Infraestructura', 'Personal docente y administrativo', 'Cafetería')),
	'dislike' => array('label' => '¿Qué no te agrada de la universidad?', 'options' => array('Internet', 'Docentes y administrativos', 'Baños', 'Otra')),
	'likes_career' => array('label' => '¿Te gusta estar en la carrera en la que te encuentras?', 'options' => array('Sí', 'No', 'Quería otra carrera'))
);
$surveyReady = true;
foreach ($surveyQuestions as $question) {
	if (count($question['options']) === 0) {
		$surveyReady = false;
	}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	try {
		verifyCsrfToken();
		if (!$surveyReady) {
			throw new RuntimeException('La encuesta estará disponible cuando agreguemos las opciones de respuesta.');
		}

		$answers = array();
		foreach ($surveyQuestions as $field => $question) {
			$answer = trim(isset($_POST[$field]) ? $_POST[$field] : '');
			if (!in_array($answer, $question['options'], true)) {
				throw new RuntimeException('Selecciona una respuesta válida en cada pregunta de opción múltiple.');
			}
			$answers[] = $answer;
		}

		$criticism = trim(isset($_POST['constructive_criticism']) ? $_POST['constructive_criticism'] : '');
		if ($criticism === '' || strlen($criticism) > 5000) {
			throw new RuntimeException('Escribe una crítica constructiva de hasta 5000 caracteres.');
		}

		$statement = $db->prepare('INSERT INTO upvm_survey_responses (career, shift, teachers_opinion, improve, dislike, likes_career, constructive_criticism) VALUES (?, ?, ?, ?, ?, ?, ?)');
		if (!$statement) {
			throw new RuntimeException('No se pudo preparar el guardado de la encuesta.');
		}
		$career = $answers[0];
		$shift = $answers[1];
		$teachersOpinion = $answers[2];
		$improve = $answers[3];
		$dislike = $answers[4];
		$likesCareer = $answers[5];
		$statement->bind_param('sssssss', $career, $shift, $teachersOpinion, $improve, $dislike, $likesCareer, $criticism);
		if (!$statement->execute()) {
			$statement->close();
			throw new RuntimeException('No se pudo guardar tu respuesta. Inténtalo de nuevo.');
		}
		$statement->close();

		$surpriseImages = array('foto10.jpeg', 'foto11.jpeg', 'foto13.jpeg');
		$surpriseQuotes = array(
			'TIENES QUE TENER CONFIANZA, PORQUE SI NO CONFIAS, NO HAY CONFIANZA',
			'LUIS MIGUEL ESTA ORGULLOSO DE TI, Y YO TAMBIÉN',
			'OTAKUS O EMOS?',
			'CANTEMOS UNA CANCION DE EZPINOZA PAZ',
			'ME GUSTA MONTERREY O LA DE MONTERREY...',
			'SABES QUE PASA SI UNA GOMITA TOMA? HACE GOMITAS JAJAAJAJAJAJAJAAJ',
			'GRACIAS PRECIOSA POR RESPONDER LA ENCUESTA, TE QUIERO MUCHO',
		
		);
		$_SESSION['survey_surprise'] = array(
			'image' => $surpriseImages[array_rand($surpriseImages)],
			'quote' => $surpriseQuotes[array_rand($surpriseQuotes)]
		);
		header('Location: sorpresa.php');
		exit;
	} catch (RuntimeException $exception) {
		$surveyError = $exception->getMessage();
	}
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Encuesta UPVM de Dani Daniel</title>
	<style>
		body{font-family:Arial,sans-serif;background:#f2f5f4;color:#20302d;margin:0;padding:32px 16px}
		main{max-width:900px;margin:auto;background:#fff;padding:28px;border-radius:8px}
		a.button,button{font:inherit;background:#176b58;color:#fff;border:0;border-radius:4px;padding:11px 16px;text-decoration:none;cursor:pointer}
		a.button.secondary{background:#e5efec;color:#176b58}
		.survey{margin:0 auto 12px;max-width:760px}
		.survey-heading{padding:30px;background:#176b58;color:#fff;border-radius:6px;position:relative;overflow:hidden}
		.survey-heading:after{content:"";position:absolute;width:180px;height:180px;border:1px solid rgba(255,255,255,.24);border-radius:50%;right:-45px;top:-78px}
		.survey-kicker{font-size:12px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;color:#d5eadf}
		.survey-heading h1{font-size:30px;line-height:1.15;margin:10px 0;color:#fff}
		.survey-heading p{max-width:520px;margin:0;color:#e5f0eb;line-height:1.6}
		.survey-progress{margin:24px 0 18px;display:flex;justify-content:space-between;gap:12px;font-size:14px;color:#52645e}
		.progress-track{height:5px;background:#e8efec;border-radius:5px;overflow:hidden;margin-bottom:26px}
		.progress-value{height:100%;width:0;background:#de8c54;transition:width .2s ease}
		.survey-question{border:0;border-bottom:1px solid #e0e8e5;padding:20px 0 22px;margin:0;min-width:0}
		.survey-question legend{font-weight:bold;line-height:1.5;padding:0 0 12px;color:#263a34}
		.survey-options{display:flex;gap:9px;flex-wrap:wrap}
		.survey-option{display:inline-flex;align-items:center;gap:8px;padding:10px 13px;border:1px solid #ccd9d4;border-radius:4px;font-weight:normal;margin:0;cursor:pointer}
		.survey-option:has(input:checked){border-color:#176b58;background:#e9f3ef;color:#145744}
		.survey-option input{accent-color:#176b58;margin:0}
		.options-pending{font-size:14px;color:#786247;background:#f8f1e8;padding:10px 12px;border-left:3px solid #de8c54}
		.survey label[for=constructive_criticism]{display:block;font-weight:bold;margin:20px 0 7px}
		.survey textarea{box-sizing:border-box;width:100%;min-height:130px;padding:12px;border:1px solid #bdcbc7;border-radius:4px;font:inherit;resize:vertical}
		.actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:22px}
		.survey-submit:disabled{background:#aab8b3;cursor:not-allowed}
		.survey-notice{padding:13px 15px;border-radius:4px;line-height:1.5}.survey-error{background:#fde9e7;color:#8a2d25}
		@media(max-width:600px){body{padding:16px 10px}main{padding:20px 16px}.survey-heading{padding:24px 20px}.survey-heading h1{font-size:26px}}
		@media(prefers-reduced-motion:reduce){.progress-value{transition:none}}
	</style>
</head>
<body>
<main>
	<section class="survey" aria-labelledby="survey-title">
		<header class="survey-heading">
			<span class="survey-kicker">Tu voz construye comunidad</span>
			<h1 id="survey-title">Encuesta UPVM de Dani Daniel</h1>
			<p>Comparte tu experiencia en la universidad. Tus respuestas nos ayudan a imaginar mejoras.</p>
		</header>
		<div class="survey-progress" aria-live="polite"><span id="survey-progress-label">0 de 7 respuestas</span><span>Encuesta estudiantil</span></div>
		<div class="progress-track" aria-hidden="true"><div class="progress-value" id="survey-progress-value"></div></div>
		<?php if ($surveyError !== ''): ?><p class="survey-notice survey-error" role="alert"><?php echo e($surveyError); ?></p><?php endif; ?>
		<form id="survey-form" method="post">
			<?php echo csrfField(); ?>
			<?php foreach ($surveyQuestions as $field => $question): ?>
				<fieldset class="survey-question">
					<legend><?php echo e($question['label']); ?></legend>
					<?php if (count($question['options']) > 0): ?>
						<div class="survey-options">
						<?php foreach ($question['options'] as $index => $option): ?>
							<label class="survey-option"><input type="radio" name="<?php echo e($field); ?>" value="<?php echo e($option); ?>" <?php echo $index === 0 ? 'required' : ''; ?>><?php echo e($option); ?></label>
						<?php endforeach; ?>
						</div>
					<?php else: ?>
						<p class="options-pending">Opciones de respuesta pendientes; aquí agregaremos las respuestas que nos compartas.</p>
					<?php endif; ?>
			</fieldset>
			<?php endforeach; ?>
			<label for="constructive_criticism">Agrega una crítica constructiva sobre la UPVM</label>
			<textarea id="constructive_criticism" name="constructive_criticism" maxlength="5000" required placeholder="Escribe tu propuesta o comentario..."></textarea>
			<div class="actions survey-submit">
				<button type="submit" <?php echo $surveyReady ? '' : 'disabled'; ?>>Enviar encuesta</button>
				<a class="button secondary" href="index.php">Volver a la página principal</a>
			</div>
		</form>
	</section>
</main>
<script>
(() => {
	const form = document.getElementById('survey-form');
	const label = document.getElementById('survey-progress-label');
	const value = document.getElementById('survey-progress-value');
	if (!form || !label || !value) return;
	const updateProgress = () => {
		const answered = form.querySelectorAll('input[type="radio"]:checked').length + (form.querySelector('textarea').value.trim() ? 1 : 0);
		label.textContent = answered + ' de 7 respuestas';
		value.style.width = (answered / 7 * 100) + '%';
	};
	form.addEventListener('change', updateProgress);
	form.addEventListener('input', updateProgress);
	updateProgress();
})();
</script>
</body>
</html>