<?php
require_once __DIR__ . '/app/bootstrap.php';
check_csrf();
$_SESSION = [];
session_regenerate_id(true);
$_SESSION['csrf'] = bin2hex(random_bytes(32));
flash('Cerraste sesión correctamente. Hasta pronto.', 'info');
redirect('login.php');
