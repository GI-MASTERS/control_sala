# ControlSala v2 - Agente cliente Win11 estilo Veyon
# pip install requests pillow
# Configura SERVIDOR, TOKEN y ETIQUETA (ej "Equipo 1")
import os, sys, io, time, json, socket, ctypes, glob, getpass, subprocess, webbrowser, threading
from pathlib import Path

SERVIDOR = "http://127.0.0.1:8000"   # <-- IP del servidor, ej http://192.168.1.10:8000
TOKEN = "sala2026-cambia-esto"       # <-- igual que config.php
ETIQUETA = ""                        # <-- ej "Equipo 1". Si se deja vacío usa el hostname. También lee equipo.txt
POLL_SECS = 5
SHOT_SECS = 8                        # captura de pantalla cada 8s para vista en vivo
HOSTNAME = os.environ.get("COMPUTERNAME", socket.gethostname())
# Fuera de Documentos para evitar "Acceso controlado a carpetas" (ransomware protection)
# Documentos está protegido y bloquea a python.exe no firmado.
BASE_DIR = Path(os.environ.get("PROGRAMDATA", "C:\\ProgramData")) / "ControlSala"
try:
    BASE_DIR.mkdir(parents=True, exist_ok=True)
except PermissionError:
    BASE_DIR = Path(__file__).parent / "archivos"
    BASE_DIR.mkdir(parents=True, exist_ok=True)

# Etiqueta desde archivo equipo.txt (para clonar PCs sin editar código)
try:
    _f = Path(__file__).parent / "equipo.txt"
    if _f.exists() and not ETIQUETA:
        ETIQUETA = _f.read_text(encoding="utf-8").strip()
except Exception:
    pass
LABEL = ETIQUETA or HOSTNAME

try:
    import requests
except ImportError:
    print("Falta 'requests'. Ejecuta: pip install requests pillow"); sys.exit(1)

HEADERS = {"X-Token": TOKEN}
lock_window = None

def _logpath():
    try: return Path(__file__).parent / "agente.log"
    except Exception: return BASE_DIR / "agente.log"

def log(msg):
    try:
        with open(_logpath(), "a", encoding="utf-8") as f:
            f.write(time.strftime("%Y-%m-%d %H:%M:%S") + " " + str(msg) + "\n")
    except Exception:
        pass

def _fatal(t, v, tb):
    try:
        import traceback
        log("FATAL: " + "".join(traceback.format_exception(t, v, tb))[-2000:])
    except Exception:
        pass
    sys.__excepthook__(t, v, tb)
sys.excepthook = _fatal

def api(action, method="GET", **kw):
    url = f"{SERVIDOR}/api.php?action={action}"
    kw.setdefault("headers", HEADERS); kw.setdefault("timeout", 12)
    return requests.request(method, url, **kw).json()

def mi_ip():
    try:
        s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        s.connect(("8.8.8.8", 80)); ip = s.getsockname()[0]; s.close(); return ip
    except Exception:
        return socket.gethostbyname(socket.gethostname())

def mi_usuario():
    try: return getpass.getuser()
    except Exception: return ""

def set_wallpaper(file_url, filename="fondo.jpg"):
    dest = BASE_DIR / filename
    with requests.get(file_url, headers=HEADERS, stream=True, timeout=60) as r:
        r.raise_for_status()
        with open(dest, "wb") as f:
            for ch in r.iter_content(8192): f.write(ch)
    p = str(dest.resolve())
    if os.name == "nt":
        try:
            import winreg
            k = winreg.OpenKey(winreg.HKEY_CURRENT_USER, r"Control Panel\Desktop", 0, winreg.KEY_SET_VALUE)
            winreg.SetValueEx(k, "WallpaperStyle", 0, winreg.REG_SZ, "10")
            winreg.SetValueEx(k, "TileWallpaper", 0, winreg.REG_SZ, "0")
            winreg.CloseKey(k)
        except Exception as e: print("registro:", e)
        ok = ctypes.windll.user32.SystemParametersInfoW(20, 0, p, 0x01 | 0x02)
        print("wallpaper:", "OK" if ok else "falló")
    else: print("guardado:", p)

def capturar_y_subir():
    """Toma screenshot reducido y lo sube para la vista en vivo del profe."""
    try:
        from PIL import ImageGrab
        img = ImageGrab.grab()
        img.thumbnail((640, 360))
        buf = io.BytesIO(); img.save(buf, "JPEG", quality=55)
        buf.seek(0)
        requests.post(f"{SERVIDOR}/api.php?action=screenshot", headers=HEADERS,
            data={"hostname": HOSTNAME}, files={"shot": ("s.jpg", buf, "image/jpeg")}, timeout=15)
    except Exception as e:
        print("screenshot:", e)

def shot_loop():
    while True:
        try: capturar_y_subir()
        except Exception as e: print("shot:", e)
        time.sleep(SHOT_SECS)

