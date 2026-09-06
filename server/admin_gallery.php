<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/storage.php';
requireAdminAuth();

$message = '';
$error = '';

// Load gallery items (DB with JSON fallback)
function fetchGalleryItems() {
    global $pdo;
    $items = [];
    if (isset($pdo) && $pdo) {
        try {
            $stmt = $pdo->query("SELECT * FROM gallery ORDER BY id DESC");
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($items as &$item) {
                $item['id'] = (int)$item['id'];
                $item['is_featured'] = (int)($item['is_featured'] ?? 0);
            }
            return $items;
        } catch (\Exception $e) {
            // Fall back
        }
    }

    $jsonFile = __DIR__ . '/data/gallery.json';
    if (file_exists($jsonFile)) {
        return json_decode(file_get_contents($jsonFile), true) ?? [];
    }
    return [];
}

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'add';

    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? 'Celebration Cakes');
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $imageUrl = trim($_POST['image_url'] ?? '');

        // Upload file if provided
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadFileToStorage($_FILES['image_file']);
            if ($uploadResult['success']) {
                $imageUrl = $uploadResult['url'];
            } else {
                $error = "Image upload failed: " . ($uploadResult['error'] ?? 'Unknown error');
            }
        }

        if (empty($error)) {
            if (empty($title) || empty($imageUrl)) {
                $error = "Title and Image are required.";
            } else {
                $newId = time();
                $createdAt = date('Y-m-d H:i:s');

                if (isset($pdo) && $pdo) {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO gallery (title, category, image, is_featured, created_at) VALUES (?, ?, ?, ?, NOW())");
                        $stmt->execute([$title, $category, $imageUrl, $isFeatured]);
                        $newId = (int)$pdo->lastInsertId();
                    } catch (\Exception $e) {
                        // DB fallback handled
                    }
                }

                // Sync JSON file
                $jsonFile = __DIR__ . '/data/gallery.json';
                $jsonItems = [];
                if (file_exists($jsonFile)) {
                    $jsonItems = json_decode(file_get_contents($jsonFile), true) ?? [];
                }
                $newItem = [
                    "id" => $newId,
                    "title" => $title,
                    "category" => $category,
                    "image" => $imageUrl,
                    "is_featured" => $isFeatured,
                    "created_at" => $createdAt
                ];
                array_unshift($jsonItems, $newItem);
                file_put_contents($jsonFile, json_encode($jsonItems, JSON_PRETTY_PRINT));

                header("Location: admin_gallery.php?msg=added");
                exit;
            }
        }
    }

    if ($action === 'toggle_featured') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            if (isset($pdo) && $pdo) {
                try {
                    $stmt = $pdo->prepare("UPDATE gallery SET is_featured = 1 - is_featured WHERE id = ?");
                    $stmt->execute([$id]);
                } catch (\Exception $e) {
                    // Fall back
                }
            }

            $jsonFile = __DIR__ . '/data/gallery.json';
            if (file_exists($jsonFile)) {
                $jsonItems = json_decode(file_get_contents($jsonFile), true) ?? [];
                foreach ($jsonItems as &$item) {
                    if ($item['id'] == $id) {
                        $item['is_featured'] = 1 - ($item['is_featured'] ?? 0);
                        break;
                    }
                }
                file_put_contents($jsonFile, json_encode($jsonItems, JSON_PRETTY_PRINT));
            }

            header("Location: admin_gallery.php?msg=toggled");
            exit;
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            if (isset($pdo) && $pdo) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
                    $stmt->execute([$id]);
                } catch (\Exception $e) {
                    // Fall back
                }
            }

            $jsonFile = __DIR__ . '/data/gallery.json';
            if (file_exists($jsonFile)) {
                $jsonItems = json_decode(file_get_contents($jsonFile), true) ?? [];
                $jsonItems = array_values(array_filter($jsonItems, fn($item) => $item['id'] != $id));
                file_put_contents($jsonFile, json_encode($jsonItems, JSON_PRETTY_PRINT));
            }

            header("Location: admin_gallery.php?msg=deleted");
            exit;
        }
    }
}

