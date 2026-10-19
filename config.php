<?php
// conexion.php - Soporte optimizado para Render (PostgreSQL / MySQL)

$host     = getenv('DB_HOST')     ?: 'localhost';
$user     = getenv('DB_USER')     ?: 'root';
$password = getenv('DB_PASS')     ?: (getenv('DB_PASSWORD') ?: '');
$database = getenv('DB_NAME')     ?: 'railway';
$port     = getenv('DB_PORT')     ?: '5432'; // Por defecto 5432 para Postgres en Render (cambia a 3306 si usas MySQL)

// Detectar si estamos usando PostgreSQL (nativo de Render) o MySQL
$isPostgres = (strpos($host, 'render.com') !== false || $port == 5432 || getenv('DATABASE_URL') !== false);

try {
    if ($isPostgres) {
        // Conexión PDO para PostgreSQL (Render)
        $dsn = "pgsql:host=$host;port=$port;dbname=$database;sslmode=require";
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } else {
        // Conexión PDO para MySQL (por si usas Railway u otro entorno MySQL)
        $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    die("Error de conexión PDO: " . $e->getMessage());
}

// -----------------------------------------------------------------
// ALIAS GLOBALES: Para asegurar compatibilidad con TODO tu proyecto
// -----------------------------------------------------------------
$db = $pdo;         // Soluciona el error en administrar.php y crud.php que buscaban $db
$conexion = $pdo;   // Alias por si usas $conexion

// Nota: Si algún archivo viejo usa estrictamente mysqli, se recomienda migrarlo a PDO,
// pero con esto cubrimos que las consultas PDO funcionen perfectamente.

// Iniciar sesión de forma segura si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Función global para escapar texto y evitar XSS
if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
?>