def do_lock():
    global lock_window
    if lock_window: return
    try:
        import tkinter as tk
        w = tk.Tk(); w.attributes("-fullscreen", True); w.attributes("-topmost", True)
        w.configure(bg="black"); w.title("CLASE EN CURSO")
        tk.Label(w, text="🔒 CLASE EN CURSO", fg="#38bdf8", bg="black", font=("Segoe UI", 48, "bold")).pack(expand=True)
        tk.Label(w, text=f"{LABEL} — espera la indicación del profesor", fg="white", bg="black", font=("Segoe UI", 18)).pack()
        w.protocol("WM_DELETE_WINDOW", lambda: None)
        w.bind("<Alt-F4>", lambda e: "break")
        threading.Thread(target=w.mainloop, daemon=True).start()
        lock_window = w; print("bloqueado")
    except Exception as e: print("lock:", e)

def do_unlock():
    global lock_window
    try:
        if lock_window: lock_window.destroy(); lock_window = None; print("desbloqueado")
    except Exception as e: print("unlock:", e)

def ejecutar(cmd):
    t = cmd["type"]; p = json.loads(cmd.get("payload") or "{}")
    print(f"-> #{cmd['id']} {t}")
    try:
        if t == "wallpaper": set_wallpaper(p["file_url"], p.get("filename", "fondo.jpg"))
        elif t == "open_url": webbrowser.open(p.get("url", ""))
        elif t == "message":
            if os.name == "nt": ctypes.windll.user32.MessageBoxW(0, p.get("text", ""), "ControlSala — Profesor", 0x40 | 0x1000)
            else: print(p.get("text", ""))
        elif t == "run_app":
            app = p.get("app", "notepad.exe")
            subprocess.Popen(app, shell=True)
        elif t == "lock": do_lock()
        elif t == "unlock": do_unlock()
        elif t == "screenshot_now": capturar_y_subir()
        elif t == "shutdown":
            subprocess.run(["shutdown", "/s", "/t", str(int(p.get("delay", 15))), "/c", "Apagado por ControlSala"], shell=(os.name == "nt"))
        elif t == "reboot":
            subprocess.run(["shutdown", "/r", "/t", str(int(p.get("delay", 15))), "/c", "Reinicio por ControlSala"], shell=(os.name == "nt"))
        elif t == "download_file":
            url, fn = p.get("file_url"), p.get("filename", "archivo"); dest = BASE_DIR / fn
            with requests.get(url, headers=HEADERS, stream=True, timeout=120) as r:
                r.raise_for_status()
                open(dest, "wb").write(r.content)
            print("descargado:", dest)
            if p.get("open_after") and os.name == "nt": os.startfile(dest)
        elif t == "collect_file":
            for pat in p.get("pattern", "*.docx;*.pdf").replace(",", ";").split(";"):
                for f in glob.glob(str(BASE_DIR / pat.strip())):
                    with open(f, "rb") as fh:
                        requests.post(f"{SERVIDOR}/api.php?action=upload_collected", headers=HEADERS,
                            data={"hostname": HOSTNAME}, files={"file": (os.path.basename(f), fh)}, timeout=60)
                    print("subido:", f)
        api("ack", "POST", data={"hostname": HOSTNAME, "command_id": cmd["id"]})
    except Exception as e:
        print("error comando:", e); log(f"comando {cmd.get('type')} fallo: {e}")
def main():
    print(f"ControlSala v2 | {LABEL} ({HOSTNAME}) IP={mi_ip()} user={mi_usuario()} -> {SERVIDOR}")
    log(f"inicio agente {LABEL} ({HOSTNAME}) -> {SERVIDOR}")
    threading.Thread(target=shot_loop, daemon=True).start()
    try:
        print(api("register", "POST", data={"hostname": HOSTNAME, "label": LABEL,
              "username": mi_usuario(), "ip_local": mi_ip(), "os_info": sys.platform}))
    except Exception as e:
        print("sin servidor:", e); log(f"register fallo: {e}")
    last_hb = 0
    fails = 0
    while True:
        try:
            if time.time() - last_hb > 10:
                api("heartbeat", "POST", data={"hostname": HOSTNAME, "label": LABEL,
                    "username": mi_usuario(), "ip_local": mi_ip(), "os_info": sys.platform})
                last_hb = time.time(); fails = 0
            for c in api("poll", params={"hostname": HOSTNAME}).get("commands", []):
                ejecutar(c)
        except Exception as e:
            fails += 1
            print("loop:", e); log(f"loop fallo #{fails}: {e}")
        time.sleep(POLL_SECS)

if __name__ == "__main__":
    if "--probar" in sys.argv:
        try:
            r = api("register", "POST", data={"hostname": HOSTNAME, "label": LABEL,
                "username": mi_usuario(), "ip_local": mi_ip(), "os_info": sys.platform})
            print("OK conexion con servidor:", r)
        except Exception as e:
            print("FALLO:", e); sys.exit(1)
    else:
        main()
