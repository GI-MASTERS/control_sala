<?php
// ControlSala API v2 estilo Veyon: register, heartbeat, poll, ack, screenshot, screen, list_pcs, rename_pc, send_command...
if (($_GET['action'] ?? '') === 'screen') {
    // Servir última captura (la usa el <img> del panel, sin JSON)
    require_once __DIR__ . '/config.php';
    $h = preg_replace('/[^A-Za-z0-9-_]/', '_', $_GET['hostname'] ?? '');
    $f = CS_DIR_SCREENS . '/' . $h . '.jpg';
    if ($h && is_file($f)) { header('Content-Type: image/jpeg'); header('Cache-Control: no-cache'); readfile($f); exit; }
    header('HTTP/1.1 404 Not Found'); exit;
}
if (($_GET['action'] ?? '') === 'descargar_agente') {
    // ZIP del agente ya configurado con SERVIDOR + TOKEN ÚNICO del equipo + ETIQUETA. Sin tocar código.
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/db.php';
    $label = trim($_GET['label'] ?? 'Equipo 1');
    if ($label === '') $label = 'Equipo 1';
    $pdo2 = cs_db();
    // Buscar o generar token único para este equipo (formato E01-XXXXXX)
    $st = $pdo2->prepare("SELECT token FROM tokens WHERE label=?");
    $st->execute([$label]);
    $tok = ($st->fetch(PDO::FETCH_ASSOC)['token'] ?? '');
    if ($tok === '') {
        $num = preg_replace('/\D/', '', $label); $num = str_pad(substr($num, -2), 2, '0', STR_PAD_LEFT);
        $tok = 'E' . $num . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        $pdo2->prepare("INSERT INTO tokens(label,token,created_at) VALUES(?,?,?) ON CONFLICT(label) DO UPDATE SET token=excluded.token")->execute([$label, $tok, time()]);
    }
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    // Si se descargó vía localhost, forzar la IP LAN pública para que el cliente no apunte a sí mismo
    if (strpos($host, '127.0.0.1') !== false || stripos($host, 'localhost') !== false) {
        $servidor = CS_PUBLIC_BASE;
    } else {
        $servidor = $proto . '://' . $host . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    }
    $tpl = @file_get_contents(__DIR__ . '/client/agente.py');
    if (!$tpl) { http_response_code(500); exit('falta client/agente.py'); }
    // Inyectar valores (no-greedy para no comerse comentarios con comillas)
    $tpl = preg_replace('/^SERVIDOR = ".*?"/m', 'SERVIDOR = "' . addslashes($servidor) . '"', $tpl);
    $tpl = preg_replace('/^TOKEN = ".*?"/m', 'TOKEN = "' . addslashes($tok) . '"', $tpl);
    $tpl = preg_replace('/^ETIQUETA = ".*?"/m', 'ETIQUETA = "' . addslashes($label) . '"', $tpl);
    $req = @file_get_contents(__DIR__ . '/client/requirements.txt') ?: "requests>=2.31\npillow>=10\n";
    $safe = preg_replace('/[^A-Za-z0-9-_]/', '_', $label) ?: 'agente';
    // Instalador TODO-INCLUIDO: instala Python si falta (winget), luego deps OFFLINE desde wheels/
    $bat = "@echo off\r\nTITLE ControlSala - instalar $label\r\ncd /d \"%~dp0\"\r\n"
        . "echo === ControlSala $label ===\r\necho Servidor: $servidor\r\necho.\r\n"
        . "set PY=NONE\r\n"
        . "py -3 --version >nul 2>&1 && set PY=py -3\r\n"
        . "if \"%PY%\"==\"NONE\" python --version >nul 2>&1 && set PY=python\r\n"
        . "if \"%PY%\"==\"NONE\" (\r\n"
        . "  echo Python real no detectado (solo alias de Store). Instalando Python 3.11 con winget...\r\n"
        . "  winget install --id Python.Python.3.11 -e --silent --accept-package-agreements --accept-source-agreements\r\n"
        . "  set \"PATH=%LOCALAPPDATA%\\Programs\\Python\\Python311;%LOCALAPPDATA%\\Programs\\Python\\Python311\\Scripts;%PATH%\"\r\n"
        . "  py -3 --version >nul 2>&1 && set PY=py -3\r\n"
        . "  if \"%PY%\"==\"NONE\" python --version >nul 2>&1 && set PY=python\r\n"
        . ")\r\n"
        . "if \"%PY%\"==\"NONE\" (echo ERROR: desactiva el alias en Configuracion - Apps - App execution aliases -python.exe- o instala manual https://www.python.org/downloads/ (marca Add to PATH). & pause & exit /b 1)\r\n"
        . "echo Python OK: & %PY% --version\r\n"
        . "if errorlevel 1 (echo ERROR: quedo el alias de Store. Desactivalo en App execution aliases y reejecuta. & pause & exit /b 1)\r\n"
        . "echo Instalando dependencias OFFLINE...\r\n"
        . "%PY% -m pip install --no-index --find-links wheels -r requirements.txt\r\n"
        . "if errorlevel 1 (echo Reintentando ONLINE... & %PY% -m pip install -r requirements.txt)\r\n"
        . "echo Creando tarea de inicio automatico...\r\n"
        . "schtasks /create /tn \"ControlSala\" /tr \"pythonw \\\"%~dp0agente.py\\\"\" /sc onlogon /rl highest /f\r\n"
        . "echo.\r\necho Listo. Equipo: $label.Probando conexion...\r\n"
        . "%PY% \"%~dp0agente.py\" --probar\r\n"
        . "echo Si dice OK aparece en el panel. Cierra y reinicia sesion.\r\npause\r\n";
    $leeme = "ControlSala - $label\r\nServidor: $servidor\r\nToken unico: $tok (ya inyectado, no lo edites)\r\n"
        . "TODO INCLUIDO: no necesitas instalar nada manual.\r\n"
        . "1. Doble clic instalar.bat (como Administrador). Si falta Python lo instala solo con winget.\r\n"
        . "2. Las dependencias (requests, pillow) van en carpeta wheels/ OFFLINE.\r\n"
        . "3. Aparece en el panel como $label.\r\n";
    $zip = new ZipArchive();
    $tmp = tempnam(sys_get_temp_dir(), 'cs') . '.zip';
    $zip->open($tmp, ZipArchive::CREATE);
    $zip->addFromString('agente.py', $tpl);
    $zip->addFromString('equipo.txt', $label . "\n");
    $zip->addFromString('requirements.txt', $req);
    $zip->addFromString('instalar.bat', $bat);
    $zip->addFromString('LEEME.txt', $leeme);
    $diag = "@echo off\r\nTITLE Diagnostico $label\r\ncd /d \"%~dp0\"\r\n"
        . "echo === Diagnostico $label ===\r\n"
        . "echo [1] Python real...\r\npy -3 --version 2>&1\r\npython --version 2>&1\r\necho.\r\n"
        . "echo [2] Librerias...\r\npython -c \"import requests; print('requests OK', requests.__version__)\" 2>&1\r\npython -c \"import PIL; print('pillow OK', PIL.__version__)\" 2>&1\r\necho.\r\n"
        . "echo [3] Config del agente...\r\nfindstr /R \"^SERVIDOR ^TOKEN ^ETIQUETA\" agente.py\r\necho.\r\n"
        . "echo [4] Red hacia servidor...\r\ncurl -s -m 10 \"$servidor/api.php?action=list_pcs\"\r\necho.\r\necho.\r\n"
        . "echo [5] Registro de prueba...\r\npython agente.py --probar 2>&1\r\necho.\r\n"
        . "echo [6] Tarea programada...\r\nschtasks /query /tn ControlSala 2>&1\r\necho.\r\n"
        . "echo [7] Ultimas lineas de agente.log...\r\nif exist agente.log (powershell -c \"Get-Content agente.log -Tail 15\") else (echo sin agente.log: el agente nunca arranco)\r\n"
        . "echo.\r\npause\r\n";
    $zip->addFromString('diagnostico.bat', $diag);
    // Dependencias offline
    foreach (glob(CS_DIR_UPLOADS . '/wheels/*.whl') as $wh) {
        $zip->addFile($wh, 'wheels/' . basename($wh));
    }
    $zip->close();
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="ControlSala-' . $safe . '.zip"');
    header('Content-Length: ' . filesize($tmp));
    readfile($tmp); @unlink($tmp); @unlink(str_replace('.zip', '', $tmp)); exit;
}
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: X-Token, Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
require_once __DIR__ . '/db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$pdo = cs_db();
$now = time();

