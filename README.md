# Sitio UPVM (CRUD, encuesta y chat)

El proyecto usa una sola base de datos MySQL con las tablas `products`,
`upvm_survey_responses`, `usuarios`, `ubicaciones` y `fotos_camara`. La definición
completa está en `schema.sql`; la aplicación también crea las tablas si no existen.

## Ejecutar en local

Configura las variables de entorno de la base de datos y ejecuta el sitio con PHP
y Apache, o con el servidor integrado de PHP:

```powershell
php -S 0.0.0.0:5000
```

Abre `http://localhost:5000/index.php`. El chat puede abrirse desde
`http://localhost:5000/app.php`.

## Variables de entorno

| Variable | Descripción |
|---|---|
| `DB_HOST` | Host de MySQL |
| `DB_PORT` | Puerto de MySQL (opcional; por defecto `3306`) |
| `DB_USER` | Usuario de MySQL |
| `DB_PASSWORD` | Contraseña de MySQL |
| `DB_NAME` | Nombre de la base de datos |

También se aceptan `MYSQLHOST`, `MYSQLPORT`, `MYSQLUSER`, `MYSQLPASSWORD` y
`MYSQLDATABASE`. Para configurar el regreso desde el chat se puede establecer
`CRUD_URL`. `CHAT_DATA_FILE` permite elegir dónde se guardan los mensajes.

## Desplegar en Render

1. Crea un **Web Service** conectado al repositorio de GitHub.
2. Selecciona **Docker** para que Render use el `Dockerfile` del proyecto.
3. Configura `DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASSWORD` y `DB_NAME` en las
   variables de entorno del servicio.
4. Asegúrate de que MySQL permita conexiones desde Render y despliega.

Los mensajes del chat y las fotos subidas se guardan en el disco local del
servicio, que puede ser temporal. Los datos de MySQL sí persisten.

## API del chat

- `app.php?route=api/estado`
- `app.php?route=api/mensajes`

Los mensajes se conservan en un archivo JSON, hasta un máximo de 200; el cliente
consulta mensajes nuevos cada dos segundos. El archivo local no se comparte entre
varias instancias del servicio.

Para probar la API en local:

```text
curl "http://localhost:5000/app.php?route=api/estado"
curl -X POST "http://localhost:5000/app.php?route=api/mensajes" -H "Content-Type: application/json" -d "{\"usuario\":\"Ana\",\"texto\":\"Hola\"}"
curl "http://localhost:5000/app.php?route=api/mensajes&desde=0"
```
