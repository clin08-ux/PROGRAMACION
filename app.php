<?php

const MAX_MENSAJES = 200;
const MAX_USUARIO = 30;
const MAX_TEXTO = 500;

function responderJson($datos, $estado = 200)
{
    http_response_code($estado);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function longitudTexto($texto)
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($texto, 'UTF-8');
    }
    preg_match_all('/./us', $texto, $caracteres);
    return count($caracteres[0]);
}

function actualizarAlmacenMensajes($operacion)
{
    $ruta = getenv('CHAT_DATA_FILE');
    if ($ruta === false || trim($ruta) === '') {
        $ruta = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ejercicio-chat-mensajes.json';
    }

    $archivo = @fopen($ruta, 'c+');
    if ($archivo === false) {
        throw new RuntimeException('No se pudo abrir el almacenamiento del chat.');
    }

    try {
        if (!flock($archivo, LOCK_EX)) {
            throw new RuntimeException('No se pudo bloquear el almacenamiento del chat.');
        }

        rewind($archivo);
        $contenido = stream_get_contents($archivo);
        if ($contenido === false) {
            throw new RuntimeException('No se pudo leer el almacenamiento del chat.');
        }
        if ($contenido === '') {
            $almacen = array('siguiente_id' => 1, 'mensajes' => array());
        } else {
            $almacen = json_decode($contenido, true);
            if (json_last_error() !== JSON_ERROR_NONE
                || !is_array($almacen)
                || !isset($almacen['siguiente_id'], $almacen['mensajes'])
                || !is_int($almacen['siguiente_id'])
                || !is_array($almacen['mensajes'])) {
                throw new RuntimeException('El almacenamiento del chat tiene un formato inválido.');
            }
        }

        $resultado = $operacion($almacen);
        $serializado = json_encode($almacen, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($serializado === false) {
            throw new RuntimeException('No se pudo serializar el almacenamiento del chat.');
        }
        rewind($archivo);
        if (!ftruncate($archivo, 0)
            || fwrite($archivo, $serializado) !== strlen($serializado)
            || !fflush($archivo)) {
            throw new RuntimeException('No se pudo guardar el almacenamiento del chat.');
        }
        flock($archivo, LOCK_UN);
        return $resultado;
    } finally {
        fclose($archivo);
    }
}

function metodoNoPermitido($permitido)
{
    header('Allow: ' . $permitido);
    responderJson(array('error' => 'Método no permitido.'), 405);
}

$ruta = isset($_GET['route']) && is_string($_GET['route']) ? $_GET['route'] : '';
if ($ruta === 'api/mensajes') {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $desde = isset($_GET['desde']) ? filter_var($_GET['desde'], FILTER_VALIDATE_INT) : 0;
        if ($desde === false) {
            $desde = 0;
        }
        try {
            $nuevos = actualizarAlmacenMensajes(function (&$almacen) use ($desde) {
                return array_values(array_filter($almacen['mensajes'], function ($mensaje) use ($desde) {
                    return isset($mensaje['id']) && $mensaje['id'] > $desde;
                }));
            });
            responderJson($nuevos);
        } catch (RuntimeException $exception) {
            error_log('Chat message storage failed: ' . $exception->getMessage());
            responderJson(array('error' => 'No se pudieron consultar los mensajes.'), 500);
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $datos = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($datos)) {
            $datos = array();
        }
        $usuario = isset($datos['usuario']) && is_scalar($datos['usuario'])
            ? trim((string) $datos['usuario'])
            : '';
        $texto = isset($datos['texto']) && is_scalar($datos['texto'])
            ? trim((string) $datos['texto'])
            : '';

        if ($usuario === '' || $texto === '') {
            responderJson(array('error' => "Los campos 'usuario' y 'texto' son obligatorios."), 400);
        }
        if (longitudTexto($usuario) > MAX_USUARIO || longitudTexto($texto) > MAX_TEXTO) {
            responderJson(array(
                'error' => 'Máximo ' . MAX_USUARIO . ' caracteres en usuario y ' . MAX_TEXTO . ' en texto.'
            ), 400);
        }

        try {
            $mensaje = actualizarAlmacenMensajes(function (&$almacen) use ($usuario, $texto) {
                $mensaje = array(
                    'id' => $almacen['siguiente_id'],
                    'usuario' => $usuario,
                    'texto' => $texto,
                    'fecha' => gmdate('Y-m-d\TH:i:s\Z')
                );
                $almacen['siguiente_id']++;
                $almacen['mensajes'][] = $mensaje;
                if (count($almacen['mensajes']) > MAX_MENSAJES) {
                    array_shift($almacen['mensajes']);
                }
                return $mensaje;
            });
            responderJson($mensaje, 201);
        } catch (RuntimeException $exception) {
            error_log('Chat message storage failed: ' . $exception->getMessage());
            responderJson(array('error' => 'No se pudo guardar el mensaje.'), 500);
        }
    }

    metodoNoPermitido('GET, POST');
}

if ($ruta === 'api/estado') {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        metodoNoPermitido('GET');
    }
    try {
        $estado = actualizarAlmacenMensajes(function (&$almacen) {
            return array(
                'servicio' => 'Ejercicio_Chat v1',
                'mensajes_en_memoria' => count($almacen['mensajes']),
                'ultimo_id' => $almacen['siguiente_id'] - 1
            );
        });
        responderJson($estado);
    } catch (RuntimeException $exception) {
        error_log('Chat message storage failed: ' . $exception->getMessage());
        responderJson(array('error' => 'No se pudo consultar el estado del chat.'), 500);
    }
}

if ($ruta !== '') {
    responderJson(array('error' => 'Ruta no encontrada.'), 404);
}
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    metodoNoPermitido('GET');
}

$homeUrl = isset($_GET['return_url']) && is_string($_GET['return_url'])
    ? trim($_GET['return_url'])
    : '';
if ($homeUrl === false || $homeUrl === '') {
    $crudUrl = getenv('CRUD_URL');
    $homeUrl = $crudUrl === false ? '' : trim($crudUrl);
}
$homeUrlParts = $homeUrl === '' ? false : parse_url($homeUrl);
if (!$homeUrlParts
    || !isset($homeUrlParts['scheme'], $homeUrlParts['host'])
    || !in_array(strtolower($homeUrlParts['scheme']), array('http', 'https'), true)) {
    $homeUrl = '';
}

$apiBaseJson = json_encode(
    'app.php?route=',
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
);
require __DIR__ . '/chat_template.php';