function out($a) { echo json_encode($a, JSON_UNESCAPED_UNICODE); exit; }
function safe_h($h) { return preg_replace('/[^A-Za-z0-9-_]/', '_', trim($h)); }

// --- Agente: registro / heartbeat (con identificación Equipo + IP + usuario) ---
if ($action === 'register' || $action === 'heartbeat') {
    $h = trim($_POST['hostname'] ?? $_GET['hostname'] ?? 'DESCONOCIDO');
    $label = trim($_POST['label'] ?? '');
    $t = $_SERVER['HTTP_X_TOKEN'] ?? $_POST['token'] ?? $_GET['token'] ?? '';
    if (!cs_agent_token($label !== '' ? $label : $h, $h, $t)) { http_response_code(401); out(['ok'=>false,'error'=>'token inválido']); }
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $os = trim($_POST['os_info'] ?? '');
    $user = trim($_POST['username'] ?? '');
    $ipLocal = trim($_POST['ip_local'] ?? '');
    $st = $pdo->prepare("INSERT INTO pcs(hostname,ip,os_info,last_seen,created_at,label,username,ip_local,screen_at)
        VALUES(?,?,?,?,?,?,?,?,?)
        ON CONFLICT(hostname) DO UPDATE SET ip=excluded.ip, os_info=excluded.os_info, last_seen=excluded.last_seen,
        label=CASE WHEN pcs.label='' THEN excluded.label ELSE pcs.label END,
        username=excluded.username, ip_local=excluded.ip_local");
    // screen_at se conserva: leer previo
    $prev = $pdo->prepare("SELECT screen_at,label FROM pcs WHERE hostname=?");
    $prev->execute([$h]); $row = $prev->fetch(PDO::FETCH_ASSOC);
    $screenAt = intval($row['screen_at'] ?? 0);
    $labelFinal = $label !== '' ? $label : ($row['label'] ?? '');
    $st->execute([$h, $ip, $os, $now, $now, $labelFinal, $user, $ipLocal, $screenAt]);
    // Guardar token único del equipo para futuras validaciones
    try { $pdo->prepare("UPDATE pcs SET token=? WHERE hostname=?")->execute([$t, $h]); } catch (Exception $e) {}
    out(['ok'=>true, 'server_time'=>$now]);
}

// --- Agente: subir captura de pantalla (miniatura en vivo) ---
if ($action === 'screenshot') {
    $hh = trim($_POST['hostname'] ?? ''); $tt = $_SERVER['HTTP_X_TOKEN'] ?? $_POST['token'] ?? '';
    $stL = $pdo->prepare("SELECT label FROM pcs WHERE hostname=?"); $stL->execute([$hh]);
    $lb = ($stL->fetch(PDO::FETCH_ASSOC)['label'] ?? $hh);
    if (!cs_agent_token($lb, $hh, $tt)) { http_response_code(401); out(['ok'=>false,'error'=>'token inválido']); }
    $h = safe_h($_POST['hostname'] ?? '');
    if (!$h || empty($_FILES['shot']['tmp_name'])) out(['ok'=>false,'error'=>'sin imagen']);
    move_uploaded_file($_FILES['shot']['tmp_name'], CS_DIR_SCREENS . '/' . $h . '.jpg');
    $pdo->prepare("UPDATE pcs SET last_seen=?, screen_at=? WHERE hostname=?")->execute([$now, $now, trim($_POST['hostname'] ?? '')]);
    out(['ok'=>true]);
}

// --- Agente: pedir comandos pendientes ---
if ($action === 'poll') {
    $h = trim($_GET['hostname'] ?? $_POST['hostname'] ?? '');
    $tt = $_SERVER['HTTP_X_TOKEN'] ?? $_POST['token'] ?? $_GET['token'] ?? '';
    $stL = $pdo->prepare("SELECT label FROM pcs WHERE hostname=?"); $stL->execute([$h]);
    $lb = ($stL->fetch(PDO::FETCH_ASSOC)['label'] ?? $h);
    if (!cs_agent_token($lb !== '' ? $lb : $h, $h, $tt)) { http_response_code(401); out(['ok'=>false,'error'=>'token inválido']); }
    $st = $pdo->prepare("SELECT * FROM commands WHERE status='pending' AND (target='*' OR target=?) ORDER BY id ASC LIMIT 10");
    $st->execute([$h]);
    out(['ok'=>true, 'commands'=>$st->fetchAll(PDO::FETCH_ASSOC)]);
}

// --- Agente: confirmar ejecución ---
if ($action === 'ack') {
    $h = trim($_POST['hostname'] ?? '');
    $tt = $_SERVER['HTTP_X_TOKEN'] ?? $_POST['token'] ?? '';
    $stL = $pdo->prepare("SELECT label FROM pcs WHERE hostname=?"); $stL->execute([$h]);
    $lb = ($stL->fetch(PDO::FETCH_ASSOC)['label'] ?? $h);
    if (!cs_agent_token($lb !== '' ? $lb : $h, $h, $tt)) { http_response_code(401); out(['ok'=>false,'error'=>'token inválido']); }
    $id = intval($_POST['command_id'] ?? 0);
    $pdo->prepare("UPDATE commands SET status='done', delivered_at=? WHERE id=?")->execute([$now, $id]);
    if ($h) $pdo->prepare("UPDATE pcs SET last_seen=? WHERE hostname=?")->execute([$now, $h]);
    out(['ok'=>true]);
}

// --- Agente: subir archivo recolectado ---
if ($action === 'upload_collected') {
    $hh = trim($_POST['hostname'] ?? 'PC');
    $tt = $_SERVER['HTTP_X_TOKEN'] ?? $_POST['token'] ?? '';
    $stL = $pdo->prepare("SELECT label FROM pcs WHERE hostname=?"); $stL->execute([$hh]);
    $lb = ($stL->fetch(PDO::FETCH_ASSOC)['label'] ?? $hh);
    if (!cs_agent_token($lb !== '' ? $lb : $hh, $hh, $tt)) { http_response_code(401); out(['ok'=>false,'error'=>'token inválido']); }
    $h = safe_h($_POST['hostname'] ?? 'PC');
    if (empty($_FILES['file'])) out(['ok'=>false,'error'=>'sin archivo']);
    $name = basename($_FILES['file']['name']);
    $dest = CS_DIR_COLLECTED . '/' . date('Ymd-His') . '_' . $h . '_' . $name;
    move_uploaded_file($_FILES['file']['tmp_name'], $dest);
    out(['ok'=>true, 'saved'=>basename($dest)]);
}

// --- Panel: listar PCs con estado + pantalla ---
if ($action === 'list_pcs') {
    $rows = $pdo->query("SELECT p.*, s.nombre AS sala FROM pcs p LEFT JOIN salas s ON s.id=p.sala_id ORDER BY COALESCE(NULLIF(p.label,''),p.hostname)")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) {
        $r['online'] = ($now - intval($r['last_seen'])) < CS_ONLINE_SECS;
        $r['screen_age'] = $now - intval($r['screen_at'] ?? 0);
        $r['has_screen'] = is_file(CS_DIR_SCREENS . '/' . safe_h($r['hostname']) . '.jpg');
        $r['display'] = $r['label'] !== '' ? $r['label'] : $r['hostname'];
    }
    out(['ok'=>true, 'pcs'=>$rows, 'now'=>$now]);
}

