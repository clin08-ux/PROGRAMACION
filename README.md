# Chat de la clase

Chat en PHP con una API REST. Conserva los últimos 200 mensajes en un archivo JSON local y el navegador consulta mensajes nuevos cada 2 segundos. El chat se abre desde `app.php`.

## Ejecutar en local (Windows)

Con PHP instalado, desde la carpeta del proyecto:

```powershell
php -S 0.0.0.0:5000 app.php
```

Abre http://localhost:5000/app.php en dos pestañas con nombres distintos.

## Conectar el chat con el sitio PHP

Configura `CHAT_URL` en el servidor del sitio para que apunte a la página del chat:

```text
CHAT_URL=https://tu-sitio.example.com/app.php
```

El enlace del sitio abre el chat y le pasa la dirección de regreso. Si se abre el chat directamente, `CRUD_URL` puede configurarse con la dirección del sitio:

```text
CRUD_URL=https://tu-sitio.example.com/
```

## Desplegar en Render

1. Sube el repositorio a GitHub y crea un **Web Service** en Render conectado al repositorio.
2. Usa el `Dockerfile` incluido para desplegar el servicio PHP con Apache.
3. Configura `CHAT_URL` en Render con la URL del servicio terminada en `/app.php`.
4. Opcionalmente configura `CRUD_URL` para que el chat muestre el enlace de regreso al sitio.

La API está disponible en `app.php?route=api/estado` y `app.php?route=api/mensajes`. Se puede establecer `CHAT_DATA_FILE` para elegir la ruta del archivo JSON; si no, se guarda en el directorio temporal del servidor. El archivo es local al servicio y no es una base de datos compartida entre varias instancias.

## Probar la API sin navegador

```text
curl "http://localhost:5000/app.php?route=api/estado"
curl -X POST "http://localhost:5000/app.php?route=api/mensajes" -H "Content-Type: application/json" -d "{\"usuario\":\"Ana\",\"texto\":\"Hola\"}"
curl "http://localhost:5000/app.php?route=api/mensajes&desde=0"
```

## Limitaciones

- Los mensajes se pierden al reiniciar el servidor o si Render reemplaza su sistema de archivos local.
- Solo se conservan los últimos 200 mensajes.
- No hay autenticación: cualquiera puede escribir con cualquier nombre.
- El cliente consulta mensajes cada 2 segundos (polling).
