<?php
// Enrutador del servidor PHP local. Apache utiliza los archivos .htaccess.
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
if (preg_match('~^/(app|config|database|deploy|docs|tests|scripts|\.git)(/|$)|/\.|\.(sql|md|ini|log|ya?ml)$~i', $path)
    || $path === '/dev_router.php' || $path === '/Dockerfile' || $path === '/compose.yaml') {
    http_response_code(403); echo 'Acceso no permitido.'; return true;
}
if ($path !== '/' && is_file(__DIR__ . $path)) return false;
if ($path === '/health/') { require __DIR__ . '/health/index.php'; return true; }
if ($path === '/') { require __DIR__ . '/index.php'; return true; }
http_response_code(404); echo 'Página no encontrada.'; return true;
