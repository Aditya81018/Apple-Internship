<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/storage.php';
requireAdminAuth();

$message = '';
$error = '';
$product = null;

if (!isset($pdo) || !$pdo) {
    die("Database Connection Failed. Check your DB credentials in server/.env");
}

$id = $_GET['id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = trim($_POST['id'] ?? '');
    $name        = trim($_POST['name'] ?? '');
    $category    = trim($_POST['category'] ?? '');
    $price       = trim($_POST['price'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $imageUrl    = trim($_POST['image'] ?? '');

    // Handle File Upload to Supabase Storage
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadFileToStorage($_FILES['image_file']);
        if ($uploadResult['success']) {
            $imageUrl = $uploadResult['url'];
        } else {
            $error = "Image upload failed: " . ($uploadResult['error'] ?? 'Unknown error');
        }
    }

    if (empty($error)) {
        if (empty($id) || empty($name) || empty($price)) {
            $error = "Name and Price are required fields.";
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE products SET name = ?, category = ?, price = ?, image = ? WHERE id = ?");
                $stmt->execute([$name, $category, $price, $imageUrl, $id]);

                header("Location: admin_products.php?msg=updated");
                exit;
            } catch (\PDOException $e) {
                $error = "Database Error: " . $e->getMessage();
            }
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
    <title>Edit Product — Raj Confections CMS</title>
    <!-- Google Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --bg-page: #f8fafc;
            --card-bg: #ffffff;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --radius: 12px;
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-page);
            color: var(--text-dark);
            padding: 40px 20px;
        }

        .lucide {
            vertical-align: middle;
            stroke-width: 2.2px;
        }

        .container {
            max-width: 620px;
            margin: 0 auto;
            background: var(--card-bg);
            padding: 36px;
            border-radius: var(--radius);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-md);
        }

        .header {
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .header-icon {
            background: #eff6ff;
            color: var(--primary);
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #bfdbfe;
        }

        .header h1 {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-dark);
            letter-spacing: -0.02em;
        }

        .header p {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 6px;
        }

        input[type="text"], input[type="number"], input[type="file"], select, textarea {
            width: 100%;
            padding: 11px 14px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            font-size: 14px;
            font-family: inherit;
            outline: none;
            transition: all 0.2s;
            background: #ffffff;
        }

        input[readonly] {
            background: #f1f5f9;
            color: #64748b;
            cursor: not-allowed;
        }

        input[type="text"]:focus, input[type="number"]:focus, select:focus, textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        textarea {
            height: 90px;
            resize: vertical;
        }

        .img-preview {
            width: 90px;
            height: 90px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid var(--border-color);
            margin-top: 8px;
            background: #f1f5f9;
        }

        .note {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 6px;
        }

        .btn-group {
            margin-top: 28px;
            display: flex;
            gap: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 22px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--primary);
            color: #ffffff;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid #fecaca;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div class="header-icon">
            <i data-lucide="pencil" style="width:24px; height:24px;"></i>
        </div>
        <div>
            <h1>Edit Product</h1>
            <p>Modify catalog entry details or replace asset image</p>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert-error">
            <i data-lucide="alert-circle" style="width:18px; height:18px;"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" action="admin_product_edit.php?id=<?= urlencode($product['id']) ?>" enctype="multipart/form-data">
        <div class="form-group">
            <label for="id">Product ID / Slug (Read-only)</label>
            <input type="text" id="id" name="id" value="<?= htmlspecialchars($product['id']) ?>" readonly>
        </div>

        <div class="form-group">
            <label for="name">Product Name *</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
        </div>

        <div class="form-group">
            <label for="category">Category *</label>
            <?php $currentCat = strtolower($product['category'] ?? 'cake'); ?>
            <select id="category" name="category" required>
                <option value="cake" <?= (strpos($currentCat, 'addon') === false) ? 'selected' : '' ?>>Cake</option>
                <option value="addon" <?= (strpos($currentCat, 'addon') !== false) ? 'selected' : '' ?>>Addon</option>
            </select>
        </div>

        <div class="form-group">
            <label for="price">Price (₹) *</label>
            <input type="number" step="0.01" id="price" name="price" value="<?= htmlspecialchars($product['price']) ?>" required>
        </div>

        <div class="form-group">
            <label for="image_file">Upload New Image (Supabase Storage)</label>
            <input type="file" id="image_file" name="image_file" accept="image/*">
            <?php if (!empty($product['image'])): ?>
                <div style="display:flex; align-items:center; gap:12px; margin-top:8px;">
                    <img src="<?= htmlspecialchars($product['image']) ?>" alt="Current Image" class="img-preview" onerror="this.src='https://placehold.co/90x90?text=Cake'">
                    <div class="note">Current Image URL:<br><code style="font-size:11px; word-break:break-all;"><?= htmlspecialchars($product['image']) ?></code></div>
                </div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="image">Or Image URL</label>
            <input type="text" id="image" name="image" value="<?= htmlspecialchars($product['image'] ?? '') ?>" placeholder="https://... or /uploads/...">
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
        </div>

        <div class="btn-group">
            <button type="submit" class="btn btn-primary">
                <i data-lucide="save" style="width:16px; height:16px;"></i>
                <span>Update Product</span>
            </button>
            <a href="admin_products.php" class="btn btn-secondary">
                <i data-lucide="arrow-left" style="width:16px; height:16px;"></i>
                <span>Cancel</span>
            </a>
        </div>
    </form>
</div>

<script>
    lucide.createIcons();
</script>
</body>
</html>