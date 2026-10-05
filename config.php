<?php
$host = getenv('DB_HOST');
$port = (int) getenv('DB_PORT');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$name = getenv('DB_NAME');

$conexion = new mysqli($host, $user, $pass, $name, $port);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");
