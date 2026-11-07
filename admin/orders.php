<?php
require __DIR__ . '/../config.php';
require_admin();

$orders = db()->query(
    'SELECT o.id, o.total, o.created_at, u.username
     FROM orders o JOIN users u ON u.id = o.user_id
     ORDER BY o.id DESC'
)->fetchAll();

$itemsStmt = db()->prepare('SELECT product_name, price, quantity FROM order_items WHERE order_id = ?');

$title = 'Orders';
$root  = '../';
require __DIR__ . '/../includes/header.php';
?>
<main class="container">
    <h2 style="margin-bottom:20px">Orders</h2>
    <?php if (!$orders): ?>
        <p class="empty">No orders yet.</p>
    <?php else: ?>
    <div class="table-wrap">
    <table class="data">
        <tr><th>#</th><th>Customer</th><th>Items</th><th>Total</th><th>Date</th></tr>
        <?php foreach ($orders as $o):
            $itemsStmt->execute([$o['id']]);
            $lines = $itemsStmt->fetchAll(); ?>
        <tr>
            <td><?= (int)$o['id'] ?></td>
            <td><?= e($o['username']) ?></td>
            <td>
                <?php foreach ($lines as $l): ?>
                    <?= e($l['product_name']) ?> &times; <?= (int)$l['quantity'] ?><br>
                <?php endforeach; ?>
            </td>
            <td><?= money($o['total']) ?></td>
            <td><?= e($o['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    </div>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
