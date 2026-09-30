<?php
/* PBI Group — Railway-aware configuration.
   On Railway: env vars are injected automatically when you add a
               MySQL / Postgres plugin, or set them in the Variables tab.
   Locally:    SQLite is used out of the box — no setup needed.
   -------------------------------------------------------------------- */

/* ── Auto-detect Railway / cloud database ───────────────────────────── */

if (getenv('DATABASE_URL') !== false && getenv('DATABASE_URL') !== '') {
    // Railway MySQL or Postgres plugin — unified DATABASE_URL
    $dsn    = parse_url(getenv('DATABASE_URL'));
    $scheme = strtolower($dsn['scheme'] ?? 'mysql');
    if (in_array($scheme, array('postgres', 'postgresql'), true)) {
        define('DB_DRIVER', 'pgsql');
    } else {
        define('DB_DRIVER', 'mysql');
    }
    define('DB_HOST', $dsn['host']);
    define('DB_NAME', ltrim($dsn['path'] ?? '', '/'));
    define('DB_USER', $dsn['user'] ?? '');
    define('DB_PASS', $dsn['pass'] ?? '');
    define('DB_PORT', (string)($dsn['port'] ?? (DB_DRIVER === 'pgsql' ? 5432 : 3306)));

} elseif (getenv('MYSQLHOST') !== false || getenv('MYSQL_HOST') !== false) {
    // Railway MySQL plugin — individual vars
    define('DB_DRIVER', 'mysql');
    define('DB_HOST',   getenv('MYSQLHOST')     ?: getenv('MYSQL_HOST'));
    define('DB_NAME',   getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE'));
    define('DB_USER',   getenv('MYSQLUSER')     ?: getenv('MYSQL_USER'));
    define('DB_PASS',   getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD'));
    define('DB_PORT',   getenv('MYSQLPORT')     ?: getenv('MYSQL_PORT') ?: '3306');

} elseif (getenv('PGHOST') !== false) {
    // Railway Postgres plugin — individual vars
    define('DB_DRIVER', 'pgsql');
    define('DB_HOST',   getenv('PGHOST'));
    define('DB_NAME',   getenv('PGDATABASE'));
    define('DB_USER',   getenv('PGUSER'));
    define('DB_PASS',   getenv('PGPASSWORD'));
    define('DB_PORT',   getenv('PGPORT') ?: '5432');

} else {
    /* ── Local / SQLite fallback ──────────────────────────────────────── */
    define('DB_DRIVER',      'sqlite');
    define('DB_SQLITE_PATH', __DIR__ . '/storage/pbigroup.sqlite');

    /* Manual MySQL override — uncomment + fill in for non-Railway MySQL:
    define('DB_DRIVER', 'mysql');
    define('DB_HOST',   'localhost');
    define('DB_NAME',   'yourcpanel_pbigroup');
    define('DB_USER',   'yourcpanel_pbigroup');
    define('DB_PASS',   'your-database-password');
    define('DB_PORT',   '3306');
    */
}

/* ── App settings ─────────────────────────────────────────────────── */

// Set APP_KEY in Railway Variables tab for production.
define('APP_KEY',   getenv('APP_KEY')   ?: 'pbi_9f3a2e1c7b6d4f8e2a0c5b1d9e3f7a4b');
define('APP_DEBUG', getenv('APP_DEBUG') === 'true');

// APP_URL auto-resolved from Railway public domain (set automatically).
$_rd = getenv('RAILWAY_PUBLIC_DOMAIN');
define('APP_URL', $_rd ? 'https://' . $_rd : (getenv('APP_URL') ?: ''));
unset($_rd);

