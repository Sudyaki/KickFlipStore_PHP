<?php
require __DIR__ . '/config.php';
require_login();
$uid = current_user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    // ---- DELETE (remove one item) ----
    if (isset($_POST['remove'])) {
        $st = db()->prepare('DELETE FROM cart_items WHERE id = ? AND user_id = ?');
        $st->execute([(int)$_POST['remove'], $uid]);
        flash('Item removed from cart.');
        redirect('cart.php');
    }

    // ---- CHECKOUT ----
    if (isset($_POST['checkout'])) {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $st = $pdo->prepare(
                'SELECT c.quantity, p.id, p.name, p.price
                 FROM cart_items c JOIN products p ON p.id = c.product_id
                 WHERE c.user_id = ? FOR UPDATE'
            );
            $st->execute([$uid]);
            $rows = $st->fetchAll();
            if (!$rows) {
                $pdo->rollBack();
                flash('Your cart is empty.', 'err');
                redirect('cart.php');
            }
            $total = 0;
            foreach ($rows as $r) $total += $r['price'] * $r['quantity'];

            $pdo->prepare('INSERT INTO orders (user_id, total) VALUES (?, ?)')->execute([$uid, $total]);
            $orderId = (int)$pdo->lastInsertId();

            $ins = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?,?,?,?,?)');
            foreach ($rows as $r) {
                $ins->execute([$orderId, $r['id'], $r['name'], $r['price'], $r['quantity']]);
            }
            $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?')->execute([$uid]);
            $pdo->commit();
            flash("Order #$orderId placed. Total: " . money($total));
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            flash('Checkout failed. Please try again.', 'err');
        }
        redirect('cart.php');
    }
}

// ---- READ ----
$st = db()->prepare(
    'SELECT c.id AS cart_id, c.quantity, p.name, p.category, p.price, p.image
     FROM cart_items c JOIN products p ON p.id = c.product_id
     WHERE c.user_id = ? ORDER BY c.id DESC'
);
$st->execute([$uid]);
$items = $st->fetchAll();
$total = 0;
foreach ($items as $i) $total += $i['price'] * $i['quantity'];
$f = flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Cart | <?= e(SITE_NAME) ?></title>
    <link rel="icon" href="images/logo.png">
    <link rel="stylesheet" href="css/CartStyle.css">
    <link rel="stylesheet" href="css/dark.css">
    <script src="js/theme.js"></script>
    <style>
        a.back { color:#e6e6e6; margin-bottom:20px; text-decoration:none; }
        a.back:hover { text-decoration:underline; }
        .total { color:#e6e6e6; font-size:22px; margin-top:10px; text-align:center; }
        .msg { padding:10px 18px; border-radius:8px; margin-bottom:15px; font-weight:600; }
        .msg.ok { background:#e3f4e3; color:#14532d; } .msg.err { background:#fde4e4; color:#7f1d1d; }
        .empty { color:#e6e6e6; text-align:center; }
    </style>
</head>
<body>
<button class="theme-btn theme-float" type="button">🌙 Dark</button>
<form method="post">
    <?= csrf_field() ?>
    <div class="logo-title">
        <img src="images/logo.png" alt="Logo">
        <h2>My Cart</h2>
    </div>
    <a class="back" href="index.php">&larr; Continue shopping</a>

    <?php if ($f): ?><div class="msg <?= e($f['type']) ?>"><?= e($f['msg']) ?></div><?php endif; ?>

    <div class="cart-box">
        <?php if (!$items): ?>
            <p class="empty">Your cart is empty.</p>
        <?php else: ?>
            <?php foreach ($items as $i): ?>
                <div class="cart-item">
                    <img src="<?= e(product_image($i['image'])) ?>" alt="<?= e($i['name']) ?>">
                    <div class="cart-info">
                        <h3><?= e($i['name']) ?></h3>
                        <p><?= e($i['category']) ?></p>
                        <p><?= (int)$i['quantity'] ?> &times; <?= money($i['price']) ?></p>
                        <p class="price"><?= money($i['price'] * $i['quantity']) ?></p>
                    </div>
                    <div class="cart-action">
                        <button class="btn-remove" type="submit" name="remove" value="<?= (int)$i['cart_id'] ?>">Remove</button>
                    </div>
                </div>
            <?php endforeach; ?>

            <p class="total">Total: <strong><?= money($total) ?></strong></p>
            <div class="checkout-box">
                <button class="btn-checkout" type="submit" name="checkout" value="1">Checkout</button>
            </div>
        <?php endif; ?>
    </div>
</form>
</body>
</html>
