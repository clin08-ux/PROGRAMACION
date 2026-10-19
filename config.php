<?php
// conexion.php - Soporte para MySQLi y PDO

// Obtener credenciales desde las variables de entorno de Render o valores por defecto
$host     = getenv('DB_HOST') ?: 'localhost';
$user     = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$database = getenv('DB_NAME') ?: 'railway';
$port     = getenv('DB_PORT') ?: 3306;

// 1. Conexión mediante MySQLi (por si algún otro módulo la usa)
$conn = new mysqli($host, $user, $password, $database, (int)$port);
$conexion = $conn;

if ($conn->connect_error) {
    die("Error de conexión MySQLi: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// 2. Conexión mediante PDO (requerida para login.php y registro.php)
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die("Error de conexión PDO: " . $e->getMessage());
}

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
