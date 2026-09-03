<?php
$host = '127.0.0.1';
$db   = 'raj-confections-db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (\PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    header("Location: admin_products.php?msg=deleted");
    exit;
}

$stmt = $pdo->query("SELECT * FROM products ORDER BY id ASC");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - Raj Confections CMS</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f4f6f8; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        h1 { margin: 0; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; font-weight: 600; color: #495057; }
        tr:hover { background-color: #f1f1f1; }
        .btn { padding: 8px 14px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: 600; display: inline-block; }
        .btn-add { background: #28a745; color: white; }
        .btn-edit { background: #ffc107; color: #212529; margin-right: 5px; }
        .btn-delete { background: #dc3545; color: white; }
        .alert-success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .desc-col { max-width: 200px; color: #666; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>🍰 Raj Confections Admin CMS</h1>
        <a href="admin_product_add.php" class="btn btn-add">+ Add Product</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert-success">
            <?php
            if ($_GET['msg'] === 'added') echo "Product created successfully!";
            if ($_GET['msg'] === 'updated') echo "Product updated successfully!";
            if ($_GET['msg'] === 'deleted') echo "Product deleted successfully!";
            ?>
        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Weight</th>
                <th>Price (₹)</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: #777;">No products found in the database.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td><code><?= htmlspecialchars($p['id']) ?></code></td>
                        <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                        <td><?= htmlspecialchars($p['category'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($p['weight'] ?? '1 kg') ?></td>
                        <td>₹<?= number_format((float)$p['price'], 2) ?></td>
                        <td class="desc-col" title="<?= htmlspecialchars($p['description'] ?? '') ?>">
                            <?= htmlspecialchars($p['description'] ?? '-') ?>
                        </td>
                        <td>
                            <a href="admin_product_edit.php?id=<?= urlencode($p['id']) ?>" class="btn btn-edit">Edit</a>
                            <a href="admin_products.php?action=delete&id=<?= urlencode($p['id']) ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>