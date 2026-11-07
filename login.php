<?php
require __DIR__ . '/config.php';

if (current_user()) redirect('index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $st = db()->prepare('SELECT * FROM users WHERE username = ?');
    $st->execute([$username]);
    $u = $st->fetch();

    if ($u && password_verify($password, $u['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int)$u['id'], 'username' => $u['username'], 'role' => $u['role']];
        redirect($u['role'] === 'admin' ? 'admin/products.php' : 'index.php');
    }
    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | <?= e(SITE_NAME) ?></title>
    <link rel="icon" href="images/logo.png">
    <link rel="stylesheet" href="css/LogStyle.css">
    <link rel="stylesheet" href="css/dark.css">
    <script src="js/theme.js"></script>
</head>
<body>
<button class="theme-btn theme-float" type="button">🌙 Dark</button>
<form method="post" class="login-box" style="height:auto">
    <div class="logo-title">
        <img src="images/logo.png" alt="Logo">
        <h2>Login</h2>
    </div>
    <?php if ($error): ?><p style="color:#b91c1c;margin-bottom:8px"><?= e($error) ?></p><?php endif; ?>
    <?php if ($f = flash()): ?><p style="color:#14532d;margin-bottom:8px"><?= e($f['msg']) ?></p><?php endif; ?>
    <?= csrf_field() ?>
    <input type="text" name="username" placeholder="Username" required autofocus>
    <input type="password" name="password" placeholder="Password" required>
    <br><br>
    <button class="btn" type="submit">Login</button>
    <p style="margin-top:18px;font-size:14px">No account? <a href="register.php">Register</a> &middot; <a href="index.php">Back to shop</a></p>
</form>
</body>
</html>
