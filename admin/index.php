<?php
require_once __DIR__ . '/config.php';
admin_session_start();

// Already logged in — go to dashboard
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: /admin/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } elseif (password_verify($_POST['password'] ?? '', ADMIN_PASSWORD_HASH)) {
        $_SESSION['admin_logged_in'] = true;
        // Regenerate session ID on login
        session_regenerate_id(true);
        header('Location: /admin/dashboard.php');
        exit;
    } else {
        $error = 'Incorrect password.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Login</title>
  <link rel="stylesheet" href="/admin/assets/admin.css">
  <meta name="robots" content="noindex,nofollow">
</head>
<body class="login-page">
  <div class="login-card">
    <h1>Admin Login</h1>
    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" autocomplete="off">
      <?= csrf_field() ?>
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required autofocus>
      <button type="submit">Log In</button>
    </form>
  </div>
</body>
</html>
