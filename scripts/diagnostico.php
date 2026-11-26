<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
echo 'PHP: ' . PHP_VERSION . PHP_EOL;
$ok = version_compare(PHP_VERSION, '8.1.0', '>=');
foreach (['mysqli','mbstring','session'] as $extension) {
    $loaded = extension_loaded($extension);
    echo $extension . ': ' . ($loaded ? 'disponible' : 'falta') . PHP_EOL;
    $ok = $ok && $loaded;
}
exit($ok ? 0 : 1);
