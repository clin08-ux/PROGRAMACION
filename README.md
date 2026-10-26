# Sitio UPVM (CRUD + encuesta + chat)

Todo el proyecto está en una sola carpeta y usa **una sola base de datos MySQL** (Railway)
con las tablas `products`, `upvm_survey_responses` y `usuarios` (ver `schema.sql`).

## Variables de entorno

| Variable | Descripción |
|---|---|
| `DB_HOST` | Host de Railway (ej. `xxxx.proxy.rlwy.net`) |
| `DB_PORT` | Puerto de Railway |
| `DB_USER` | Usuario |
| `DB_PASSWORD` | Contraseña |
| `DB_NAME` | Nombre de la base (ej. `railway`) |

También funcionan `MYSQLHOST`, `MYSQLPORT`, `MYSQLUSER`, `MYSQLPASSWORD` y `MYSQLDATABASE`.
Opcionales: `CHAT_URL`, `CRUD_URL`, `CHAT_DATA_FILE`.

## Subir a GitHub

```powershell
git add .
git commit -m "Proyecto listo para Render con una sola base de datos"
git push origin main
```

## Desplegar en Render

1. New > **Web Service** > conecta el repositorio de GitHub.
2. Runtime: **Docker** (usa el `Dockerfile`).
3. En **Environment** agrega las 5 variables `DB_*`.
4. Deploy. Las tablas se crean solas la primera vez que abres el sitio.

En Railway, el MySQL debe tener el acceso público (TCP proxy) para que Render se conecte.

## Limitaciones en Render

Las fotos subidas en `uploads/` y los mensajes del chat se guardan en el disco del servicio,
que es temporal: se pierden al reiniciar o redesplegar. Los datos de la base de datos sí persisten.
