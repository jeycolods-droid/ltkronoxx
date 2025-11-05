<?php
/*
 * Archivo: assets/config/conexion.php
 * * 1. Carga la configuración de 'config.php'
 * 2. Usa esa configuración para crear la conexión PDO (para PostgreSQL).
 * 3. Deja la conexión en la variable $conn.
 */

// 1. Cargar la configuración
// __DIR__ asegura que encuentre el config.php en la misma carpeta
$config = include(__DIR__ . 'config.php');

// 2. Obtener las credenciales de la BBDD desde el array de config
$host = $config['db']['host'];
$db   = $config['db']['dbname'];
$user = $config['db']['user'];
$pass = $config['db']['password'];
$port = 5432; // Puerto por defecto de PostgreSQL

// DSN (Data Source Name) para PostgreSQL
$dsn = "pgsql:host=$host;port=$port;dbname=$db";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // 3. Crear la conexión y guardarla en $conn
    // Esto funciona con el driver 'pdo_pgsql' que ya instalaste
    $conn = new PDO($dsn, $user, $pass, $options);

} catch (\PDOException $e) {
    // Manejo de errores
    error_log("Error de conexión a la BBDD: " . $e->getMessage());
    die("Error al conectar con la base de datos.");
}

// Al incluir este archivo, la variable $conn (un objeto PDO)
// estará disponible para tus otros scripts.
?>