// --- Panel: renombrar Equipo 1, Equipo 2... ---
if ($action === 'rename_pc') {
    $h = trim($_POST['hostname'] ?? ''); $label = trim($_POST['label'] ?? '');
    $pdo->prepare("UPDATE pcs SET label=? WHERE hostname=?")->execute([$label, $h]);
    // API fetch o formulario
    if (empty($_POST['from_panel'])) out(['ok'=>true]);
    header('Location: index.php'); exit;
}

// --- Panel: generar / listar / revocar token único por equipo ---
if ($action === 'generar_token') {
    $label = trim($_POST['label'] ?? $_GET['label'] ?? '');
    if ($label === '') out(['ok'=>false,'error'=>'indica Equipo 1, Equipo 2...']);
    $num = preg_replace('/\D/', '', $label); $num = str_pad(substr($num, -2), 2, '0', STR_PAD_LEFT);
    $tok = 'E' . $num . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    $pdo->prepare("INSERT INTO tokens(label,token,created_at) VALUES(?,?,?) ON CONFLICT(label) DO UPDATE SET token=excluded.token,created_at=excluded.created_at")->execute([$label, $tok, $now]);
    out(['ok'=>true, 'label'=>$label, 'token'=>$tok]);
}
if ($action === 'listar_tokens') {
    $rows = $pdo->query("SELECT label,token,created_at FROM tokens ORDER BY label")->fetchAll(PDO::FETCH_ASSOC);
    out(['ok'=>true, 'tokens'=>$rows]);
}
if ($action === 'revocar_token') {
    $label = trim($_POST['label'] ?? '');
    $pdo->prepare("DELETE FROM tokens WHERE label=?")->execute([$label]);
    out(['ok'=>true]);
}

