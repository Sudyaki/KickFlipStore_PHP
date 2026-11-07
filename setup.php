<?php
// RUN ONCE to create the first admin account, then DELETE this file.
require __DIR__ . '/config.php';

$exists = (int)db()->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
if ($exists) {
    exit('An admin already exists. Delete setup.php from the server.');
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username) || strlen($password) < 8) {
        $msg = 'Username: 3-30 letters/numbers/_. Password: at least 8 characters.';
    } else {
        $st = db()->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'admin')
                             ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = 'admin'");
        $st->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
        exit('Admin created. <strong>Delete setup.php now</strong>, then <a href="login.php">log in</a>.');
    }
}
?>
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Setup</title>
<style>body{font-family:Arial;max-width:360px;margin:60px auto}input{width:100%;padding:10px;margin:6px 0}</style></head>
<body>
<h2>Create admin account</h2>
<?php if ($msg): ?><p style="color:#b91c1c"><?= e($msg) ?></p><?php endif; ?>
<form method="post">
    <?= csrf_field() ?>
    <input type="text" name="username" placeholder="Admin username" required>
    <input type="password" name="password" placeholder="Password (8+ chars)" required>
    <button type="submit">Create admin</button>
</form>
</body></html>
