<?php
require __DIR__ . '/../config.php';
require_admin();

$id      = (int)($_GET['id'] ?? 0);
$product = ['name' => '', 'category' => '', 'price' => '', 'description' => '', 'image' => null];

if ($id) {
    $st = db()->prepare('SELECT * FROM products WHERE id = ?');
    $st->execute([$id]);
    $product = $st->fetch();
    if (!$product) {
        flash('Product not found.', 'err');
        redirect('products.php');
    }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name     = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price    = $_POST['price'] ?? '';
    $desc     = trim($_POST['description'] ?? '');

    if ($name === '' || mb_strlen($name) > 120)     $errors[] = 'Name is required (max 120 characters).';
    if ($category === '' || mb_strlen($category) > 60) $errors[] = 'Category is required (max 60 characters).';
    if (!is_numeric($price) || $price < 0 || $price > 99999999) $errors[] = 'Enter a valid price.';

    // ---- image upload (optional) ----
    $newImage = null;
    if (!empty($_FILES['image']['name'])) {
        $f = $_FILES['image'];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Image upload failed.';
        } elseif ($f['size'] > MAX_IMG_MB * 1024 * 1024) {
            $errors[] = 'Image must be ' . MAX_IMG_MB . ' MB or smaller.';
        } else {
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
            if (!isset($allowed[$mime])) {
                $errors[] = 'Only JPG, PNG, WEBP or GIF images are allowed.';
            } else {
                $newImage = bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
            }
        }
    }

    $product = array_merge($product, ['name' => $name, 'category' => $category, 'price' => $price, 'description' => $desc]);

    if (!$errors) {
        if ($newImage && !move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_DIR . $newImage)) {
            $errors[] = 'Could not save the image. Check the uploads folder permissions.';
        }
    }

    if (!$errors) {
        if ($id) {
            // ---- UPDATE ----
            $oldImage = $product['image'];
            $image    = $newImage ?: $oldImage;
            $st = db()->prepare('UPDATE products SET name=?, category=?, price=?, description=?, image=? WHERE id=?');
            $st->execute([$name, $category, $price, $desc, $image, $id]);
            if ($newImage && $oldImage && is_file(UPLOAD_DIR . $oldImage)) @unlink(UPLOAD_DIR . $oldImage);
            flash('Product updated.');
        } else {
            // ---- CREATE ----
            $st = db()->prepare('INSERT INTO products (name, category, price, description, image) VALUES (?,?,?,?,?)');
            $st->execute([$name, $category, $price, $desc, $newImage]);
            flash('Product added.');
        }
        redirect('products.php');
    }
}

$title = $id ? 'Edit Product' : 'Add Product';
$root  = '../';
require __DIR__ . '/../includes/header.php';
?>
<main class="container">
    <div class="panel">
        <h2><?= e($title) ?></h2>

        <?php if ($errors): ?>
            <ul class="error-list"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <label>Name</label>
            <input type="text" name="name" value="<?= e($product['name']) ?>" required>

            <label>Category</label>
            <input type="text" name="category" value="<?= e($product['category']) ?>" required>

            <label>Price</label>
            <input type="number" name="price" step="0.01" min="0" value="<?= e($product['price']) ?>" required>

            <label>Description</label>
            <textarea name="description"><?= e($product['description']) ?></textarea>

            <label>Image (JPG/PNG/WEBP/GIF, max <?= MAX_IMG_MB ?> MB)</label>
            <input type="file" name="image" accept="image/*">
            <?php if ($id && $product['image']): ?>
                <img class="current-img" src="<?= e(product_image($product['image'], '../')) ?>" alt="Current image">
            <?php endif; ?>

            <div class="actions">
                <button class="btn-main" type="submit"><?= $id ? 'Save changes' : 'Add product' ?></button>
                <a class="btn-main btn-del" href="products.php">Cancel</a>
            </div>
        </form>
    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
