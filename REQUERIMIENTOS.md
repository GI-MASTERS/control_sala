# ControlSala — Requerimientos para implementación (v1.0 propia)

**Sistema:** Win11, misma VLAN red local. Servidor XAMPP + Agente Python.
**Fecha:** 2026-09-30
**Carpeta:** `C:\xampp\controlsala`

## 1. Objetivo
Cliente-servidor propio para controlar sala de sistemas desde un menú de administración central.

## 2. Requerimientos funcionales (RF)

| ID | Requerimiento | Detalle | Estado |
|----|---------------|---------|--------|
| RF1 | Cambiar fondo de pantalla desde admin | Individual / todos. Subir JPG/PNG en panel, se distribuye y aplica con `SystemParametersInfoW` + registro `WallpaperStyle=10 Fill` | Implementado v1 |
| RF2 | Enviar página web a clientes | Escribir URL en panel, se abre navegador en todos/seleccionados | Implementado v1 |
| RF3a | Enviar archivos admin → usuarios | Subir archivo en panel, clientes lo descargan a `Documentos/ControlSala/` y opcional auto-abrir | Implementado v1 |
| RF3b | Recibir archivos usuarios → admin | Orden `Recolectar` + máscara (ej `*.docx`), cliente sube a `collected/` del servidor | Implementado v1 |
| RF4 | Apagar / Reiniciar equipos | Apagar ahora o con retardo, reiniciar. Individual/todos. Confirma en panel | Implementado v1 |
| RF5 | Bloquear equipos durante clase | `Bloquear`: pantalla completa negra “CLASE EN CURSO” topmost (bloqueo lógico, Alt+F4/Ctrl deshabilitados a nivel app). `Desbloquear` libera. + `Mensaje` emergente | Implementado v1 |
| RF6 | Ver estado online/offline | Heartbeat cada 10s agente → servidor. Panel semáforo verde (<30s) / gris | Implementado v1 |
| RF7 | Registro e historial | Log de comandos enviados/entregados/ejecutados | Implementado v1 |

## 3. Requerimientos no funcionales
- RNF1: 100% LAN, sin internet obligatorio. Servidor: `http://IP_SERVIDOR/controlsala` o `http://IP:8000`.
- RNF2: Win11 cliente sin dominio necesario. Agente Python 3.11+ con `requests`.
- RNF3: Token compartido `X-Token` configurable en `config.php` y `agente.py`.
- RNF4: Polling HTTP (no puertos entrantes en cliente, evita firewall). Intervalo 5s.
- RNF5: Estructura migrable a Laravel (api REST + tabla pcs/commands ya diseñada para Eloquent).

## 4. Arquitectura v1
```
[Panel Admin PHP] <-> [SQLite pcs/commands] <-> [api.php REST]
        ^                                              ^
     humano                                     polling HTTP
                                                        v
                                              [agente.py en cada PC]
                                              ejecuta: wallpaper, url,
                                              archivos, lock, shutdown
```

## 5. Alcance futuro (v2)
- Ver pantalla en vivo (integrar Veyon o screenshot periódico).
- Encender por Wake-on-LAN, LDAP/AD, roles profesor/técnico.
- Migrar servidor a Laravel 11 + Reverb websockets para tiempo real <1s.
