<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'pitlane_f1');
define('DB_USER', 'daniel');
define('DB_PASS', 'lenovo');
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opciones);
        } catch (PDOException $e) {
            error_log("Error de BD: " . $e->getMessage());
            die("<h2 style='font-family:sans-serif;color:#c00;padding:30px'>
                Error de conexión a la base de datos.<br>
                <small style='font-size:14px;color:#666'>" . htmlspecialchars($e->getMessage()) . "</small>
                </h2>");
        }
    }
    return $pdo;
}
