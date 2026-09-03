<?php
$host = '127.0.0.1';
$db   = 'raj-confections-db';
$user = 'root';
$pass = '';

$message = '';
$error = '';
$product = null;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (\PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

$id = $_GET['id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = trim($_POST['id'] ?? '');
    $name        = trim($_POST['name'] ?? '');
    $category    = trim($_POST['category'] ?? '');
    $weight      = trim($_POST['weight'] ?? '1 kg');
    $price       = trim($_POST['price'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($id) || empty($name) || empty($price)) {
        $error = "Name and Price are required fields.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE products SET name = ?, category = ?, weight = ?, price = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $category, $weight, $price, $description, $id]);

            header("Location: admin_products.php?msg=updated");
            exit;
        } catch (\PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}

if (!empty($id)) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$product) {
    die("Product not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Raj Confections CMS</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f4f6f8; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { margin-top: 0; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #555; }
        input[type="text"], input[type="number"], textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        textarea { height: 80px; }
        .btn-group { margin-top: 20px; display: flex; gap: 10px; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-weight: 600; display: inline-block; }
        .btn-primary { background: #28a745; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="container">
    <h1>✏️ Edit Product</h1>

    <?php if (!empty($error)): ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="admin_product_edit.php?id=<?= urlencode($product['id']) ?>">
        <div class="form-group">
            <label for="id">Product ID / Slug (Read-only)</label>
            <input type="text" id="id" name="id" value="<?= htmlspecialchars($product['id']) ?>" readonly style="background: #e9ecef;">
        </div>

        <div class="form-group">
            <label for="name">Product Name</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
        </div>

        <div class="form-group">
            <label for="category">Category</label>
            <input type="text" id="category" name="category" value="<?= htmlspecialchars($product['category'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="weight">Weight</label>
            <input type="text" id="weight" name="weight" value="<?= htmlspecialchars($product['weight'] ?? '1 kg') ?>">
        </div>

        <div class="form-group">
            <label for="price">Price (₹)</label>
            <input type="number" step="0.01" id="price" name="price" value="<?= htmlspecialchars($product['price']) ?>" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
        </div>

        <div class="btn-group">
            <button type="submit" class="btn btn-primary">Update Product</button>
            <a href="admin_products.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

</body>
</html>