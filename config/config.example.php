<?php
// Copiar como config.php. No publicar credenciales en GitHub.
return [
    'db_host' => getenv('DB_HOST') ?: '127.0.0.1',
    'db_port' => (int) (getenv('DB_PORT') ?: 3306),
    'db_name' => getenv('DB_NAME') ?: 'agenda_contactos',
    'db_user' => getenv('DB_USER') ?: 'root',
    'db_pass' => getenv('DB_PASS') ?: '',
    'db_socket' => getenv('DB_SOCKET') ?: null,
];
