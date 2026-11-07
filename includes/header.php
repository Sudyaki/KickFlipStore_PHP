<?php
// Usage: $title = '...'; $root = '' (or '../' inside /admin); require header.php
$root  = $root  ?? '';
$title = $title ?? SITE_NAME;
$user  = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> | <?= e(SITE_NAME) ?></title>
    <link rel="icon" href="<?= $root ?>images/logo.png">
    <link rel="stylesheet" href="<?= $root ?>css/style.css">
    <link rel="stylesheet" href="<?= $root ?>css/extra.css">
    <link rel="stylesheet" href="<?= $root ?>css/dark.css">
    <script src="<?= $root ?>js/theme.js"></script>
</head>
<body>
<header class="header">
    <div class="logo">
        <img src="<?= $root ?>images/logo.png" alt="Logo">
        <span><?= e(SITE_NAME) ?></span>
    </div>
    <nav class="nav">
        <a href="<?= $root ?>index.php">Shop</a>
        <?php if ($user && $user['role'] === 'admin'): ?>
            <a href="<?= $root ?>admin/products.php">Products</a>
            <a href="<?= $root ?>admin/orders.php">Orders</a>
        <?php endif; ?>
        <?php if ($user): ?>
            <span class="cart"><a href="<?= $root ?>cart.php">🛒 Cart (<?= cart_count() ?>)</a></span>
            <span class="who"><?= e($user['username']) ?></span>
            <a href="<?= $root ?>logout.php">Logout</a>
        <?php else: ?>
            <a href="<?= $root ?>login.php">Login</a>
            <a href="<?= $root ?>register.php">Register</a>
        <?php endif; ?>
        <button class="theme-btn" type="button">🌙 Dark</button>
    </nav>
</header>
<?php if ($f = flash()): ?>
    <div class="flash <?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
<?php endif; ?>
