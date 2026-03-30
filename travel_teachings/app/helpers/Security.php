<?php
/**
 * Security Helper
 */
class Security {

    // ── Session ──────────────────────────────────────────────────────────────
    public static function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path'     => '/',
                'secure'   => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
            session_start();
            // Regenerate periodically
            if (!isset($_SESSION['_initiated'])) {
                session_regenerate_id(true);
                $_SESSION['_initiated'] = true;
            }
        }
    }

    // ── CSRF ─────────────────────────────────────────────────────────────────
    public static function generateCsrf(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(string $token): bool {
        return isset($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function csrfField(): string {
        return '<input type="hidden" name="csrf_token" value="' . self::generateCsrf() . '">';
    }

    // ── Input ─────────────────────────────────────────────────────────────────
    public static function clean(string $input): string {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public static function post(string $key, string $default = ''): string {
        return isset($_POST[$key]) ? self::clean($_POST[$key]) : $default;
    }

    public static function get(string $key, string $default = ''): string {
        return isset($_GET[$key]) ? self::clean($_GET[$key]) : $default;
    }

    // ── Rate Limiting (session-based) ─────────────────────────────────────────
    public static function checkLoginAttempts(string $identifier): bool {
        $key   = 'login_attempts_' . md5($identifier);
        $time  = 'login_time_' . md5($identifier);
        $now   = time();

        if (isset($_SESSION[$time]) && ($now - $_SESSION[$time]) > LOCKOUT_TIME) {
            unset($_SESSION[$key], $_SESSION[$time]);
        }

        return (!isset($_SESSION[$key]) || $_SESSION[$key] < MAX_LOGIN_ATTEMPTS);
    }

    public static function recordLoginAttempt(string $identifier): void {
        $key  = 'login_attempts_' . md5($identifier);
        $time = 'login_time_' . md5($identifier);
        $_SESSION[$key] = ($_SESSION[$key] ?? 0) + 1;
        if ($_SESSION[$key] === 1) {
            $_SESSION[$time] = time();
        }
    }

    public static function clearLoginAttempts(string $identifier): void {
        $key  = 'login_attempts_' . md5($identifier);
        $time = 'login_time_' . md5($identifier);
        unset($_SESSION[$key], $_SESSION[$time]);
    }

    // ── Auth ──────────────────────────────────────────────────────────────────
    public static function isAdmin(): bool {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }

    public static function requireAdmin(): void {
        if (!self::isAdmin()) {
            header('Location: ../admin.php');
            exit;
        }
    }

    // ── Headers ───────────────────────────────────────────────────────────────
    public static function setSecureHeaders(): void {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com https://fonts.gstatic.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data: https:; connect-src 'self' https://api.groq.com;");
    }

    // ── File validation ───────────────────────────────────────────────────────
    public static function validateUpload(array $file): array {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'msg' => 'Upload error code: ' . $file['error']];
        }
        if ($file['size'] > UPLOAD_MAX_SIZE) {
            return ['ok' => false, 'msg' => 'File too large (max 20MB)'];
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_EXT, true)) {
            return ['ok' => false, 'msg' => 'Only PDF files are allowed'];
        }
        // Check magic bytes
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, ALLOWED_TYPES, true)) {
            return ['ok' => false, 'msg' => 'Invalid file type'];
        }
        return ['ok' => true];
    }
}