// --- Panel: salas / grupos ---
if ($action === 'crear_sala') {
    $nombre = trim($_POST['nombre'] ?? '');
    if ($nombre === '') out(['ok'=>false,'error'=>'nombre vacío']);
    try {
        $pdo->prepare("INSERT INTO salas(nombre) VALUES(?)")->execute([$nombre]);
        out(['ok'=>true, 'id'=>$pdo->lastInsertId()]);
    } catch (Exception $e) { out(['ok'=>false,'error'=>'esa sala ya existe']); }
}
if ($action === 'listar_salas') {
    $rows = $pdo->query("SELECT s.id, s.nombre, COUNT(p.hostname) AS equipos FROM salas s LEFT JOIN pcs p ON p.sala_id=s.id GROUP BY s.id ORDER BY s.nombre")->fetchAll(PDO::FETCH_ASSOC);
    out(['ok'=>true, 'salas'=>$rows]);
}
if ($action === 'eliminar_sala') {
    $id = intval($_POST['id'] ?? 0);
    $pdo->prepare("UPDATE pcs SET sala_id=NULL WHERE sala_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM salas WHERE id=?")->execute([$id]);
    out(['ok'=>true]);
}
if ($action === 'asignar_sala') {
    $h = trim($_POST['hostname'] ?? '');
    $sid = trim($_POST['sala_id'] ?? '');
    $sid = ($sid === '' || $sid === '0') ? null : intval($sid);
    $pdo->prepare("UPDATE pcs SET sala_id=? WHERE hostname=?")->execute([$sid, $h]);
    out(['ok'=>true]);
}
// --- Panel: eliminar equipo registrado ---
if ($action === 'eliminar_pc') {
    $h = trim($_POST['hostname'] ?? '');
    if ($h === '') out(['ok'=>false,'error'=>'sin equipo']);
    $pdo->prepare("DELETE FROM commands WHERE target=?")->execute([$h]);
    $pdo->prepare("DELETE FROM pcs WHERE hostname=?")->execute([$h]);
    @unlink(CS_DIR_SCREENS . '/' . safe_h($h) . '.jpg');
    out(['ok'=>true]);
}

