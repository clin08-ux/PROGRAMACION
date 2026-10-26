<?php
// ---------------------------------------------------------------
// Configuración de la base de datos (una sola base para todo el sitio)
//
// Variables de entorno (Render -> Environment):
//   DB_HOST, DB_PORT, DB_USER, DB_PASSWORD, DB_NAME
// También se aceptan las variables que genera Railway:
//   MYSQLHOST, MYSQLPORT, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE
// ---------------------------------------------------------------
function envValue(array $names)
{
    foreach ($names as $name) {
        $value = getenv($name);
        if ($value !== false && trim($value) !== '') {
            return trim($value);
        }
    }
    return null;
}

$dbHost     = envValue(array('DB_HOST', 'MYSQLHOST'));
$dbUser     = envValue(array('DB_USER', 'MYSQLUSER'));
$dbPassword = envValue(array('DB_PASSWORD', 'MYSQLPASSWORD'));
$dbName     = envValue(array('DB_NAME', 'MYSQLDATABASE'));
$dbPortRaw  = envValue(array('DB_PORT', 'MYSQLPORT'));

$missingEnvironment = array();
if ($dbHost === null)     { $missingEnvironment[] = 'DB_HOST'; }
if ($dbUser === null)     { $missingEnvironment[] = 'DB_USER'; }
if ($dbPassword === null) { $missingEnvironment[] = 'DB_PASSWORD'; }
if ($dbName === null)     { $missingEnvironment[] = 'DB_NAME'; }
if ($missingEnvironment) {
    error_log('Missing database environment variables: ' . implode(', ', $missingEnvironment));
    http_response_code(500);
    exit('Faltan variables de entorno para configurar la base de datos: ' . implode(', ', $missingEnvironment));
}

$dbPort = $dbPortRaw === null ? 3306 : filter_var($dbPortRaw, FILTER_VALIDATE_INT);
if ($dbPort === false || $dbPort < 1 || $dbPort > 65535) {
    error_log('Invalid DB_PORT environment variable.');
    http_response_code(500);
    exit('La variable DB_PORT no contiene un puerto válido.');
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $db = new mysqli($dbHost, $dbUser, $dbPassword, $dbName, $dbPort);
    $db->set_charset('utf8mb4');
} catch (mysqli_sql_exception $exception) {
    error_log('Database connection failed: ' . $exception->getMessage());
    http_response_code(500);
    exit('No se pudo conectar a la base de datos. Revisa las variables de entorno y el acceso remoto del servidor MySQL.');
}

// Tablas de la base única (las mismas que en schema.sql)
$createTables = array(
    "CREATE TABLE IF NOT EXISTS products (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        description TEXT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        stock INT UNSIGNED NOT NULL DEFAULT 0,
        photo1 VARCHAR(255) NOT NULL,
        photo2 VARCHAR(255) NOT NULL,
        photo3 VARCHAR(255) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    "CREATE TABLE IF NOT EXISTS upvm_survey_responses (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        career VARCHAR(150) NOT NULL,
        shift VARCHAR(100) NOT NULL,
        teachers_opinion VARCHAR(255) NOT NULL,
        improve VARCHAR(500) NOT NULL,
        dislike VARCHAR(500) NOT NULL,
        likes_career VARCHAR(100) NOT NULL,
        constructive_criticism TEXT NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    "CREATE TABLE IF NOT EXISTS usuarios (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(150) NOT NULL,
        correo VARCHAR(254) NOT NULL,
        sexo ENUM('hombre','mujer','prefiero no decirlo') NOT NULL,
        edad TINYINT UNSIGNED NOT NULL,
        carrera ENUM('administracion','ingenieria en tecnologias de la informacion','enfermeria','mecatronica','logistica','ingenieria en alimentos','ingenieria industrial','robotica') NOT NULL,
        contrasena VARCHAR(255) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

try {
    foreach ($createTables as $createTable) {
        $db->query($createTable);
    }
} catch (mysqli_sql_exception $exception) {
    error_log('Database table initialization failed: ' . $exception->getMessage());
    http_response_code(500);
    exit('No se pudieron crear las tablas. Revisa los permisos de la base de datos.');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['crud_token'])) {
    $_SESSION['crud_token'] = bin2hex(random_bytes(32));
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrfField()
{
    return '<input type="hidden" name="csrf_token" value="' . e($_SESSION['crud_token']) . '">';
}

function verifyCsrfToken()
{
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!is_string($token) || !hash_equals($_SESSION['crud_token'], $token)) {
        throw new RuntimeException('La sesión expiró. Recarga la página e inténtalo de nuevo.');
    }
}

function getProductInput()
{
    $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
    $description = trim(isset($_POST['description']) ? $_POST['description'] : '');
    $price = trim(isset($_POST['price']) ? $_POST['price'] : '');
    $stockValue = trim(isset($_POST['stock']) ? $_POST['stock'] : '');

    if ($name === '' || $description === '') {
        throw new RuntimeException('Escribe el nombre y la descripción del producto.');
    }
    if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', $price)) {
        throw new RuntimeException('El precio debe ser positivo y tener como máximo dos decimales.');
    }

    $stock = filter_var($stockValue, FILTER_VALIDATE_INT);
    if ($stock === false || $stock < 0) {
        throw new RuntimeException('Las existencias deben ser un número entero igual o mayor que cero.');
    }

    return array($name, $description, $price, $stock);
}

function saveUploadedPhoto($field, $required)
{
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        if ($required) {
            throw new RuntimeException('Debes seleccionar las dos fotos.');
        }
        return null;
    }

    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('Cada foto debe pesar 5 MB o menos.');
    }

    $imageInfo = @getimagesize($file['tmp_name']);
    $mimeTypes = array(
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    );
    $mime = $imageInfo && isset($imageInfo['mime']) ? $imageInfo['mime'] : '';
    if (!isset($mimeTypes[$mime])) {
        throw new RuntimeException('Usa únicamente imágenes JPG, PNG o WEBP válidas.');
    }

    $directory = __DIR__ . '/uploads';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('No se pudo crear la carpeta para las fotos.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $mimeTypes[$mime];
    if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
        throw new RuntimeException('No se pudo guardar una foto.');
    }

    return $filename;
}

function removePhotos($filenames)
{
    foreach ($filenames as $filename) {
        if ($filename) {
            $path = __DIR__ . '/uploads/' . basename($filename);
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}