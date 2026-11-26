<?php
declare(strict_types=1);
// Instalación idempotente para el primer arranque del contenedor.
// Nunca se ejecuta desde una petición web.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
$path = $root . '/config/config.php';
$config = require (is_file($path) ? $path : $root . '/config/config.example.php');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$connection = null;
for ($attempt = 1; $attempt <= 30; $attempt++) {
    try {
        $connection = mysqli_init();
        $connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, 4);
        $connection->real_connect($config['db_host'], $config['db_user'], $config['db_pass'],
            $config['db_name'], $config['db_port'], $config['db_socket'] ?? null);
        break;
    } catch (mysqli_sql_exception $error) {
        if ($attempt === 30) {
            fwrite(STDERR, "No se pudo conectar con la base de datos durante el arranque.\n");
            exit(1);
        }
        sleep(2);
    }
}
$connection->set_charset('utf8mb4');
$sql = file_get_contents($root . '/database/agenda_contactos.sql');
if ($sql === false) { fwrite(STDERR, "No se encontró el esquema SQL.\n"); exit(1); }
$connection->multi_query($sql);
do {
    if ($result = $connection->store_result()) $result->free();
    if (!$connection->more_results()) break;
} while ($connection->next_result());
$connection->close();
echo "Esquema de la agenda preparado correctamente.\n";
