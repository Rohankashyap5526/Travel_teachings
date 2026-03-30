<?php
/**
 * TravelTeachings - Application Configuration
 * Production-ready config with environment variable support
 */

// ─── Environment ─────────────────────────────────────────────────────────────
define('APP_ENV', getenv('APP_ENV') ?: 'production');
define('APP_NAME', 'TravelTeachings');
define('APP_VERSION', '2.0.0');
define('APP_URL', getenv('APP_URL') ?: '');

// ─── Database ─────────────────────────────────────────────────────────────────
// define('DB_HOST',     getenv('DB_HOST')     ?: 'localhost');
// define('DB_USER',     getenv('DB_USER')     ?: 'u575935146_root');
// define('DB_PASS',     getenv('DB_PASS')     ?: 'Paarth@2024');
// define('DB_NAME',     getenv('DB_NAME')     ?: 'u575935146_test');
// define('DB_CHARSET',  'utf8mb4');

// ─── Security ─────────────────────────────────────────────────────────────────
define('SESSION_NAME',        'tt_session');
define('SESSION_LIFETIME',    3600);           // 1 hour
define('CSRF_TOKEN_LENGTH',   32);
define('MAX_LOGIN_ATTEMPTS',  5);
define('LOCKOUT_TIME',        900);            // 15 minutes
define('ADMIN_PASSWORD_HASH', password_hash('', PASSWORD_BCRYPT, ['cost' => 12]));
define('ADMIN_USERNAME',      'Renumaci');

// ─── File Upload ──────────────────────────────────────────────────────────────
define('UPLOAD_DIR',      dirname(__DIR__, 2) . '/notes/');
define('UPLOAD_MAX_SIZE', 20 * 1024 * 1024);  // 20 MB
define('ALLOWED_TYPES',   ['application/pdf']);
define('ALLOWED_EXT',     ['pdf']);

// ─── Groq / AI ────────────────────────────────────────────────────────────────
define('GROQ_API_KEY',   getenv('GROQ_API_KEY') ?: '');   // set via env or admin panel
define('GROQ_MODEL',     'llama-3.1-8b-instant');
define('GROQ_MAX_TOKENS', 1024);

// ─── Paths ────────────────────────────────────────────────────────────────────
define('ROOT_PATH',    dirname(__DIR__, 2));
define('PUBLIC_PATH',  ROOT_PATH . '/public');
define('INCLUDES_PATH', ROOT_PATH . '/includes');

// ─── Error Handling ───────────────────────────────────────────────────────────
if (APP_ENV === 'production') {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', ROOT_PATH . '/logs/error.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}
