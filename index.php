<?php
require __DIR__ . '/config.php';

// ---- ADD TO CART ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    csrf_check();
    require_login();
    $pid = (int)$_POST['product_id'];
    $chk = db()->prepare('SELECT id FROM products WHERE id = ?');
    $chk->execute([$pid]);
    if ($chk->fetch()) {
        $st = db()->prepare(
            'INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, 1)
             ON DUPLICATE KEY UPDATE quantity = quantity + 1'
        );
        $st->execute([current_user()['id'], $pid]);
        flash('Added to cart.');
    }
    redirect('index.php' . (!empty($_POST['back']) ? '?' . ltrim($_POST['back'], '?') : ''));
}

// ---- READ (search + category filter) ----
$q   = trim($_GET['q'] ?? '');
$cat = trim($_GET['category'] ?? '');

$sql    = 'SELECT * FROM products WHERE 1=1';
$params = [];
if ($q !== '')   { $sql .= ' AND (name LIKE ? OR description LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; }
if ($cat !== '') { $sql .= ' AND category = ?'; $params[] = $cat; }
$sql .= ' ORDER BY id DESC';

$st = db()->prepare($sql);
$st->execute($params);
$products   = $st->fetchAll();
$categories = db()->query('SELECT DISTINCT category FROM products ORDER BY category')->fetchAll(PDO::FETCH_COLUMN);
$back = http_build_query(array_filter(['q' => $q, 'category' => $cat]));

$title = 'Shop';
$root  = '';
require __DIR__ . '/includes/header.php';
?>
<section class="hero-banner">
    <img src="images/banner.jpg" alt="<?= e(SITE_NAME) ?> - Streetwear, skate &amp; athletic shoes">
</section>

<main class="container">
    <form class="toolbar" method="get" style="padding:0;display:flex;flex-direction:row;align-items:stretch">
        <input type="text" name="q" placeholder="Search products..." value="<?= e($q) ?>">
        <select name="category">
            <option value="">All categories</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= e($c) ?>" <?= $c === $cat ? 'selected' : '' ?>><?= e($c) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn-main" type="submit">Search</button>
    </form>

    <?php if (!$products): ?>
        <p class="empty">No products found.</p>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $p): ?>
                <div class="product">
                    <img src="<?= e(product_image($p['image'])) ?>" alt="<?= e($p['name']) ?>">
                    <h3><?= e($p['name']) ?></h3>
                    <p class="product-category"><?= e($p['category']) ?></p>
                    <p class="product-price"><?= money($p['price']) ?></p>
                    <p class="product-desc"><?= e($p['description']) ?></p>
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                        <input type="hidden" name="back" value="<?= e($back) ?>">
                        <button class="btn-add-cart" name="add_to_cart" value="1">Add to Cart</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
