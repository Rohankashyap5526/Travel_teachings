<?php
/**
 * admin.php — Secure Admin Login
 */
require_once __DIR__ . '/includes/bootstrap.php';

// Already logged in → go to dashboard
if (Security::isAdmin()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCsrf(Security::post('csrf_token'))) {
        $error = 'Security token mismatch. Please refresh and try again.';
    } else {
        $username = Security::post('username');
        $password = $_POST['password'] ?? '';  // raw for password_verify
        $ip       = $_SERVER['REMOTE_ADDR'];

        if (!Security::checkLoginAttempts($ip)) {
            $error = 'Too many failed attempts. Please wait 15 minutes.';
        } elseif ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH)) {
            Security::clearLoginAttempts($ip);
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user']      = $username;
            $_SESSION['admin_time']      = time();
            // Log login
            try {
                Database::get()->prepare(
                    "INSERT INTO admin_log (action, ip_address) VALUES ('login', ?)"
                )->execute([$ip]);
            } catch (Exception $e) {}
            header('Location: dashboard.php');
            exit;
        } else {
            Security::recordLoginAttempt($ip);
            $error = 'Invalid credentials.';
            // Timing attack prevention
            usleep(random_int(100000, 300000));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login | TravelTeachings</title>
<link rel="icon" type="image/png" href="assets/images/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/main.css">
<style>
body { background: var(--navy); }
.login-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
.login-box { width: 420px; background: var(--white); border-radius: var(--radius-lg); padding: 48px 40px; box-shadow: var(--shadow-lg); }
.login-logo { text-align: center; margin-bottom: 36px; }
.login-logo img { height: 52px; margin: 0 auto 12px; }
.login-logo h2 { font-family: var(--font-display); font-size: 1.5rem; color: var(--navy); }
.login-logo p  { font-size: .85rem; color: var(--text-muted); margin-top: 4px; }
.input-icon-wrap { position: relative; }
.input-icon-wrap i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: .9rem; }
.input-icon-wrap input { padding-left: 40px; }
.toggle-pass { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted); font-size: .9rem; }
.back-link { text-align: center; margin-top: 20px; font-size: .85rem; color: var(--text-muted); }
.back-link a { color: var(--gold); font-weight: 600; }
</style>
</head>
<body>
<div class="login-page">
  <div class="login-box">
    <div class="login-logo">
      <img src="assets/images/logo.png" alt="TravelTeachings">
      <h2>Admin Access</h2>
      <p>TravelTeachings Dashboard</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
      <?= Security::csrfField() ?>
      <div class="form-group">
        <label>Username</label>
        <div class="input-icon-wrap">
          <i class="fas fa-user"></i>
          <input type="text" name="username" required autocomplete="username"
                 placeholder="Admin username" value="<?= htmlspecialchars(Security::post('username')) ?>">
        </div>
      </div>
      <div class="form-group">
        <label>Password</label>
        <div class="input-icon-wrap">
          <i class="fas fa-lock"></i>
          <input type="password" name="password" id="pass-field" required autocomplete="current-password" placeholder="Password">
          <button type="button" class="toggle-pass" onclick="togglePass()" id="toggle-icon">
            <i class="fas fa-eye" id="eye-icon"></i>
          </button>
        </div>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:8px;">
        <i class="fas fa-sign-in-alt"></i> Sign In
      </button>
    </form>

    <div class="back-link"><a href="index.php"><i class="fas fa-arrow-left"></i> Back to website</a></div>
  </div>
</div>
<script>
function togglePass() {
  const f = document.getElementById('pass-field');
  const i = document.getElementById('eye-icon');
  if (f.type === 'password') { f.type = 'text'; i.className = 'fas fa-eye-slash'; }
  else { f.type = 'password'; i.className = 'fas fa-eye'; }
}
</script>
</body>
</html>
