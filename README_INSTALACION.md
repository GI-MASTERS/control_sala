# ControlSala v1.0 — Instalación (Win11 + VLAN)

## A. Servidor (solo 1 PC o mini-PC, ej `192.168.1.10`)
1. Copia esta carpeta a `C:\xampp\htdocs\controlsala` (o deja en `C:\xampp\controlsala` y sirve con PHP).
2. Opción XAMPP: inicia Apache, abre `http://localhost/controlsala/`.
   Opción rápida: `C:\xampp\php\php.exe -S 0.0.0.0:8000 -t C:\xampp\controlsala` → panel en `http://IP_SERVIDOR:8000`.
3. Edita `config.php` → cambia `CS_TOKEN` por una clave tuya.
4. Abre firewall: permite TCP 80 (o 8000) solo desde tu VLAN.
5. `data.db` y `uploads/` se crean solos.

## B. Clientes (cada PC alumno Win11) — USA EL ZIP, no copies client/ a mano
1. En el panel lateral Agentes: poné etiqueta (Equipo 1), Generar token, Descargar ZIP.
2. Llevá ese ZIP a la PC alumno, descomprimí, doble clic `instalar.bat` como Administrador (instala Python vía winget si falta + deps offline wheels + tarea logon /rl highest).
3. El instalador corre `agente.py --probar` solo: tiene que decir OK. Si dice FALLO, corre `diagnostico.bat` y mirá [4] ping y [5] register.
4. Si cambió la IP del servidor y no querés re-descargar: editá `servidor.txt` junto a agente.py con `http://IP_NUEVA:8000` y reejecutá instalar.bat.
5. El alumno guarda sus trabajos en `C:\ProgramData\ControlSala\` (NO en Documentos, por Ransomware Protection) para que `Recolectar` los encuentre.

## C. Uso en clase + diagnóstico si no aparece nada
- Si el panel dice 0 equipos: 1) en servidor `http://localhost:8000/api.php?action=ping` debe dar ok, 2) en alumno navegador `http://IP_SERVIDOR:8000/api.php?action=ping` debe abrir, 3) `uploads/register_fails.log` muestra 401 por token.
- **Fondo:** Subir JPG → Todos → Enviar.
- **Web:** Pegar URL → Abrir en clientes.
- **Archivos:** Enviar (llega a C:\ProgramData\ControlSala) / Recolectar `*.docx` (llega a `uploads/collected/`).
- **Bloquear:** 🔒 BLOQUEAR TODOS mientras explicas, 🔓 para liberar.
- **Apagar:** ⏻ con 15s de aviso. Requiere que el agente corra con privilegio para shutdown (tarea con /rl highest).

## D. Migración futura a Laravel
La API ya es REST (`pcs`, `commands`) y la DB es trasladable a migraciones Eloquent. El agente no cambia.
