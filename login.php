<?php
require_once __DIR__ . '/includes/auth.php';

// Already logged in? go straight to dashboard.
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        // Prepared statement — protects against SQL injection.
        $stmt = $pdo->prepare('SELECT id, full_name, username, password_hash, role FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['role']      = $user['role'];
            header('Location: dashboard.php');
            exit;
        }

        $error = 'Incorrect username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Log in · MedLedger</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-shell">
  <div class="auth-card">
    <div class="auth-brand">
      <span class="mark">MedLedger</span>
      <span class="tag">Rx Stock</span>
    </div>
    <p class="auth-sub">Pharmacy inventory dashboard. Sign in to continue.</p>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="post" action="login.php" novalidate>
      <div class="field">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" autocomplete="username" required
               value="<?= h($_POST['username'] ?? '') ?>">
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" autocomplete="current-password" required>
      </div>
      <button type="submit" class="btn btn-primary">Log in</button>
    </form>

    <div class="auth-hint">Demo login: username <strong>admin</strong> — password set during setup (see README)</div>
  </div>
</div>
</body>
</html>
