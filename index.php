<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>ControlSala · Consola de administración</title>
<style>
:root{--bg:#eef1f5;--card:#fff;--line:#e2e8f0;--ink:#0f172a;--mut:#64748b;--acc:#1d4ed8;--acc-d:#1e40af;--ok:#15803d;--ok-bg:#dcfce7;--bad:#b91c1c;--bad-bg:#fee2e2;--warn:#b45309;--warn-bg:#fef3c7;--side:#0c1424;--side2:#111c33}
*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--ink);font:14px/1.45 "Inter","Segoe UI",Arial,sans-serif}
a{color:inherit}.app{display:flex;min-height:100vh}
/* Sidebar */
.side{width:248px;background:var(--side);color:#cbd5e1;display:flex;flex-direction:column;position:sticky;top:0;height:100vh;flex-shrink:0}
.brand{padding:18px;border-bottom:1px solid #1e293b}.brand b{color:#fff;font-size:16px;letter-spacing:.2px;display:block}.brand span{font-size:11px;color:#7d8aa5;text-transform:uppercase;letter-spacing:1.2px}
.nav{padding:12px;display:flex;flex-direction:column;gap:2px}.nav a{text-decoration:none;padding:9px 12px;border-radius:8px;color:#cbd5e1;font-weight:600;font-size:13.5px;display:flex;gap:10px;align-items:center}.nav a.on,.nav a:hover{background:var(--side2);color:#fff}
.side .foot{margin-top:auto;padding:14px;border-top:1px solid #1e293b;font-size:12px;color:#7d8aa5}.pill{display:inline-block;padding:2px 8px;border-radius:99px;font-size:11px;font-weight:700}
/* Main */
.main{flex:1;min-width:0}.top{position:sticky;top:0;z-index:10;background:rgba(255,255,255,.92);backdrop-filter:blur(6px);border-bottom:1px solid var(--line);padding:12px 22px;display:flex;align-items:center;gap:14px}
.top h1{font-size:17px;margin:0}.top h1 small{color:var(--mut);font-weight:500}.top .sp{flex:1}.meta{font-size:12.5px;color:var(--mut)}
.btn{--b:#fff;--bd:#cbd5e1;--t:#0f172a;border:1px solid var(--bd);background:var(--b);color:var(--t);padding:0 13px;height:34px;border-radius:8px;font-weight:650;cursor:pointer;font-size:13px;display:inline-flex;align-items:center;justify-content:center;gap:7px;white-space:nowrap;box-shadow:0 1px 2px rgba(15,23,42,.06);transition:background .12s,border-color .12s,box-shadow .12s,transform .05s}.btn:hover{border-color:#94a3b8;background:#f8fafc}.btn:active{transform:translateY(1px)}.btn:focus-visible{outline:2px solid #93c5fd;outline-offset:2px}.btn svg{width:15px;height:15px;flex-shrink:0}.btn.sm{height:29px;padding:0 10px;font-size:12.5px;border-radius:7px}.btn.block{width:100%;height:37px;font-size:13.5px}.btn.pri{--b:var(--acc);--bd:var(--acc);--t:#fff}.btn.pri:hover{background:var(--acc-d);border-color:var(--acc-d)}.btn.dan-solid{--b:#dc2626;--bd:#dc2626;--t:#fff}.btn.dan-solid:hover{background:#b91c1c;border-color:#b91c1c}.btn.dan{--b:#fff;--bd:#fecaca;--t:var(--bad)}.btn.dan:hover{background:#fef2f2;border-color:#fca5a5}.btn.okb{--b:#f0fdf4;--bd:#bbf7d0;--t:var(--ok)}.btn.okb:hover{background:#dcfce7}.btn.warnb{--b:#fffbeb;--bd:#fde68a;--t:var(--warn)}.btn.warnb:hover{background:#fef3c7}.btn.ghost{--b:transparent;--bd:transparent;--t:#475569;box-shadow:none}.btn.ghost:hover{background:#f1f5f9}
.toolbar{display:flex;gap:8px;flex-wrap:wrap;align-items:center}.toolbar .sep{width:1px;height:24px;background:var(--line)}.toolbar .grp{display:flex;gap:8px;align-items:center}.toolbar .glab{font-size:11px;font-weight:800;color:var(--mut);text-transform:uppercase;letter-spacing:.6px;margin-right:2px}
.wrap{max-width:1240px;margin:0 auto;padding:20px 22px 40px}
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:14px}@media(max-width:900px){.stats{grid-template-columns:repeat(2,1fr)}}
.stat{background:var(--card);border:1px solid var(--line);border-radius:12px;padding:13px 15px}.stat .k{font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:var(--mut);font-weight:700}.stat .v{font-size:24px;font-weight:800;margin-top:2px}.stat .s{font-size:12px;color:var(--mut)}
.card{background:var(--card);border:1px solid var(--line);border-radius:12px;margin-bottom:14px}.card .hd{padding:13px 16px;border-bottom:1px solid var(--line);display:flex;align-items:center;gap:10px}.card .hd h2{font-size:14px;margin:0}.card .hd .sub{font-size:12px;color:var(--mut)}.card .bd{padding:14px 16px}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px}@media(max-width:980px){.grid2{grid-template-columns:1fr}}
.layout{display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:14px;align-items:start}@media(max-width:1100px){.layout{grid-template-columns:1fr}}
.lateral{position:sticky;top:70px}.lateral .card{margin-bottom:12px}
.side-mod{margin:12px;padding:12px;background:#111c33;border:1px solid #1e293b;border-radius:10px;font-size:12.5px}.side-mod .sm-t{color:#fff;font-weight:800;font-size:13px}.side-mod .sm-s{color:#7d8aa5;font-size:11.5px;margin-bottom:8px}.side-mod label{font-size:10.5px;font-weight:800;color:#7d8aa5;text-transform:uppercase;letter-spacing:.6px}.side-mod input{width:100%;margin:4px 0 8px;padding:7px 9px;border-radius:7px;border:1px solid #334155;background:#0b1220;color:#fff;font-size:13px}.side-mod .sbtn{width:100%;margin-bottom:7px;padding:8px;border-radius:7px;border:1px solid #334155;background:#1e293b;color:#e2e8f0;font-weight:700;font-size:12.5px;cursor:pointer}.side-mod .sbtn:hover{background:#273349}.side-mod .sbtn.pri{background:#1d4ed8;border-color:#1d4ed8;color:#fff}.side-mod .sbtn.pri:hover{background:#1e40af}.side-mod .sm-tok{color:#94a3b8;margin-top:2px;word-break:break-all}.side-mod .sm-tok code{color:#fff;background:#0b1220;padding:1px 5px;border-radius:5px}.side-mod .sm-tok a{color:#60a5fa}.side-mod .sm-list{color:#7d8aa5;margin-top:6px;word-break:break-word}.side .foot{margin-top:12px}
/* Sala */
.sala{display:grid;grid-template-columns:repeat(auto-fill,minmax(255px,1fr));gap:12px}
.pc{border:1px solid var(--line);border-radius:11px;overflow:hidden;background:#fff;cursor:pointer;transition:border-color .12s,box-shadow .12s}.pc:hover{border-color:#93c5fd;box-shadow:0 4px 18px rgba(29,78,216,.12)}
.pc .shot{position:relative;background:#0b1220;aspect-ratio:16/9;display:block}.pc .shot img{width:100%;height:100%;object-fit:cover;display:block}
.badge{position:absolute;top:8px;left:8px;font-size:11px;font-weight:800;padding:3px 9px;border-radius:99px}.b-on{background:#16a34a;color:#fff}.b-off{background:#475569;color:#fff}
.pc .meta{padding:10px 12px}.pc .meta .t1{display:flex;justify-content:space-between;align-items:center}.pc .meta b{font-size:14px}.pc .meta .ip{font-family:Consolas,monospace;font-size:12px;color:var(--mut)}.pc .meta .row2{font-size:12px;color:var(--mut);margin-top:3px}
.pc .ops{display:grid;grid-template-columns:1fr 1fr 1fr 1fr;border-top:1px solid var(--line);background:#f8fafc}.pc .ops button{border:0;background:transparent;padding:9px 2px;font-size:11.5px;font-weight:750;color:#334155;cursor:pointer;border-right:1px solid var(--line);display:flex;flex-direction:column;align-items:center;gap:3px}.pc .ops button:last-child{border-right:0}.pc .ops button svg{width:16px;height:16px}.pc .ops button:hover{background:#eef2ff;color:var(--acc)}.pc .ops button[data-a="off"]:hover{background:#fef2f2;color:var(--bad)}.pc .ops button[data-a="lock"]:hover{background:#fffbeb;color:var(--warn)}
/* Forms */
.frm{border:1px solid var(--line);border-radius:10px;padding:12px;margin-bottom:10px;background:#fbfcfe}.frm h3{margin:0 0 8px;font-size:13px}.frm h3 span{color:var(--mut);font-weight:500}
label.lbl{font-size:11.5px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.5px}
input[type=text],input[type=number],select{width:100%;padding:8px 10px;border-radius:8px;border:1px solid #cbd5e1;background:#fff;color:var(--ink);margin:5px 0;font-size:13.5px}
input[type=file]{width:100%;font-size:13px;margin:5px 0}
.hrow{display:flex;gap:8px}.hrow>*{flex:1}
.notice{background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;padding:10px 12px;border-radius:9px;font-size:13px;margin-bottom:12px}
table{width:100%;border-collapse:collapse;font-size:12.5px}th{font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:var(--mut);text-align:left;padding:7px;border-bottom:1px solid var(--line)}td{padding:7px;border-bottom:1px solid #f1f5f9}
/* Modal */
#modal{display:none;position:fixed;inset:0;background:rgba(2,6,23,.72);z-index:60;padding:22px;overflow:auto}
#modalBox{max-width:1020px;margin:0 auto;background:#fff;border-radius:14px;overflow:hidden}
#modalBox .mhd{padding:13px 16px;border-bottom:1px solid var(--line);display:flex;align-items:center;gap:10px}
#modalImg{width:100%;background:#000;display:block;min-height:320px}
.mbar{padding:12px 16px;border-top:1px solid var(--line);display:flex;gap:8px;flex-wrap:wrap;background:#f8fafc}
code.k{font-family:Consolas,monospace;background:#f1f5f9;border:1px solid var(--line);padding:1px 6px;border-radius:6px;font-size:12px}
</style>
</head>
<body>
<div class="app">
<aside class="side">
<div class="brand"><b>ControlSala</b><span>Classroom Manager · v2</span></div>
<nav class="nav">
<a href="#sala" class="on">◧ &nbsp;Sala en vivo</a>
<a href="#salas">▦ &nbsp;Salas y grupos</a>
<a href="#contenido">▤ &nbsp;Contenido y web</a>
<a href="#archivos">🗀 &nbsp;Archivos</a>
<a href="#energia">⏻ &nbsp;Energía y apps</a>
<a href="#historia">≣ &nbsp;Historial</a>
</nav>
<div class="side-mod" id="despliegue">
<div class="sm-t">Agentes</div>
<div class="sm-s">Token único + ZIP, sin código</div>
<label>Equipo</label><input type="text" id="dlLabel" value="Equipo 1">
<button class="sbtn" onclick="generarToken()">Generar token</button>
<button class="sbtn pri" onclick="descargarAgente()">Descargar ZIP</button>
<div class="sm-tok">Token: <code id="tokShow">—</code> <a href="#" onclick="copiarToken();return false">Copiar</a></div>
<div class="sm-list" id="tokList"></div>
</div>
<div class="foot">Servidor <code class="k"><?php require_once __DIR__.'/config.php'; echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? CS_PUBLIC_BASE); ?></code><br><span style="font-size:11px">Base ZIP: <?php echo htmlspecialchars(CS_PUBLIC_BASE); ?></span><br>Red local VLAN · <span id="srv">en línea</span><br><a href="api.php?action=ping" target="_blank" style="color:#60a5fa">probar ping</a> · <a href="api.php?action=estado" target="_blank" style="color:#60a5fa">estado</a></div>
</aside>
<div class="main">
<div class="top">
<h1>Sala de sistemas <small>— monitoreo y control</small></h1><span class="sp"></span>
<span class="meta" id="reloj"></span>
<button class="btn" onclick="cargar()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-2.6-6.4M21 4v5h-5"/></svg>Actualizar</button>
</div>
<div class="wrap">
<?php if(isset($_GET['sent'])) echo '<div class="notice">Comando encolado correctamente. Los equipos lo aplican en el siguiente ciclo de sondeo (≈5 s).</div>'; ?>
<?php
require_once __DIR__.'/config.php';
$__host = $_SERVER['HTTP_HOST'] ?? '';
$__pb = CS_PUBLIC_BASE;
$__isLocal = (stripos($__host,'localhost')!==false || strpos($__host,'127.0.0.1')!==false);
if ($__isLocal && $__pb && stripos($__pb,'localhost')===false && strpos($__pb,'127.0.0.1')===false) {
  echo '<div class="notice" style="background:#fffbeb;border-color:#fde68a;color:#92400e">Estás abriendo el panel por <b>localhost</b>, pero los ZIP se generarán con <b>'.htmlspecialchars($__pb).'</b>. Para que los agentes conecten, descarga los ZIP desde <b>http://'.htmlspecialchars($_SERVER['SERVER_ADDR'] ?? 'IP_SERVIDOR').':8000</b> o actualiza CS_PUBLIC_BASE en config.php con ipconfig.</div>';
}
?>

<div class="stats">
<div class="stat"><div class="k">Equipos en línea</div><div class="v" id="stOn">0</div><div class="s" id="stTot">0 registrados</div></div>
<div class="stat"><div class="k">Pantallas activas</div><div class="v" id="stScr">0</div><div class="s">capturas &lt; 60 s</div></div>
<div class="stat"><div class="k">Comandos pendientes</div><div class="v" id="stPen">0</div><div class="s">en cola de ejecución</div></div>
<div class="stat"><div class="k">Última sincronización</div><div class="v" style="font-size:16px" id="stSync">—</div><div class="s">sondeo automático 8 s</div></div>
</div>

<div class="card" id="sala">
<div class="hd" style="flex-wrap:wrap;row-gap:10px"><h2>Sala en vivo</h2><span class="sub">Seleccione un equipo para inspección detallada.</span>
<select id="filtroSala" onchange="cargar()" style="max-width:220px;margin:0 0 0 8px"><option value="">Todas las salas</option></select>
<span style="flex:1"></span>
<div class="toolbar">
<span class="grp"><span class="glab">Clase</span>
<button class="btn warnb sm" onclick="masivo('lock')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>Bloquear</button>
<button class="btn okb sm" onclick="masivo('unlock')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 7.5-2"/></svg>Desbloquear</button>
</span><span class="sep"></span>
<span class="grp"><span class="glab">Monitoreo</span>
<button class="btn sm" onclick="masivo('screenshot_now')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>Capturas</button>
</span><span class="sep"></span>
<span class="grp"><span class="glab">Energía</span>
<button class="btn dan-solid sm" onclick="masivo('shutdown')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.4 6.6a9 9 0 1 1-12.8 0M12 2v9"/></svg>Apagar todos</button>
</span>
</div>
</div>
<div class="bd"><div class="sala" id="salaGrid"></div>
<div class="meta" id="emptyMsg" style="display:none;color:var(--mut)">Sin equipos registrados. Genere el agente en el módulo Agentes del lateral izquierdo.</div>
</div>
</div>

<div class="card" id="salas"><div class="hd"><h2>Salas y grupos</h2><span class="sub">Cree salas, asigne equipos y filtre la vista. Al eliminar una sala sus equipos pasan a Sin asignar.</span></div>
<div class="bd"><div class="toolbar">
<span class="grp"><input type="text" id="nuevaSala" placeholder="Ej. Sala 1, Laboratorio…" style="max-width:260px;margin:0"></span>
<button class="btn pri" onclick="crearSala()">Crear sala</button>
</div>
<table style="margin-top:10px"><thead><tr><th>Sala</th><th>Equipos</th><th></th></tr></thead><tbody id="salasTbl"></tbody></table>
</div></div>

<div class="grid2">
<div class="card" id="contenido"><div class="hd"><h2>Contenido y web</h2></div><div class="bd">
<div class="frm"><h3>Fondo de pantalla <span>— JPG/PNG, se aplica en Fill</span></h3>
<form action="api.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="action" value="send_command"><input type="hidden" name="from_panel" value="1"><input type="hidden" name="cmd_type" value="wallpaper">
<label class="lbl">Destino</label><select name="target" class="t"></select>
<input type="file" name="wallpaper" accept=".jpg,.jpeg,.png,.bmp" required>
<button class="btn pri block"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>Aplicar fondo</button></form></div>
<div class="frm"><h3>Abrir sitio web <span>— en el navegador del alumno</span></h3>
<form action="api.php" method="post">
<input type="hidden" name="action" value="send_command"><input type="hidden" name="from_panel" value="1"><input type="hidden" name="cmd_type" value="open_url">
<label class="lbl">Destino</label><select name="target" class="t"></select>
<input type="text" name="url" placeholder="https://aulavirtual.edu/..." required>
<button class="btn pri block"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20 15 15 0 0 1 0-20z"/></svg>Abrir en equipos</button></form></div>
<div class="frm"><h3>Mensaje <span>— ventana emergente masiva o individual</span></h3>
<form action="api.php" method="post">
<input type="hidden" name="action" value="send_command"><input type="hidden" name="from_panel" value="1"><input type="hidden" name="cmd_type" value="message"><input type="hidden" name="target" value="*">
<input type="text" name="text" placeholder="Ej.: Guarden su trabajo, quedan 5 minutos" required>
<button class="btn block"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>Enviar mensaje</button></form></div>
</div></div>

<div>
<div class="card" id="archivos"><div class="hd"><h2>Archivos</h2></div><div class="bd">
<div class="frm"><h3>Distribuir <span>— servidor → alumnos (Documentos/ControlSala)</span></h3>
<form action="api.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="action" value="send_command"><input type="hidden" name="from_panel" value="1"><input type="hidden" name="cmd_type" value="download_file">
<label class="lbl">Destino</label><select name="target" class="t"></select>
<input type="file" name="afile" required>
<label style="font-size:12.5px"><input type="checkbox" name="open_after" style="width:auto"> Abrir automáticamente al recibir</label><br><br>
<button class="btn pri block"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>Distribuir archivo</button></form></div>
<div class="frm"><h3>Recolectar <span>— alumnos → uploads/collected/</span></h3>
<form action="api.php" method="post">
<input type="hidden" name="action" value="send_command"><input type="hidden" name="from_panel" value="1"><input type="hidden" name="cmd_type" value="collect_file">
<label class="lbl">Destino</label><select name="target" class="t"></select>
<input type="text" name="pattern" value="*.docx;*.pdf">
<button class="btn block"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>Recolectar ahora</button></form></div>
</div></div>
<div class="card" id="energia"><div class="hd"><h2>Energía y aplicaciones</h2></div><div class="bd">
<form action="api.php" method="post">
<input type="hidden" name="action" value="send_command"><input type="hidden" name="from_panel" value="1">
<div class="hrow"><div><label class="lbl">Destino</label><select name="target" class="t"></select></div>
<div style="max-width:110px"><label class="lbl">Retardo s</label><input type="number" name="delay" value="15"></div></div>
<div class="toolbar" style="margin-top:8px"><button class="btn dan-solid" name="cmd_type" value="shutdown"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.4 6.6a9 9 0 1 1-12.8 0M12 2v9"/></svg>Apagar</button>
<button class="btn warnb" name="cmd_type" value="reboot"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-2.6-6.4M21 4v5h-5"/></svg>Reiniciar</button></div></form>
<form action="api.php" method="post" style="margin-top:10px">
<input type="hidden" name="action" value="send_command"><input type="hidden" name="from_panel" value="1"><input type="hidden" name="cmd_type" value="run_app">
<label class="lbl">Ejecutar programa remoto</label>
<div class="hrow"><select name="target" class="t"></select></div>
<input type="text" name="app" value="notepad.exe">
<button class="btn block"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>Ejecutar en equipos</button></form>
</div></div>
</div>
</div>

<div class="card" id="historia"><div class="hd"><h2>Historial de comandos</h2><span class="sub">Últimos 50 · pending / done</span></div>
<div class="bd"><table><thead><tr><th>ID</th><th>Destino</th><th>Tipo</th><th>Detalle</th><th>Estado</th></tr></thead><tbody id="hist"></tbody></table></div></div>
</div>
</div>
</div>

<div id="modal"><div id="modalBox">
<div class="mhd"><h2 style="margin:0;font-size:15px" id="mTitle">Equipo</h2><span style="flex:1"></span><button class="btn sm" onclick="cerrar()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>Cerrar</button></div>
<img id="modalImg" src="" alt="Pantalla del equipo">
<div class="mbar">
<div class="toolbar" style="width:100%">
<span class="grp"><span class="glab">Ver</span>
<button class="btn sm" onclick="cmdSel('screenshot_now')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>Captura</button>
<button class="btn sm" onclick="renombrar()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.8 2.8 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5z"/></svg>Renombrar</button>
</span><span class="sep"></span>
<span class="grp"><span class="glab">Control</span>
<button class="btn warnb sm" onclick="cmdSel('lock')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>Bloquear</button>
<button class="btn okb sm" onclick="cmdSel('unlock')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 7.5-2"/></svg>Desbloquear</button>
<button class="btn sm" onclick="msgSel()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>Mensaje</button>
<button class="btn sm" onclick="webSel()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20 15 15 0 0 1 0-20z"/></svg>Web</button>
</span><span class="sep"></span>
<span class="grp"><span class="glab">Energía</span>
<button class="btn dan-solid sm" onclick="cmdSel('shutdown')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.4 6.6a9 9 0 1 1-12.8 0M12 2v9"/></svg>Apagar</button>
<button class="btn warnb sm" onclick="cmdSel('reboot')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-2.6-6.4M21 4v5h-5"/></svg>Reiniciar</button>
</span><span class="sep"></span>
<span class="grp"><span class="glab">Gestión</span>
<select id="mSala" onchange="asignarSalaModal()" style="max-width:170px;margin:0" title="Asignar a sala"></select>
<button class="btn dan-solid sm" onclick="eliminarPC()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/></svg>Eliminar</button>
</span>
</div>
</div>
<div style="padding:10px 16px;font-size:12.5px;color:var(--mut)" id="mInfo"></div>
</div></div>

<script>
let PCS=[],SEL=null,timer=null,SALAS=[];
async function cargarSalas(){
  try{
    let j=await(await fetch('api.php?action=listar_salas')).json();SALAS=j.salas||[];
    let fs=document.getElementById('filtroSala'),v=fs.value;
    fs.innerHTML='<option value="">Todas las salas</option><option value="0">Sin asignar</option>'+SALAS.map(s=>`<option value="${s.id}">${s.nombre} (${s.equipos})</option>`).join('');
    if(v!==undefined)fs.value=v;
    document.getElementById('salasTbl').innerHTML=SALAS.map(s=>`<tr><td><b>${s.nombre}</b></td><td>${s.equipos}</td><td><button class="btn sm dan-solid" onclick="eliminarSala(${s.id},'${s.nombre.replace(/'/g,"")}')">Eliminar</button></td></tr>`).join('')||'<tr><td colspan="3">Sin salas. Cree la primera arriba.</td></tr>';
    let ms=document.getElementById('mSala');
    if(ms)ms.innerHTML='<option value="">Sin asignar</option>'+SALAS.map(s=>`<option value="${s.id}">${s.nombre}</option>`).join('');
  }catch(e){}
}
async function crearSala(){let n=document.getElementById('nuevaSala').value.trim();if(!n)return alert('Escriba el nombre');let f=new FormData();f.append('action','crear_sala');f.append('nombre',n);let j=await(await fetch('api.php',{method:'POST',body:f})).json();if(j.ok){document.getElementById('nuevaSala').value='';cargarSalas();cargar();}else alert(j.error||'error');}
async function eliminarSala(id,nombre){if(!confirm('¿Eliminar sala "'+nombre+'"? Sus equipos pasan a Sin asignar.'))return;let f=new FormData();f.append('action','eliminar_sala');f.append('id',id);await fetch('api.php',{method:'POST',body:f});cargarSalas();cargar();}
async function asignarSalaModal(){if(!SEL)return;let f=new FormData();f.append('action','asignar_sala');f.append('hostname',SEL.hostname);f.append('sala_id',document.getElementById('mSala').value);await fetch('api.php',{method:'POST',body:f});cargarSalas();cargar();}
async function eliminarPC(){if(!SEL)return;if(!confirm('¿Eliminar "'+SEL.display+'" del sistema? Se borra su registro, comandos pendientes y última captura. El agente, si sigue corriendo, volverá a registrarse.'))return;let f=new FormData();f.append('action','eliminar_pc');f.append('hostname',SEL.hostname);await fetch('api.php',{method:'POST',body:f});cerrar();cargarSalas();cargar();}
async function cargar(){
  try{
    let j=await(await fetch('api.php?action=list_pcs')).json();
    PCS=j.pcs;document.getElementById('srv').textContent='en línea';
    let on=PCS.filter(p=>p.online).length, scr=PCS.filter(p=>p.has_screen&&(j.now-p.screen_at)<60).length;
    document.getElementById('stOn').textContent=on;
    document.getElementById('stTot').textContent=PCS.length+' registrados';
    document.getElementById('stScr').textContent=scr;
    document.getElementById('stSync').textContent=new Date().toLocaleTimeString();
    let g=document.getElementById('salaGrid');g.innerHTML='';
    let fs0=document.getElementById('filtroSala').value;
    let vis=PCS.filter(p=>fs0===''||(fs0==='0'?(p.sala_id==null):String(p.sala_id)===fs0));
    document.getElementById('emptyMsg').style.display=vis.length?'none':'block';
    document.querySelectorAll('select.t').forEach(s=>{let v=s.value;s.innerHTML='<option value="*">Todos los equipos (*)</option>'+PCS.map(p=>`<option value="${p.hostname}">${p.display} — ${p.ip_local||p.ip}</option>`).join('');if(v)s.value=v;});
    PCS.forEach(p=>{
      if(fs0!==''&&!((fs0==='0'?(p.sala_id==null):String(p.sala_id)===fs0)))return;
      let hace=Math.max(0,j.now-p.last_seen);
      let img=p.has_screen?`api.php?action=screen&hostname=${encodeURIComponent(p.hostname)}&t=${p.screen_at}`:'';
      let d=document.createElement('div');d.className='pc';
      const I={ver:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>',lock:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>',msg:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',off:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.4 6.6a9 9 0 1 1-12.8 0M12 2v9"/></svg>'};
      d.innerHTML=`<div class="shot">${img?`<img src="${img}" loading="lazy">`:''}<span class="badge ${p.online?'b-on':'b-off'}">${p.online?'EN LÍNEA':'OFFLINE'}</span></div>
      <div class="meta"><div class="t1"><b>${p.display}</b><span class="ip">${p.ip_local||p.ip||''}</span></div>
      <div class="row2">${p.hostname} · ${p.username||'—'} · hace ${hace}s · cap ${p.screen_age}s${p.sala?' · <b>'+p.sala+'</b>':''}</div></div>
      <div class="ops"><button data-a="ver" title="Ver pantalla">${I.ver}Ver</button><button data-a="lock" title="Bloquear">${I.lock}Bloq.</button><button data-a="msg" title="Mensaje">${I.msg}Msg</button><button data-a="off" title="Apagar">${I.off}Off</button></div>`;
      d.querySelector('[data-a="ver"]').onclick=e=>{e.stopPropagation();ver(p.hostname)};
      d.querySelector('[data-a="lock"]').onclick=e=>{e.stopPropagation();send('lock',p.hostname,{delay:0})};
      d.querySelector('[data-a="msg"]').onclick=e=>{e.stopPropagation();let m=prompt('Mensaje para '+p.display+':');if(m)send('message',p.hostname,{text:m})};
      d.querySelector('[data-a="off"]').onclick=e=>{e.stopPropagation();if(confirm('¿Apagar '+p.display+'?'))send('shutdown',p.hostname,{delay:10})};
      d.onclick=()=>ver(p.hostname);g.appendChild(d);
    });
    let h=await(await fetch('api.php?action=list_commands')).json();
    document.getElementById('stPen').textContent=h.commands.filter(c=>c.status==='pending').length;
    document.getElementById('hist').innerHTML=h.commands.map(c=>{let pl='';try{let o=JSON.parse(c.payload);pl=[o.url,o.text,o.app,o.filename,o.pattern].filter(Boolean).join(' · ')}catch(e){pl=c.payload}return `<tr><td>#${c.id}</td><td>${c.target}</td><td><b>${c.type}</b></td><td>${pl}</td><td>${c.status==='pending'?'<span class="pill" style="background:var(--warn-bg);color:var(--warn)">pending</span>':'<span class="pill" style="background:var(--ok-bg);color:var(--ok)">done</span>'}</td></tr>`}).join('');
  }catch(e){document.getElementById('srv').textContent='sin conexión';}
}
function ver(host){SEL=PCS.find(p=>p.hostname===host);if(!SEL)return;document.getElementById('modal').style.display='block';if(document.getElementById('mSala'))document.getElementById('mSala').value=SEL.sala_id||'';refrescarModal();clearInterval(timer);timer=setInterval(refrescarModal,3000);}
function refrescarModal(){if(!SEL)return;document.getElementById('mTitle').textContent=`${SEL.display} — ${SEL.hostname} · ${SEL.ip_local||SEL.ip} · ${SEL.username||''}`;document.getElementById('modalImg').src=`api.php?action=screen&hostname=${encodeURIComponent(SEL.hostname)}&t=${Date.now()}`;document.getElementById('mInfo').innerHTML=`Último sondeo hace <b>${Math.max(0,Math.floor(Date.now()/1000)-SEL.last_seen)} s</b> · captura hace <b>${SEL.screen_age} s</b> · SO <code class="k">${SEL.os_info||''}</code>`;}
function cerrar(){document.getElementById('modal').style.display='none';clearInterval(timer);SEL=null;}
async function send(t,target,extra={}){let f=new FormData();f.append('action','send_command');f.append('from_panel','1');f.append('cmd_type',t);f.append('target',target);Object.entries(extra).forEach(([k,v])=>f.append(k,v));await fetch('api.php',{method:'POST',body:f});cargar();}
function cmdSel(t){if(t==='shutdown'&&!confirm('¿Apagar '+SEL.hostname+'?'))return;send(t,SEL.hostname,{delay:10});if(t==='screenshot_now')setTimeout(refrescarModal,4000);}
function masivo(t){if(t==='shutdown'&&!confirm('¿Apagar TODOS los equipos?'))return;send(t,'*',{delay:15});}
function msgSel(){let m=prompt('Mensaje para '+SEL.display+':');if(m)send('message',SEL.hostname,{text:m});}
function webSel(){let u=prompt('URL para '+SEL.display+':','https://');if(u)send('open_url',SEL.hostname,{url:u});}
async function renombrar(){let n=prompt('Etiqueta (ej. Equipo 1):',SEL.display);if(!n)return;let f=new FormData();f.append('action','rename_pc');f.append('from_panel','1');f.append('hostname',SEL.hostname);f.append('label',n);await fetch('api.php',{method:'POST',body:f});cerrar();cargar();}
async function generarToken(){let l=document.getElementById('dlLabel').value.trim()||'Equipo 1';let f=new FormData();f.append('action','generar_token');f.append('label',l);let j=await(await fetch('api.php',{method:'POST',body:f})).json();if(j.ok){document.getElementById('tokShow').textContent=j.token;listarTokens();}else alert(j.error||'error');}
async function listarTokens(){try{let j=await(await fetch('api.php?action=listar_tokens')).json();if(j.ok)document.getElementById('tokList').innerHTML=j.tokens.map(t=>`<code class="k">${t.label}: ${t.token}</code>`).join(' · ')||'sin tokens generados';let cur=document.getElementById('dlLabel').value.trim();let m=j.tokens.find(t=>t.label===cur);if(m)document.getElementById('tokShow').textContent=m.token;}catch(e){}}
function copiarToken(){let t=document.getElementById('tokShow').textContent;navigator.clipboard&&navigator.clipboard.writeText(t);}
function descargarAgente(){let l=document.getElementById('dlLabel').value.trim()||'Equipo 1';window.location.href='api.php?action=descargar_agente&label='+encodeURIComponent(l);setTimeout(listarTokens,1500);}
setInterval(cargar,8000);cargar();listarTokens();cargarSalas();
setInterval(()=>document.getElementById('reloj').textContent=new Date().toLocaleString(),1000);
document.getElementById('modal').addEventListener('click',e=>{if(e.target.id==='modal')cerrar();});
document.addEventListener('keydown',e=>{if(e.key==='Escape')cerrar();});
</script>
</body></html>
