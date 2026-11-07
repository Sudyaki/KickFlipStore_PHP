<?php
require __DIR__ . '/config.php';

if (current_user()) redirect('index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';
    $role     = $_POST['role'] ?? 'user';
    $code     = (string)($_POST['admin_code'] ?? '');

    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
        $error = 'Username must be 3-30 letters, numbers or underscores.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (!in_array($role, ['user', 'admin'], true)) {
        $error = 'Invalid role.';
    } elseif ($role === 'admin' && !hash_equals(ADMIN_CODE, $code)) {
        $error = 'Wrong admin confirmation code.';
    } else {
        $st = db()->prepare('SELECT 1 FROM users WHERE username = ?');
        $st->execute([$username]);
        if ($st->fetch()) {
            $error = 'That username is already taken.';
        } else {
            $ins = db()->prepare('INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)');
            $ins->execute([$username, password_hash($password, PASSWORD_DEFAULT), $role]);
            flash('Account created as ' . ($role === 'admin' ? 'Admin' : 'User') . '. You can log in now.');
            redirect('login.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register | <?= e(SITE_NAME) ?></title>
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
        <h2>Register</h2>
    </div>
    <?php if ($error): ?><p style="color:#b91c1c;margin-bottom:8px"><?= e($error) ?></p><?php endif; ?>
    <?= csrf_field() ?>
    <input type="text" name="username" placeholder="Username" value="<?= e($_POST['username'] ?? '') ?>" required autofocus>
    <input type="password" name="password" placeholder="Password" required>
    <input type="password" name="confirm" placeholder="Confirm password" required>
    <select name="role" id="role" style="width:90%;padding:10px;margin:10px">
        <option value="user" <?= ($_POST['role'] ?? 'user') === 'user' ? 'selected' : '' ?>>Register as User</option>
        <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Register as Admin</option>
    </select>
    <input type="password" name="admin_code" id="admin_code" placeholder="Admin confirmation code"
           style="display:none">
    <br><br>
    <button class="btn" type="submit">Create account</button>
    <p style="margin-top:18px;font-size:14px">Have an account? <a href="login.php">Login</a></p>
</form>
<script>
    var role = document.getElementById('role'), code = document.getElementById('admin_code');
    function toggleCode() {
        var admin = role.value === 'admin';
        code.style.display = admin ? 'inline-block' : 'none';
        code.required = admin;
    }
    role.addEventListener('change', toggleCode);
    toggleCode();
</script>
</body>
</html>
