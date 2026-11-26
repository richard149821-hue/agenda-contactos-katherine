# Despliegue de Mi agenda

## Alojamiento publicado y comprobado

- URL: [Mi agenda personal](https://agenda-web-production-03f14b30.up.railway.app/).
- Proveedor: Railway, cuenta del propietario conectada para este trabajo.
- Proyecto: `agenda-contactos-katherine`; entorno: `production`.
- Servicio web: `agenda-web`, PHP 8.3 con Apache y PHP-FPM.
- Base: servicio separado `MySQL`, imagen `mysql:8`, con volumen persistente en `/var/lib/mysql`.
- Verificación: 25 de noviembre de 2026, 19:16, Ecuador continental; 50 de 50 comprobaciones correctas.

Para evaluar, abrir la URL, crear una cuenta con datos de prueba e iniciar sesión. Pulsar **Cargar ejemplos** para obtener ocho contactos ficticios. Cada cuenta administra sus propios contactos.

## Configuración usada en Railway

El despliegue se realizó sin esperar a la autorización de escritura de GitHub. Se utilizó un contenedor público de PHP con el código de la aplicación empaquetado en una variable de configuración. El archivo `railway-config.json` conserva la configuración reproducible; no contiene contraseñas.

1. Crear un proyecto y desplegar la plantilla oficial `mysql`, con base `agenda_contactos`. Railway genera la contraseña de la base. Mantener su volumen persistente.
2. Crear un servicio vacío llamado `agenda-web` y configurar las variables antes de conectar la imagen.
3. Generar el paquete de código, desde la raíz del proyecto:

   ```sh
   python3 scripts/empaquetar_railway.py --salida ../agenda-source.b64
   ```

   Copiar el contenido del archivo generado en `AGENDA_SOURCE`. Contiene solamente el código necesario para servir la aplicación y el esquema SQL; excluye `config/config.php`, contraseñas, pruebas, capturas y datos de usuarios.
4. Definir la conexión mediante referencias al servicio llamado exactamente `MySQL`:

   | Variable web | Referencia |
   | --- | --- |
   | `DB_HOST` | `${{MySQL.MYSQLHOST}}` |
   | `DB_PORT` | `${{MySQL.MYSQLPORT}}` |
   | `DB_NAME` | `${{MySQL.MYSQLDATABASE}}` |
   | `DB_USER` | `${{MySQL.MYSQLUSER}}` |
   | `DB_PASS` | `${{MySQL.MYSQLPASSWORD}}` |

5. Aplicar las demás variables y el comando de inicio que constan en `railway-config.json`. Usar `PORT=80`, `WEB_DOCUMENT_ROOT=/app`, `WEB_DOCUMENT_INDEX=index.php`, `TRUSTED_PROXY=1` y comprobación de disponibilidad `/health/` en el puerto 80. El reconocimiento del proxy permite enviar la cookie Secure a través del HTTPS público de Railway.
6. Conectar la imagen `webdevops/php-apache:8.3-alpine`. El comando de inicio extrae el paquete dentro de `/app`, ejecuta `scripts/inicializar.php` y arranca Apache y PHP-FPM. El inicializador crea las tablas y categorías si faltan; no borra usuarios ni contactos existentes.
7. Generar un dominio público de Railway con puerto de destino 80. Esperar el estado final **SUCCESS** y comprobar `/health/` e inicio de sesión desde Internet.
8. Ejecutar las pruebas HTTP indicadas en `PRUEBAS.md`.

Los datos se guardan en MySQL y su volumen, fuera del contenedor web. No publicar la base mediante un proxy TCP para utilizar esta aplicación: ambos servicios se comunican por la red privada. Las sesiones de PHP están en el contenedor web y pueden perderse al reiniciarlo; los contactos persisten.

Para actualizar el código, regenerar `AGENDA_SOURCE` y volver a desplegar el servicio web. No eliminar el volumen de MySQL. Si cambia el esquema, preparar una migración antes de actualizar una base con datos. Para un flujo mantenido con GitHub se puede utilizar el Dockerfile incluido, configurar las mismas variables y ejecutar el inicializador por consola antes de servir la aplicación.

## Condiciones del alojamiento gratuito

Se emplearon los recursos gratuitos disponibles en la cuenta conectada. Railway ofrece créditos iniciales de prueba y un plan Free limitado; la aplicación puede suspenderse al agotarse los créditos. El modo de suspensión por inactividad del servicio web está habilitado. No se garantiza una disponibilidad indefinida ni se contrató un plan de pago para este trabajo.

Revisar el saldo y las condiciones en el panel del propietario antes de la evaluación: [documentación de Railway](https://docs.railway.com/pricing/free-plan). Mantener una copia del SQL y del proyecto; revisar también la política de conservación del volumen al terminar la prueba. La versión del proveedor puede cambiar sus condiciones.

## Alternativa en InfinityFree u otro hosting compartido

1. Crear el alojamiento PHP y una base MySQL en la cuenta del propietario.
2. Seleccionar la base asignada en phpMyAdmin e importar `database/agenda_contactos.sql`.
3. Generar el paquete para `htdocs`:

   ```sh
   python3 scripts/preparar_hosting.py --salida ../agenda-hosting.zip
   ```

4. Extraer el ZIP y subir su contenido dentro de `htdocs`, conservando todos los `.htaccess`. No agregar una carpeta contenedora adicional.
5. Copiar `config/config.example.php` como `config/config.php` y escribir los datos de conexión asignados por ese hosting. El servidor MySQL puede ser distinto de `localhost`. No publicar este archivo en GitHub.
6. Activar el certificado SSL gratuito desde el panel y comprobar la URL HTTPS. Verificar registro, inicio de sesión, CRUD y las cuatro mejoras.

El paquete para hosting compartido no contiene SQL, scripts de instalación ni credenciales; el SQL se importa por separado. Las instrucciones alternativas no acreditan un despliegue en InfinityFree: el alojamiento verificado para esta entrega es Railway.

## Repositorio GitHub de la entrega

Repositorio público: [https://github.com/richard149821-hue/agenda-contactos-katherine](https://github.com/richard149821-hue/agenda-contactos-katherine).

El proyecto está identificado a nombre de **Katherine Ximena Morocho Guanuche**, usuario EVA **kxmorocho**. Contiene el código PHP, los estilos, el script SQL, las capturas y la documentación. La cuenta utilizada para alojarlo fue autorizada por el propietario de la sesión.

La publicación se realiza mediante la interfaz web de GitHub. El paquete excluye `config/config.php`, claves, sesiones y archivos locales de ejecución. Para mantener cambios posteriores, clonar el repositorio y utilizar Git:

```sh
git clone https://github.com/richard149821-hue/agenda-contactos-katherine.git
cd agenda-contactos-katherine
```

Los pasos de instalación local constan en el README. La guía de Railway de este archivo permite reproducir el despliegue sin depender de la sesión del navegador utilizada para publicar.
