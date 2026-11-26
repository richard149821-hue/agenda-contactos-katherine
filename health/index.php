<?php
// Comprobación de disponibilidad del proceso web; no devuelve datos del sistema.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
echo json_encode(['status' => 'ok']);
