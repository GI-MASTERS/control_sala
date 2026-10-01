# ControlSala v1.0 — Instalación (Win11 + VLAN)

## A. Servidor (solo 1 PC o mini-PC, ej `192.168.1.10`)
1. Copia esta carpeta a `C:\xampp\htdocs\controlsala` (o deja en `C:\xampp\controlsala` y sirve con PHP).
2. Opción XAMPP: inicia Apache, abre `http://localhost/controlsala/`.
   Opción rápida: `C:\xampp\php\php.exe -S 0.0.0.0:8000 -t C:\xampp\controlsala` → panel en `http://IP_SERVIDOR:8000`.
3. Edita `config.php` → cambia `CS_TOKEN` por una clave tuya.
4. Abre firewall: permite TCP 80 (o 8000) solo desde tu VLAN.
5. `data.db` y `uploads/` se crean solos.

## B. Clientes (cada PC alumno Win11)
1. Instala Python 3.11 desde python.org (marca **Add to PATH**).
2. Copia carpeta `client/` a `C:\ControlSala\`.
3. Edita `C:\ControlSala\agente.py`: `SERVIDOR="http://192.168.1.10:8000"` (o `/controlsala`) y `TOKEN` igual que servidor.
4. `pip install requests` y prueba: `python agente.py` (debe aparecer en el panel).
5. Auto-inicio: ejecuta `instalar.bat` como Administrador (crea tarea programada al logon).
6. El alumno guarda sus trabajos en `Documentos\ControlSala\` para que `Recolectar` los encuentre.

## C. Uso en clase
- **Fondo:** Subir JPG → Todos → Enviar.
- **Web:** Pegar URL → Abrir en clientes.
- **Archivos:** Enviar (llega a Documentos/ControlSala) / Recolectar `*.docx` (llega a `uploads/collected/`).
- **Bloquear:** 🔒 BLOQUEAR TODOS mientras explicas, 🔓 para liberar.
- **Apagar:** ⏻ con 15s de aviso. Requiere que el agente corra con privilegio para shutdown (tarea con /rl highest).

## D. Migración futura a Laravel
La API ya es REST (`pcs`, `commands`) y la DB es trasladable a migraciones Eloquent. El agente no cambia.