$galleryItems = fetchGalleryItems();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creations Gallery Manager — Raj Confections CMS</title>
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
            --success: #10b981;
            --success-light: #ecfdf5;
            --warning: #f59e0b;
            --danger: #ef4444;
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
            line-height: 1.5;
            padding-bottom: 60px;
        }

        .lucide {
            vertical-align: middle;
            stroke-width: 2.2px;
        }

        /* Top Navigation Bar */
        .navbar {
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .brand-section {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-icon {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: var(--primary);
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #bfdbfe;
        }

        .brand-title {
            font-size: 20px;
            font-weight: 800;
            color: var(--text-dark);
            letter-spacing: -0.02em;
        }

        .brand-subtitle {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-link {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .nav-link:hover, .nav-link.active {
            background: #f1f5f9;
            color: var(--primary);
        }

        .btn-logout {
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-dark);
            border: 1px solid var(--border-color);
            text-decoration: none;
            background: #ffffff;
            transition: all 0.2s;
        }

        .btn-logout:hover {
            background: #f1f5f9;
        }

        /* Main Container */
        .container {
            max-width: 1040px;
            margin: 40px auto 0;
            padding: 0 24px;
        }

        .card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 32px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-md);
            margin-bottom: 28px;
        }

        .card-header {
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 16px;
        }

        .card-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-header h2 {
            font-size: 18px;
            font-weight: 800;
            color: var(--text-dark);
        }

        .grid-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 8px;
        }

        input[type="text"], select, input[type="file"] {
            width: 100%;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            font-size: 14px;
            font-family: inherit;
            outline: none;
            transition: all 0.2s;
            background: #ffffff;
        }

        input[type="text"]:focus, select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            user-select: none;
            font-weight: 600;
            font-size: 14px;
            color: var(--text-dark);
            margin-top: 6px;
        }

        .checkbox-label input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .btn-submit {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 800;
            color: #ffffff;
            background: var(--primary);
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-submit:hover {
            background: var(--primary-hover);
        }

        /* Gallery Grid Layout */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
            margin-top: 12px;
        }

        .gallery-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
            display: flex;
            flex-direction: column;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .gallery-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .gallery-img-container {
            position: relative;
            width: 100%;
            height: 200px;
            background: #f1f5f9;
            overflow: hidden;
        }

        .gallery-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .featured-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            color: #d97706;
            border: 1px solid #fde68a;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 4px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .category-badge {
            position: absolute;
            bottom: 12px;
            left: 12px;
            background: rgba(15, 23, 42, 0.8);
            color: #ffffff;
            backdrop-filter: blur(4px);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
        }

        .gallery-card-body {
            padding: 16px;
            display: flex;
            flex-direction: column;
            flex: 1;
            justify-content: space-between;
        }

        .gallery-item-title {
            font-size: 15px;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 12px;
        }

        .gallery-card-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            border-top: 1px solid var(--border-color);
            padding-top: 12px;
        }

        .btn-toggle-featured {
            flex: 1;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid var(--border-color);
            background: #f8fafc;
            color: var(--text-dark);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn-toggle-featured.is-active {
            background: #fffbeb;
            color: #b45309;
            border-color: #fde68a;
        }

        .btn-toggle-featured:hover {
            background: #f1f5f9;
        }

        .btn-delete {
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: var(--danger);
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-delete:hover {
            background: #fee2e2;
        }

        .toast-success {
            padding: 14px 20px;
            border-radius: 12px;
            background: var(--success-light);
            color: #065f46;
            border: 1px solid #a7f3d0;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            padding: 14px 20px;
            border-radius: 12px;
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 24px;
        }

        .empty-state {
            text-align: center;
            padding: 48px 24px;
            color: var(--text-muted);
        }

        .empty-state i {
            margin-bottom: 12px;
            color: #cbd5e1;
        }
    </style>
</head>
<body>

    <!-- Header Navigation Bar -->
    <nav class="navbar">
        <div class="brand-section">
            <div class="brand-icon">
                <i data-lucide="camera" style="width:24px; height:24px;"></i>
            </div>
            <div>
                <div class="brand-title">Raj Confections</div>
                <div class="brand-subtitle">Creations Gallery CMS</div>
            </div>
        </div>

        <div class="nav-links">
            <a href="admin_dashboard.php" class="nav-link">
                <i data-lucide="home" style="width:16px; height:16px;"></i>
                <span>Dashboard</span>
            </a>
            <a href="admin_orders.php" class="nav-link">
                <i data-lucide="shopping-bag" style="width:16px; height:16px;"></i>
                <span>Orders</span>
            </a>
            <a href="admin_products.php" class="nav-link">
                <i data-lucide="package" style="width:16px; height:16px;"></i>
                <span>Products Catalog</span>
            </a>
            <a href="admin_gallery.php" class="nav-link active">
                <i data-lucide="camera" style="width:16px; height:16px;"></i>
                <span>Creations Gallery</span>
            </a>
            <a href="admin_assets.php" class="nav-link">
                <i data-lucide="image" style="width:16px; height:16px;"></i>
                <span>Assets & Status</span>
            </a>
        </div>

        <div>
            <a href="admin_login.php?action=logout" class="btn-logout">
                <i data-lucide="log-out" style="width:14px; height:14px;"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container">

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'added'): ?>
            <div class="toast-success">
                <i data-lucide="check-circle-2" style="width:18px; height:18px;"></i>
                <span>New gallery creation item added successfully!</span>
            </div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'toggled'): ?>
            <div class="toast-success">
                <i data-lucide="sparkles" style="width:18px; height:18px;"></i>
                <span>Featured status updated!</span>
            </div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <div class="toast-success">
                <i data-lucide="trash-2" style="width:18px; height:18px;"></i>
                <span>Gallery item deleted.</span>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert-error">
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <!-- Card 1: Add New Gallery Creation -->
        <div class="card">
            <div class="card-header">
                <div class="card-header-left">
                    <i data-lucide="plus-circle" style="width:22px; height:22px; color:var(--primary);"></i>
                    <h2>Add New Creation to Gallery</h2>
                </div>
            </div>

            <form method="POST" action="admin_gallery.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">

                <div class="grid-form">
                    <div class="form-group">
                        <label for="title">Creation Title / Name *</label>
                        <input type="text" id="title" name="title" required placeholder="e.g. Inspirational Mango Glaze Cake">
                    </div>

                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" required>
                            <option value="Celebration Cakes">Celebration Cakes</option>
                            <option value="Wedding & Tier">Wedding & Tier Cakes</option>
                            <option value="Pastry & Cupcakes">Pastry & Cupcakes</option>
                            <option value="Custom Creations">Custom Creations</option>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label for="image_file">Upload Creation Image File</label>
                        <input type="file" id="image_file" name="image_file" accept="image/*">
                        <div style="margin-top: 8px;">
                            <input type="text" id="image_url" name="image_url" placeholder="Or enter direct Image URL (https://...)">
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_featured" value="1">
                            <span>Set as Featured Image (Appears in Custom Cake Inspiration Grid)</span>
                        </label>
                    </div>
                </div>

                <div style="text-align: right; margin-top: 12px;">
                    <button type="submit" class="btn-submit">
                        <i data-lucide="upload-cloud" style="width:16px; height:16px;"></i>
                        <span>Upload to Gallery</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Card 2: Gallery Showcase Items -->
        <div class="card">
            <div class="card-header">
                <div class="card-header-left">
                    <i data-lucide="grid" style="width:22px; height:22px; color:var(--primary);"></i>
                    <h2>Gallery Showcase Items (<?= count($galleryItems) ?>)</h2>
                </div>
            </div>

            <?php if (empty($galleryItems)): ?>
                <div class="empty-state">
                    <i data-lucide="image-off" style="width:48px; height:48px;"></i>
                    <p style="font-weight: 600;">No gallery items found.</p>
                    <p style="font-size: 13px;">Add your first creation above to start showcasing past cakes!</p>
                </div>
            <?php else: ?>
                <div class="gallery-grid">
                    <?php foreach ($galleryItems as $item): ?>
                        <div class="gallery-card">
                            <div class="gallery-img-container">
                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="gallery-img" onerror="this.src='https://placehold.co/400x300?text=Cake'">
                                <?php if (!empty($item['is_featured'])): ?>
                                    <div class="featured-badge">
                                        <i data-lucide="star" style="width:12px; height:12px; fill:#d97706;"></i>
                                        <span>Featured</span>
                                    </div>
                                <?php endif; ?>
                                <div class="category-badge">
                                    <?= htmlspecialchars($item['category']) ?>
                                </div>
                            </div>
                            <div class="gallery-card-body">
                                <div class="gallery-item-title">
                                    <?= htmlspecialchars($item['title']) ?>
                                </div>

                                <div class="gallery-card-actions">
                                    <form method="POST" action="admin_gallery.php" style="flex:1;">
                                        <input type="hidden" name="action" value="toggle_featured">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn-toggle-featured <?= !empty($item['is_featured']) ? 'is-active' : '' ?>">
                                            <i data-lucide="star" style="width:14px; height:14px; <?= !empty($item['is_featured']) ? 'fill:#b45309;' : '' ?>"></i>
                                            <span><?= !empty($item['is_featured']) ? 'Featured' : 'Make Featured' ?></span>
                                        </button>
                                    </form>

                                    <form method="POST" action="admin_gallery.php" onsubmit="return confirm('Are you sure you want to delete this gallery item?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn-delete" title="Delete Image">
                                            <i data-lucide="trash-2" style="width:14px; height:14px;"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
