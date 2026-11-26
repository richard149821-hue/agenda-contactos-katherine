#!/bin/sh
set -eu
php /app/scripts/inicializar.php
chown -R 1000:1000 /app
exec /entrypoint supervisord
