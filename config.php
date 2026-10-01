<?php
// ControlSala - configuración central
define('CS_TOKEN', 'sala2026-cambia-esto'); // <-- CAMBIA esto y cópialo en agente.py
define('CS_DB', __DIR__ . '/data.db');
define('CS_DIR_UPLOADS', __DIR__ . '/uploads');
define('CS_DIR_WALLPAPERS', __DIR__ . '/uploads/wallpapers');
define('CS_DIR_FILES', __DIR__ . '/uploads/files');
define('CS_DIR_COLLECTED', __DIR__ . '/uploads/collected');
define('CS_DIR_SCREENS', __DIR__ . '/uploads/screens');
// IP LAN del servidor para inyectar en los agentes (evita que quede 127.0.0.1).
// Si cambias de red, actualiza esta IP.
define('CS_PUBLIC_BASE', 'http://192.168.0.220:8000');
define('CS_ONLINE_SECS', 60); // verde si heartbeat < 60s (tolerancia a jitter)

foreach ([CS_DIR_UPLOADS, CS_DIR_WALLPAPERS, CS_DIR_FILES, CS_DIR_COLLECTED, CS_DIR_SCREENS] as $d) {
    if (!is_dir($d)) @mkdir($d, 0777, true);
}
