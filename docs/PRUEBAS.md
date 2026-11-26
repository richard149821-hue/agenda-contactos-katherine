# Evidencias de funcionamiento y despliegue

Verificación pública: 25 de noviembre de 2026 a las 19:16, hora de Ecuador continental.

**Resultado en Internet: 50 comprobaciones correctas de 50 ejecutadas.**

URL comprobada: [https://agenda-web-production-03f14b30.up.railway.app/](https://agenda-web-production-03f14b30.up.railway.app/)

## Entorno público comprobado

- Railway, proyecto `agenda-contactos-katherine`, entorno `production`.
- Servicio `agenda-web`: imagen `webdevops/php-apache:8.3-alpine`, PHP 8.3, Apache y PHP-FPM.
- Servicio separado `MySQL`: imagen `mysql:8`, volumen persistente montado en `/var/lib/mysql`.
- HTTPS público y conexión a la base a través de la red privada del proyecto.
- Despliegue web `6263d54d-6f28-4a38-8b6d-03151d42cac8`: estado final SUCCESS.
- Inicio de sesión y endpoint `/health/`: HTTP 200. Cookie de sesión Secure, HttpOnly y SameSite Lax.

El script probó la aplicación publicada, con cuentas y contactos ficticios creados para la prueba. La base es MySQL real; las operaciones no usan almacenamiento local del navegador. El resultado detallado está en `pruebas-publicas.json`.

## Comprobaciones HTTP públicas

| Número | Comprobación | Resultado |
| --- | --- | --- |
| 1 | Acceso privado sin sesión redirige al inicio de sesión | Correcto |
| 2 | Cookie de sesión HttpOnly y SameSite | Correcto |
| 3 | Cookie Secure en HTTPS | Correcto |
| 4 | Registro rechaza solicitudes sin token CSRF | Correcto |
| 5 | Registro válido crea la cuenta | Correcto |
| 6 | Se rechazan correos duplicados | Correcto |
| 7 | Credenciales incorrectas no inician sesión | Correcto |
| 8 | Inicio de sesión válido muestra el panel personal | Correcto |
| 9 | La sesión autenticada conserva una cookie | Correcto |
| 10 | Crear contacto con categoría y favorito | Correcto |
| 11 | Las cinco categorías requeridas están disponibles | Correcto |
| 12 | La creación persiste registros en la base de datos | Correcto |
| 13 | Búsqueda por nombre | Correcto |
| 14 | Búsqueda por correo | Correcto |
| 15 | Búsqueda por ciudad | Correcto |
| 16 | Búsqueda general combina nombre correo y ciudad | Correcto |
| 17 | Ordenamiento nombre_asc | Correcto |
| 18 | Ordenamiento nombre_desc | Correcto |
| 19 | Ordenamiento ciudad_asc | Correcto |
| 20 | Ordenamiento ciudad_desc | Correcto |
| 21 | Ordenamiento fecha_desc | Correcto |
| 22 | Ordenamiento fecha_asc | Correcto |
| 23 | Filtro de favoritos | Correcto |
| 24 | Filtro por categoría | Correcto |
| 25 | Búsqueda categoría y favoritos funcionan combinados | Correcto |
| 26 | Activar favorito desde el listado | Correcto |
| 27 | Desactivar favorito desde el listado | Correcto |
| 28 | Validación del servidor impide correo y categoría inválidos | Correcto |
| 29 | Edición de un contacto propio | Correcto |
| 30 | La búsqueda resiste entrada de inyección SQL | Correcto |
| 31 | El porcentaje se busca como texto literal | Correcto |
| 32 | El ordenamiento admite solo columnas de la lista permitida | Correcto |
| 33 | Una segunda cuenta no ve los contactos de la primera | Correcto |
| 34 | No se puede abrir la edición de un contacto ajeno cambiando la URL | Correcto |
| 35 | No se puede abrir la confirmación de borrado de un contacto ajeno | Correcto |
| 36 | Acción favorito rechaza un contacto ajeno | Correcto |
| 37 | Acción eliminar rechaza un contacto ajeno | Correcto |
| 38 | El POST manipulado no modifica contactos ajenos | Correcto |
| 39 | El borrado exige un token CSRF válido | Correcto |
| 40 | La pantalla de confirmación no borra por GET | Correcto |
| 41 | La confirmación POST elimina el contacto propio | Correcto |
| 42 | Se escapa HTML en la salida para impedir XSS almacenado | Correcto |
| 43 | Carga opcional de ocho contactos ficticios por cuenta | Correcto |
| 44 | La carga de ejemplos no duplica contactos | Correcto |
| 45 | Paginación sin registros repetidos entre páginas | Correcto |
| 46 | Acceso web bloqueado a config/config.php | Correcto |
| 47 | Acceso web bloqueado a database/agenda_contactos.sql | Correcto |
| 48 | Acceso web bloqueado a app/helpers.php | Correcto |
| 49 | Acceso web bloqueado a tests/integration.py | Correcto |
| 50 | Cerrar sesión revoca el acceso a las páginas privadas | Correcto |

Los archivos internos reciben 403 o 404 según estén protegidos por Apache o excluidos del paquete de alojamiento. `tests/integration.py` no se instala en el contenedor público. Ninguno de esos archivos se entrega por HTTP.

## Capturas y prueba local previa

La captura `capturas/06-inicio-publico.jpg` muestra la pantalla de inicio servida por la URL de Railway. Las capturas 01 a 05 corresponden a la prueba local anterior, identificada en el README. Se revisaron inicio, directorio, formulario, favoritos y búsqueda por ciudad con orden descendente.

La prueba local previa se ejecutó con PHP 8.4.28 y MariaDB 11.8.9, y obtuvo 49 de 49 comprobaciones correctas. Su reporte original permanece en `pruebas.json`. La comprobación adicional del alojamiento corresponde a la cookie Secure sobre HTTPS.

## Repetir la verificación

```sh
python3 -m pip install requests beautifulsoup4
python3 tests/integration.py --base-url https://agenda-web-production-03f14b30.up.railway.app/ --output pruebas-publicas.json
```

El script crea dos cuentas ficticias únicas en cada ejecución. Comprueba el aislamiento entre ellas y elimina únicamente contactos creados dentro de su propio flujo; las cuentas de prueba quedan almacenadas.

## Alcance de la entrega

El sitio público y las cuatro mejoras están comprobados. El código, el esquema SQL, el README y las capturas se publican en [https://github.com/richard149821-hue/agenda-contactos-katherine](https://github.com/richard149821-hue/agenda-contactos-katherine). La documentación identifica a Katherine Ximena Morocho Guanuche como estudiante de esta actividad.

No se ejecutó Docker Compose ni la migración sobre una base previa real de la estudiante; sí se importó y utilizó el esquema completo entregado. No se hizo una prueba en un teléfono físico. La continuidad del hosting está sujeta al saldo y a los límites gratuitos de Railway; no se garantiza alojamiento indefinido.
