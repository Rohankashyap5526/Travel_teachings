<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (Security::isAdmin()) {
    try {
        Database::get()->prepare(
            "INSERT INTO admin_log (action, ip_address) VALUES ('logout', ?)"
        )->execute([$_SERVER['REMOTE_ADDR']]);
    } catch (Exception $e) {}
}
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();
header('Location: index.php');
exit;
