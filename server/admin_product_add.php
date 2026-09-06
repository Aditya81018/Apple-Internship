<?php
require_once __DIR__ . '/config/database.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = trim($_POST['id'] ?? '');
    $name        = trim($_POST['name'] ?? '');
    $category    = trim($_POST['category'] ?? '');
    $weight      = trim($_POST['weight'] ?? '1 kg');
    $price       = trim($_POST['price'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($id) || empty($name) || empty($price)) {
        $error = "ID, Name, and Price are required fields.";
    } else {
        try {
            if (!isset($pdo) || !$pdo) {
                throw new Exception("Database not connected.");
            }

            $stmt = $pdo->prepare("INSERT INTO products (id, name, category, weight, price, description) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id, $name, $category, $weight, $price, $description]);

            header("Location: admin_products.php?msg=added");
            exit;
        } catch (\PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Raj Confections CMS</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f4f6f8; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { margin-top: 0; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #555; }
        input[type="text"], input[type="number"], textarea, select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        textarea { height: 80px; }
        .btn-group { margin-top: 20px; display: flex; gap: 10px; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-weight: 600; display: inline-block; }
        .btn-primary { background: #007bff; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="container">
    <h1>+ Add New Product</h1>

    <?php if (!empty($error)): ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="admin_product_add.php">
        <div class="form-group">
            <label for="id">Product ID / Slug (e.g. red-velvet-cake)</label>
            <input type="text" id="id" name="id" placeholder="red-velvet-cake" required>
        </div>

        <div class="form-group">
            <label for="name">Product Name</label>
            <input type="text" id="name" name="name" placeholder="Tasty Cake" required>
        </div>

        <div class="form-group">
            <label for="category">Category</label>
            <input type="text" id="category" name="category" placeholder="Cakes">
        </div>

        <div class="form-group">
            <label for="weight">Weight (e.g. 1 kg, 500g)</label>
            <input type="text" id="weight" name="weight" value="1 kg">
        </div>

        <div class="form-group">
            <label for="price">Price (₹)</label>
            <input type="number" step="0.01" id="price" name="price" placeholder="450.00" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" placeholder="Enter product description..."></textarea>
        </div>

        <div class="btn-group">
            <button type="submit" class="btn btn-primary">Save Product</button>
            <a href="admin_products.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

</body>
</html>