// --- Panel: historial ---
if ($action === 'list_commands') {
    $rows = $pdo->query("SELECT * FROM commands ORDER BY id DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
    out(['ok'=>true, 'commands'=>$rows]);
}

// --- Panel: enviar comando (funciones tipo Veyon) ---
if ($action === 'send_command') {
    $type = trim($_POST['cmd_type'] ?? '');
    $target = trim($_POST['target'] ?? '*');
    $payload = [];
    $valid = ['wallpaper','open_url','message','lock','unlock','shutdown','reboot','download_file','collect_file','screenshot_now','run_app'];
    if (!in_array($type, $valid)) out(['ok'=>false,'error'=>'tipo inválido']);

    if ($type === 'wallpaper' && !empty($_FILES['wallpaper']['tmp_name'])) {
        $ext = strtolower(pathinfo($_FILES['wallpaper']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','bmp'])) out(['ok'=>false,'error'=>'solo jpg/png/bmp']);
        $fname = 'fondo_' . date('Ymd-His') . '.' . $ext;
        move_uploaded_file($_FILES['wallpaper']['tmp_name'], CS_DIR_WALLPAPERS . '/' . $fname);
        $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $base = $proto . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        $payload = ['file_url' => $base . '/uploads/wallpapers/' . $fname, 'filename' => $fname];
    } elseif ($type === 'download_file' && !empty($_FILES['afile']['tmp_name'])) {
        $fname = basename($_FILES['afile']['name']);
        move_uploaded_file($_FILES['afile']['tmp_name'], CS_DIR_FILES . '/' . $fname);
        $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $base = $proto . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        $payload = ['file_url' => $base . '/uploads/files/' . rawurlencode($fname),
                    'filename' => $fname, 'open_after' => isset($_POST['open_after'])];
    } else {
        $payload = [
            'url' => trim($_POST['url'] ?? ''),
            'text' => trim($_POST['text'] ?? ''),
            'app' => trim($_POST['app'] ?? 'notepad.exe'),
            'delay' => intval($_POST['delay'] ?? 15),
            'pattern' => trim($_POST['pattern'] ?? '*.docx;*.pdf'),
        ];
    }
    $st = $pdo->prepare("INSERT INTO commands(target,type,payload,status,created_at) VALUES(?,?,?,?,?)");
    $st->execute([$target, $type, json_encode($payload, JSON_UNESCAPED_UNICODE), 'pending', $now]);
    if (empty($_POST['from_panel'])) out(['ok'=>true, 'id'=>$pdo->lastInsertId()]);
    header('Location: index.php?sent=1');
    exit;
}

out(['ok'=>false,'error'=>'acción desconocida']);
