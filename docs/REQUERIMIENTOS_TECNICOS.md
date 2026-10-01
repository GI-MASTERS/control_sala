# ControlSala · Requerimientos técnicos para producción

Versión del sistema: v2 (servidor PHP + SQLite + agente Python). Red: LAN / misma VLAN, sin dependencia de internet para operar.

## 1. Servidor

| Recurso | Mínimo | Recomendado |
|---|---|---|
| SO | Windows 10/11 o Linux | PC dedicado o mini-PC siempre encendido |
| PHP | 8.1+ con `pdo_sqlite`, `zip` | PHP 8.2 (probado) |
| Servidor web | PHP built-in (`php -S 0.0.0.0:8000`) | Apache (XAMPP) o Nginx como servicio |
| RAM / Disco | 2 GB / 10 GB libres | 4 GB / 20 GB (capturas y recolectados crecen) |
| Red | IP fija en la VLAN | Reserva DHCP + nombre DNS interno |

Archivos que deben existir y con permiso de escritura para el usuario del servidor web: `data.db`, `uploads/screens/`, `uploads/collected/`, `uploads/wallpapers/`, `uploads/files/`, `uploads/wheels/`.

## 2. Red y firewall

* Puerto TCP **8000** abierto en el servidor **solo para la VLAN de la sala** (perfil privado/dominio). No exponer a internet ni a la red administrativa.
* Clientes → servidor: HTTP saliente al puerto 8000. El agente nunca abre puertos entrantes (sondeo por polling cada 5 s, heartbeat 10 s, captura 8 s).
* Tráfico estimado por PC: ~30 KB por captura + JSON de sondeo. 30 PCs ≈ 150 KB/s sostenidos: despreciable en Fast/Gigabit Ethernet.
* Reloj sincronizado (NTP) en servidor y clientes: el estado online/offline se calcula con `last_seen` (umbral `CS_ONLINE_SECS` = 60 s en `config.php`).

## 3. Clientes (Windows 10/11)

* Python 3.11 de 64 bits (`py -3` o `python` en PATH). El `instalar.bat` del ZIP lo instala solo vía `winget` si falta; sin internet, preinstalar desde `python.org` (marcar *Add to PATH*).
* Dependencias `requests` + `pillow`: incluidas offline en `wheels/` del ZIP (`pip install --no-index --find-links wheels`).
* Tarea programada `ControlSala` (al iniciar sesión, privilegio máximo) para `shutdown` remoto y persistencia. Verificar con `schtasks /query /tn ControlSala`.
* Rutas de trabajo en `C:\ProgramData\ControlSala` (fuera de Documentos) para no chocar con *Acceso controlado a carpetas*. Si el antivirus bloquea `pythonw.exe`, crear exclusión a la carpeta del agente.
* Desactivar el alias de Microsoft Store (`Configuración → Apps → App execution aliases → python.exe OFF`) si interfiere.

## 4. Seguridad mínima antes de producir

1. Cambiar `CS_TOKEN` en `config.php` (token maestro de respaldo).
2. Generar un token único por equipo desde el panel (módulo Agentes) y desplegar cada ZIP en su PC. Regenerar revoca el anterior.
3. Servir el panel por HTTPS si la red lo permite (certificado interno/mkcert); si queda en HTTP, aceptar solo dentro de la VLAN.
4. Copia de respaldo diaria de `data.db` (contiene inventario y tokens) + `uploads/collected/`.
5. No publicar el repositorio con `data.db` (está en `.gitignore`); versionar solo código + `database/schema.sql`.

## 5. Puesta en producción (orden)

1. Fijar IP del servidor y actualizar `CS_PUBLIC_BASE` en `config.php`.
2. Levantar el servidor como servicio (Apache/Nginx) y abrir el firewall TCP 8000 a la VLAN.
3. Crear las salas en el panel (*Salas y grupos*).
4. Por cada PC: generar token → descargar ZIP → `instalar.bat` como administrador → `diagnostico.bat` (los 7 puntos en OK) → aparece EN LÍNEA.
5. Probar en un equipo piloto: captura en vivo, bloquear/desbloquear, mensaje, fondo, apagar con retardo.
6. Respaldar `data.db` y dejar programada la copia diaria.

## 6. Mantenimiento

* Purgar `uploads/screens/` (se sobrescriben solas) y archivar `uploads/collected/` por periodo.
* Revisar comandos `pending` antiguos en *Historial* (equipos apagados los ejecutarán al encenderse).
* Al reinstalar un PC: revocar/generar token nuevo y reasignar sala (la etiqueta del panel prevalece sobre la del agente).
