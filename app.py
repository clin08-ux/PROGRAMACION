"""
Ejercicio_Chat - Versión 1
Chat con Flask: API REST + lista en memoria (sin base de datos).

Endpoints:
    GET  /                      -> Página del chat (cliente HTML + JS)
    GET  /api/mensajes?desde=N  -> Mensajes con id mayor que N (JSON)
    POST /api/mensajes          -> Crea un mensaje {"usuario": "...", "texto": "..."}
    GET  /api/estado            -> Información básica del servicio
"""

import os
import threading
from datetime import datetime, timezone
from urllib.parse import urlparse

from flask import Flask, jsonify, render_template, request

app = Flask(__name__)

# ---------------------------------------------------------------
# "Almacenamiento": una lista de Python en la memoria del servidor.
# Se pierde al detener o reiniciar el servidor.
# ---------------------------------------------------------------
mensajes = []
siguiente_id = 1
candado = threading.Lock()   # Evita que dos peticiones simultáneas choquen

MAX_MENSAJES = 200           # Solo se conservan los últimos 200
MAX_USUARIO = 30
MAX_TEXTO = 500


@app.get("/")
def inicio():
    home_url = request.args.get("return_url", "").strip() or os.environ.get("CRUD_URL", "").strip()
    if home_url:
        parsed_url = urlparse(home_url)
        if parsed_url.scheme not in ("http", "https") or not parsed_url.netloc:
            home_url = ""
    return render_template("index.html", home_url=home_url)


@app.get("/api/mensajes")
def listar_mensajes():
    """Devuelve solo los mensajes nuevos (id > desde)."""
    desde = request.args.get("desde", default=0, type=int)
    with candado:
        nuevos = [m for m in mensajes if m["id"] > desde]
    return jsonify(nuevos), 200


@app.post("/api/mensajes")
def crear_mensaje():
    """Recibe un mensaje en JSON, lo valida y lo agrega a la lista."""
    global siguiente_id

    datos = request.get_json(silent=True) or {}
    usuario = str(datos.get("usuario", "")).strip()
    texto = str(datos.get("texto", "")).strip()

    if not usuario or not texto:
        return jsonify({"error": "Los campos 'usuario' y 'texto' son obligatorios."}), 400
    if len(usuario) > MAX_USUARIO or len(texto) > MAX_TEXTO:
        return jsonify({"error": f"Máximo {MAX_USUARIO} caracteres en usuario y {MAX_TEXTO} en texto."}), 400

    with candado:
        mensaje = {
            "id": siguiente_id,
            "usuario": usuario,
            "texto": texto,
            "fecha": datetime.now(timezone.utc).isoformat(),
        }
        siguiente_id += 1
        mensajes.append(mensaje)
        if len(mensajes) > MAX_MENSAJES:
            del mensajes[0]

    return jsonify(mensaje), 201


@app.get("/api/estado")
def estado():
    with candado:
        total = len(mensajes)
        ultimo = siguiente_id - 1
    return jsonify({"servicio": "Ejercicio_Chat v1", "mensajes_en_memoria": total, "ultimo_id": ultimo})


if __name__ == "__main__":
    # host 0.0.0.0 permite conexiones desde otras máquinas de la red
    # PORT lo define Render; en local se usa 5000
    puerto = int(os.environ.get("PORT", 5000))
    app.run(host="0.0.0.0", port=puerto, debug=True)
