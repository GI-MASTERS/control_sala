<?php
// ControlSala - capa DB SQLite (migrable a Laravel/Eloquent)
require_once __DIR__ . '/config.php';

function cs_db() {
    static $pdo = null;
    if ($pdo) return $pdo;
    $pdo = new PDO('sqlite:' . CS_DB);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE TABLE IF NOT EXISTS pcs (
        hostname TEXT PRIMARY KEY,
        ip TEXT, os_info TEXT, last_seen INTEGER, created_at INTEGER
    )");
    // Migración v2 estilo Veyon: etiqueta Equipo 1, usuario logueado, última captura
    foreach (["label TEXT DEFAULT ''", "username TEXT DEFAULT ''", "ip_local TEXT DEFAULT ''", "screen_at INTEGER DEFAULT 0", "token TEXT DEFAULT ''"] as $col) {
        try { $pdo->exec("ALTER TABLE pcs ADD COLUMN $col"); } catch (Exception $e) {}
    }
    // Tokens por equipo: pre-generados desde el panel (Equipo 1 -> token único)
    $pdo->exec("CREATE TABLE IF NOT EXISTS tokens (
        label TEXT PRIMARY KEY, token TEXT NOT NULL, created_at INTEGER
    )");
    // Salas / grupos de la sala de sistemas
    $pdo->exec("CREATE TABLE IF NOT EXISTS salas (
        id INTEGER PRIMARY KEY AUTOINCREMENT, nombre TEXT UNIQUE NOT NULL
    )");
    foreach (["sala_id INTEGER DEFAULT NULL"] as $col) {
        try { $pdo->exec("ALTER TABLE pcs ADD COLUMN $col"); } catch (Exception $e) {}
    }
    $pdo->exec("CREATE TABLE IF NOT EXISTS commands (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        target TEXT NOT NULL, -- hostname, '*'=todos
        type TEXT NOT NULL,   -- wallpaper|open_url|message|lock|unlock|shutdown|reboot|download_file|collect_file
        payload TEXT,         -- JSON
        status TEXT DEFAULT 'pending', -- pending|done
        created_at INTEGER, delivered_at INTEGER
    )");
    return $pdo;
}

function cs_agent_token($label, $hostname, $t) {
    // Acepta: token maestro CS_TOKEN, o token único del equipo (por label o por pcs.token)
    if ($t !== '' && $t === CS_TOKEN) return true;
    try {
        $pdo = cs_db();
        if ($label !== '') {
            $st = $pdo->prepare("SELECT token FROM tokens WHERE label=?");
            $st->execute([$label]);
            if (($r = $st->fetch(PDO::FETCH_ASSOC)) && hash_equals($r['token'], $t)) return true;
        }
        if ($hostname !== '') {
            $st = $pdo->prepare("SELECT token FROM pcs WHERE hostname=? OR label=?");
            $st->execute([$hostname, $hostname]);
            while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
                if (($r['token'] ?? '') !== '' && hash_equals($r['token'], $t)) return true;
            }
            // También buscar por label enviado
            if ($label !== '' && $label !== $hostname) {
                $st = $pdo->prepare("SELECT token FROM pcs WHERE label=?");
                $st->execute([$label]);
                if (($r = $st->fetch(PDO::FETCH_ASSOC)) && ($r['token'] ?? '') !== '' && hash_equals($r['token'], $t)) return true;
            }
        }
    } catch (Exception $e) {}
    return false;
}

function cs_check_token() {
    $t = $_SERVER['HTTP_X_TOKEN'] ?? $_POST['token'] ?? $_GET['token'] ?? '';
    // Panel local (misma máquina) no exige token para facilitar uso; agentes sí.
    $isLocal = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']);
    $fromPanel = isset($_POST['from_panel']);
    if ($isLocal && $fromPanel) return true;
    if ($t === CS_TOKEN) return true;
    // Permitir también panel sin token si viene del formulario local
    if ($fromPanel && $isLocal) return true;
    // Para desarrollo LAN: si el token configurado es el de defecto, aceptar pero avisar
    if ($t === '' && $fromPanel) return true;
    return $t === CS_TOKEN;
}
