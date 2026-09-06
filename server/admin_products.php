<?php
require_once __DIR__ . '/config/auth.php';
requireAdminAuth();

if (!isset($pdo) || !$pdo) {
    die("Database Connection Failed. Check your DB credentials in server/.env");
}

// Handle product delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    header("Location: admin_products.php?msg=deleted");
    exit;
}

// Handle toggle featured action
if (isset($_GET['action']) && $_GET['action'] === 'toggle_featured' && !empty($_GET['id'])) {
    $stmt = $pdo->prepare("UPDATE products SET is_featured = CASE WHEN is_featured = 1 THEN 0 ELSE 1 END WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    header("Location: admin_products.php?msg=toggled");
    exit;
}

$stmt = $pdo->query("SELECT * FROM products ORDER BY id ASC");
$products = $stmt->fetchAll();

// Calculate metrics
$totalProducts = count($products);
$cakeCount = 0;
$addonCount = 0;
$totalPrice = 0;

foreach ($products as $p) {
    $cat = strtolower($p['category'] ?? '');
    if (strpos($cat, 'addon') !== false) {
        $addonCount++;
    } else {
        $cakeCount++;
    }
    $totalPrice += (float)($p['price'] ?? 0);
}
$avgPrice = $totalProducts > 0 ? round($totalPrice / $totalProducts, 2) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raj Confections — Admin Catalog CMS</title>
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
            --danger-light: #fef2f2;
            --bg-page: #f8fafc;
            --card-bg: #ffffff;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --radius: 12px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
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
            box-shadow: var(--shadow-sm);
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

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: #f1f5f9;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--success);
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
        }

        /* Main Layout Container */
        .container {
            max-width: 1240px;
            margin: 32px auto 0;
            padding: 0 24px;
        }

        /* Metric Summary Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 20px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .stat-info .stat-label {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }

        .stat-info .stat-value {
            font-size: 26px;
            font-weight: 800;
            color: var(--text-dark);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-blue { background: #eff6ff; color: #2563eb; }
        .icon-pink { background: #fdf2f8; color: #db2777; }
        .icon-amber { background: #fffbeb; color: #d97706; }
        .icon-green { background: #ecfdf5; color: #059669; }

        /* Notification Banners */
        .toast {
            padding: 14px 20px;
            border-radius: var(--radius);
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            animation: fadeIn 0.3s ease;
        }

        .toast-success {
            background: var(--success-light);
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Toolbar & Filters */
        .content-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        .toolbar {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            background: #fafafa;
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 260px;
            max-width: 400px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 16px 10px 42px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            font-size: 14px;
            font-family: inherit;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .search-box input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .category-filters {
            display: flex;
            gap: 8px;
        }

        .filter-btn {
            padding: 8px 16px;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            background: #ffffff;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-btn.active, .filter-btn:hover {
            background: var(--text-dark);
            color: #ffffff;
            border-color: var(--text-dark);
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            transform: translateY(-1px);
        }

        .btn-outline {
            background: #ffffff;
            color: var(--text-dark);
            border: 1px solid var(--border-color);
        }

        .btn-outline:hover {
            background: #f1f5f9;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
            border-radius: 6px;
        }

        .btn-edit {
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
        }

        .btn-edit:hover {
            background: #2563eb;
            color: #ffffff;
        }

        .btn-delete {
            background: var(--danger-light);
            color: var(--danger);
            border: 1px solid #fecaca;
        }

        .btn-delete:hover {
            background: var(--danger);
            color: #ffffff;
        }

        /* Data Table */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
        }

        thead {
            background: #f8fafc;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            padding: 14px 20px;
            font-weight: 700;
            color: #475569;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        tbody tr {
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.15s ease;
        }

        tbody tr:hover {
            background-color: #f8fafc;
        }

        td {
            padding: 16px 20px;
            vertical-align: middle;
        }

        /* Product Cells */
        .product-cell {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .product-img-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            overflow: hidden;
            background: #f1f5f9;
            border: 1px solid var(--border-color);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-name {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 15px;
        }

        .product-id-badge {
            font-family: monospace;
            font-size: 11px;
            background: #f1f5f9;
            color: #64748b;
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 2px;
        }

        /* Category Badges */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: capitalize;
        }

        .badge-cake {
            background: #fce7f3;
            color: #be185d;
        }

        .badge-addon {
            background: #fef3c7;
            color: #b45309;
        }

        .badge-featured {
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-standard {
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .price-text {
            font-weight: 700;
            color: #059669;
            font-size: 15px;
        }

        .desc-text {
            color: var(--text-muted);
            max-width: 240px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 13px;
        }

        .action-cell {
            display: flex;
            gap: 8px;
        }

        .empty-state {
            padding: 60px 20px;
            text-align: center;
            color: var(--text-muted);
        }

        .empty-icon {
            margin-bottom: 12px;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

    <!-- Header Navigation Bar -->
    <nav class="navbar">
        <div class="brand-section">
            <div class="brand-icon">
                <i data-lucide="cake-slice" style="width:24px; height:24px;"></i>
            </div>
            <div>
                <div class="brand-title">Raj Confections</div>
                <div class="brand-subtitle">Catalog & Admin Management CMS</div>
            </div>
        </div>

        <div class="nav-actions">
            <div class="status-badge">
                <span class="status-dot"></span>
                <span>MariaDB / Supabase Ready</span>
            </div>
            <a href="admin_product_add.php" class="btn btn-primary">
                <i data-lucide="plus" style="width:16px; height:16px;"></i>
                <span>Add Product</span>
            </a>
            <a href="admin_login.php?action=logout" class="btn btn-outline btn-sm">
                <i data-lucide="log-out" style="width:16px; height:16px;"></i>
                <span>Logout (<?= htmlspecialchars($_SESSION['admin_id'] ?? 'admin') ?>)</span>
            </a>
        </div>
    </nav>

    <!-- Main Content Container -->
    <div class="container">

        <!-- Notification Toast -->
        <?php if (isset($_GET['msg'])): ?>
            <div class="toast toast-success" id="toastMsg">
                <div style="display:flex; align-items:center; gap:8px;">
                    <i data-lucide="check-circle-2" style="width:18px; height:18px;"></i>
                    <span>
                        <?php
                        if ($_GET['msg'] === 'added') echo "Product created successfully!";
                        if ($_GET['msg'] === 'updated') echo "Product updated successfully!";
                        if ($_GET['msg'] === 'deleted') echo "Product deleted successfully!";
                        if ($_GET['msg'] === 'toggled') echo "Product featured status updated successfully!";
                        ?>
                    </span>
                </div>
                <span style="cursor:pointer;" onclick="document.getElementById('toastMsg').remove()">
                    <i data-lucide="x" style="width:16px; height:16px;"></i>
                </span>
            </div>
        <?php endif; ?>

        <!-- Stat Summary Analytics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <div class="stat-label">Total Catalog Products</div>
                    <div class="stat-value"><?= $totalProducts ?></div>
                </div>
                <div class="stat-icon icon-blue">
                    <i data-lucide="package" style="width:24px; height:24px;"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <div class="stat-label">Custom Cakes</div>
                    <div class="stat-value"><?= $cakeCount ?></div>
                </div>
                <div class="stat-icon icon-pink">
                    <i data-lucide="cake" style="width:24px; height:24px;"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <div class="stat-label">Party Addons</div>
                    <div class="stat-value"><?= $addonCount ?></div>
                </div>
                <div class="stat-icon icon-amber">
                    <i data-lucide="sparkles" style="width:24px; height:24px;"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <div class="stat-label">Avg Product Price</div>
                    <div class="stat-value">₹<?= number_format($avgPrice, 2) ?></div>
                </div>
                <div class="stat-icon icon-green">
                    <i data-lucide="tag" style="width:24px; height:24px;"></i>
                </div>
            </div>
        </div>

        <!-- Main Data Card -->
        <div class="content-card">
            <!-- Toolbar & Filter Controls -->
            <div class="toolbar">
                <div class="search-box">
                    <span class="search-icon">
                        <i data-lucide="search" style="width:18px; height:18px;"></i>
                    </span>
                    <input type="text" id="searchInput" placeholder="Search by name, category, or ID..." onkeyup="filterTable()">
                </div>

                <div class="category-filters">
                    <button class="filter-btn active" onclick="setCategoryFilter('all', this)">All (<?= $totalProducts ?>)</button>
                    <button class="filter-btn" onclick="setCategoryFilter('cake', this)">Cakes (<?= $cakeCount ?>)</button>
                    <button class="filter-btn" onclick="setCategoryFilter('addon', this)">Addons (<?= $addonCount ?>)</button>
                </div>
            </div>

            <!-- Products Data Table -->
            <div class="table-responsive">
                <table id="productsTable">
                    <thead>
                        <tr>
                            <th>Product Info</th>
                            <th>Category</th>
                            <th>Featured</th>
                            <th>Standard Weight</th>
                            <th>Price (₹)</th>
                            <th>Description</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <i data-lucide="inbox" style="width:48px; height:48px;"></i>
                                        </div>
                                        <h3>No products found</h3>
                                        <p>Click "+ Add Product" to create your first item in the catalog.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $p): 
                                $categoryLower = strtolower($p['category'] ?? 'cake');
                                $badgeClass = (strpos($categoryLower, 'addon') !== false) ? 'badge-addon' : 'badge-cake';
                                $imgSrc = !empty($p['image']) ? htmlspecialchars($p['image']) : 'https://placehold.co/100x100?text=Cake';
                            ?>
                                <tr data-category="<?= htmlspecialchars($categoryLower) ?>" data-search="<?= htmlspecialchars(strtolower($p['name'] . ' ' . $p['id'] . ' ' . ($p['category'] ?? ''))) ?>">
                                    <td>
                                        <div class="product-cell">
                                            <div class="product-img-wrapper">
                                                <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="product-img" onerror="this.src='https://placehold.co/100x100?text=Cake'">
                                            </div>
                                            <div>
                                                <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
                                                <span class="product-id-badge"><?= htmlspecialchars($p['id']) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($p['category'] ?? 'Cake') ?></span>
                                    </td>
                                    <td>
                                        <?php $isFeatured = !empty($p['is_featured']); ?>
                                        <a href="admin_products.php?action=toggle_featured&id=<?= urlencode($p['id']) ?>" class="badge <?= $isFeatured ? 'badge-featured' : 'badge-standard' ?>" title="Click to toggle featured status">
                                            <i data-lucide="<?= $isFeatured ? 'star' : 'star-off' ?>" style="width:12px; height:12px;"></i>
                                            <span><?= $isFeatured ? 'Featured' : 'Standard' ?></span>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($p['weight'] ?? '1 kg') ?></td>
                                    <td>
                                        <span class="price-text">₹<?= number_format((float)$p['price'], 2) ?></span>
                                    </td>
                                    <td>
                                        <div class="desc-text" title="<?= htmlspecialchars($p['description'] ?? '') ?>">
                                            <?= htmlspecialchars($p['description'] ?? 'No description provided.') ?>
                                        </div>
                                    </td>
                                    <td style="text-align: right;">
                                        <div class="action-cell" style="justify-content: flex-end;">
                                            <a href="admin_product_edit.php?id=<?= urlencode($p['id']) ?>" class="btn btn-sm btn-edit">
                                                <i data-lucide="pencil" style="width:14px; height:14px;"></i>
                                                <span>Edit</span>
                                            </a>
                                            <a href="admin_products.php?action=delete&id=<?= urlencode($p['id']) ?>" class="btn btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete \'<?= htmlspecialchars($p['name']) ?>\'?');">
                                                <i data-lucide="trash-2" style="width:14px; height:14px;"></i>
                                                <span>Delete</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Real-time Filter & Search Script -->
    <script>
        let currentFilter = 'all';

        function filterTable() {
            const query = document.getElementById('searchInput').value.toLowerCase().trim();
            const rows = document.querySelectorAll('#productsTable tbody tr');

            rows.forEach(row => {
                const searchData = row.getAttribute('data-search') || '';
                const categoryData = row.getAttribute('data-category') || '';

                const matchesSearch = query === '' || searchData.includes(query);
                const matchesCategory = currentFilter === 'all' || categoryData.includes(currentFilter);

                if (matchesSearch && matchesCategory) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function setCategoryFilter(category, btn) {
            currentFilter = category;
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            filterTable();
        }

        // Initialize Lucide Icons
        lucide.createIcons();
    </script>
</body>
</html>