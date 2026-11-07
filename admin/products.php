<?php
require __DIR__ . '/../config.php';
require_admin();

// ---- DELETE ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    csrf_check();
    $id = (int)$_POST['delete'];
    $st = db()->prepare('SELECT image FROM products WHERE id = ?');
    $st->execute([$id]);
    if ($row = $st->fetch()) {
        db()->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
        if ($row['image'] && is_file(UPLOAD_DIR . $row['image'])) {
            @unlink(UPLOAD_DIR . $row['image']);
        }
        flash('Product deleted.');
    }
    redirect('products.php');
}

// ---- READ ----
$products = db()->query('SELECT * FROM products ORDER BY id DESC')->fetchAll();

$title = 'Manage Products';
$root  = '../';
require __DIR__ . '/../includes/header.php';
?>
<main class="container">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
        <h2>Products</h2>
        <a class="btn-main" href="product_form.php">+ Add product</a>
    </div>

    <?php if (!$products): ?>
        <p class="empty">No products yet.</p>
    <?php else: ?>
    <div class="table-wrap">
    <table class="data">
        <tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Actions</th></tr>
        <?php foreach ($products as $p): ?>
        <tr>
            <td><img src="<?= e(product_image($p['image'], '../')) ?>" alt=""></td>
            <td><?= e($p['name']) ?></td>
            <td><?= e($p['category']) ?></td>
            <td><?= money($p['price']) ?></td>
            <td>
                <a class="btn-main btn-edit btn-sm" href="product_form.php?id=<?= (int)$p['id'] ?>">Edit</a>
                <form class="inline" method="post" onsubmit="return confirm('Delete this product?')">
                    <?= csrf_field() ?>
                    <button class="btn-main btn-del btn-sm" name="delete" value="<?= (int)$p['id'] ?>">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    </div>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
