<?php
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
date_default_timezone_set('America/Guayaquil');
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
$https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
// El proxy HTTPS solo se considera cuando el administrador lo habilita.
if (getenv('TRUSTED_PROXY') === '1'
    && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') {
    $https = true;
}
session_name('agenda_session');
session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $https,
    'httponly' => true, 'samesite' => 'Lax']);
session_start();
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/views.php';

set_exception_handler(function (Throwable $error): void {
    error_log('[Agenda] ' . $error->getMessage());
    http_response_code(503);
    render_error('Servicio temporalmente no disponible',
        'No se pudo completar la operación. Inténtalo de nuevo en unos minutos.');
});

if (isset($_SESSION['usuario']) && time() - (int) ($_SESSION['ultima_actividad'] ?? 0) > 1800) {
    unset($_SESSION['usuario']);
    session_regenerate_id(true);
    flash('Tu sesión terminó por inactividad. Inicia sesión nuevamente.', 'info');
}
if (isset($_SESSION['usuario'])) {
    $_SESSION['ultima_actividad'] = time();
}
if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
