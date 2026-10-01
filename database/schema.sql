-- ControlSala · Esquema SQLite (solo estructura, sin datos)
-- Generado desde data.db. Las migraciones incrementales viven en db.php.

CREATE TABLE commands (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        target TEXT NOT NULL, -- hostname, '*'=todos
        type TEXT NOT NULL,   -- wallpaper|open_url|message|lock|unlock|shutdown|reboot|download_file|collect_file
        payload TEXT,         -- JSON
        status TEXT DEFAULT 'pending', -- pending|done
        created_at INTEGER, delivered_at INTEGER
    );

CREATE TABLE pcs (
        hostname TEXT PRIMARY KEY,
        ip TEXT, os_info TEXT, last_seen INTEGER, created_at INTEGER
    , label TEXT DEFAULT '', username TEXT DEFAULT '', ip_local TEXT DEFAULT '', screen_at INTEGER DEFAULT 0, token TEXT DEFAULT '', sala_id INTEGER DEFAULT NULL);

CREATE TABLE salas (
        id INTEGER PRIMARY KEY AUTOINCREMENT, nombre TEXT UNIQUE NOT NULL
    );

CREATE TABLE tokens (
        label TEXT PRIMARY KEY, token TEXT NOT NULL, created_at INTEGER
    );
