<?php
require_once __DIR__ . '/config/auth.php';
requireAdminAuth();

// Load current settings (DB or JSON fallback)
$settings = [];
$jsonFile = __DIR__ . '/data/settings.json';
if (file_exists($jsonFile)) {
    $settings = json_decode(file_get_contents($jsonFile), true) ?? [];
}

if (isset($pdo) && $pdo) {
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
        $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        if (!empty($rows)) {
            $settings = array_merge($settings, $rows);
        }
    } catch (\Exception $e) {
        // Use JSON fallback
    }
}

// Handle quick order status toggle
if (isset($_GET['action']) && $_GET['action'] === 'toggle_orders') {
    $currentStatus = ($settings['accepting_orders'] ?? '1') === '1' ? '0' : '1';
    $settings['accepting_orders'] = $currentStatus;

    file_put_contents($jsonFile, json_encode($settings, JSON_PRETTY_PRINT));

    if (isset($pdo) && $pdo) {
        try {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('accepting_orders', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            $stmt->execute([$currentStatus]);
        } catch (\Exception $e) {
            // DB fallback handled
        }
    }

    header("Location: admin_dashboard.php?msg=status_updated");
    exit;
}

// Fetch product stats
$totalProducts = 0;
$featuredCount = 0;
$cakeCount = 0;
$addonCount = 0;

if (isset($pdo) && $pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM products");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalProducts = count($products);
        foreach ($products as $p) {
            if (!empty($p['is_featured'])) $featuredCount++;
            $cat = strtolower($p['category'] ?? '');
            if (strpos($cat, 'addon') !== false) {
                $addonCount++;
            } else {
                $cakeCount++;
            }
        }
    } catch (\Exception $e) {
        // Ignore DB errors
    }
} else {
    // Fallback counts from products.json
    $prodJson = __DIR__ . '/../website/src/data/products.json';
    if (file_exists($prodJson)) {
        $products = json_decode(file_get_contents($prodJson), true) ?? [];
        $totalProducts = count($products);
        foreach ($products as $p) {
            if (!empty($p['is_featured'])) $featuredCount++;
            $cat = strtolower($p['category'] ?? '');
            if (strpos($cat, 'addon') !== false) {
                $addonCount++;
            } else {
                $cakeCount++;
            }
        }
    }
}

$totalGalleryCount = 0;
$featuredGalleryCount = 0;

$jsonGalleryFile = __DIR__ . '/data/gallery.json';
if (isset($pdo) && $pdo) {
    try {
        $stmtG = $pdo->query("SELECT * FROM gallery");
        $gItems = $stmtG->fetchAll(PDO::FETCH_ASSOC);
        $totalGalleryCount = count($gItems);
        foreach ($gItems as $gi) {
            if (!empty($gi['is_featured'])) $featuredGalleryCount++;
        }
    } catch (\Exception $e) {
        if (file_exists($jsonGalleryFile)) {
            $gItems = json_decode(file_get_contents($jsonGalleryFile), true) ?? [];
            $totalGalleryCount = count($gItems);
            foreach ($gItems as $gi) {
                if (!empty($gi['is_featured'])) $featuredGalleryCount++;
            }
        }
    }
} else if (file_exists($jsonGalleryFile)) {
    $gItems = json_decode(file_get_contents($jsonGalleryFile), true) ?? [];
    $totalGalleryCount = count($gItems);
}

$totalOrdersCount = 0;
$pendingOrdersCount = 0;
$recentOrders = [];

$jsonOrdersFile = __DIR__ . '/data/orders.json';
if (isset($pdo) && $pdo) {
    try {
        $stmtO = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5");
        $recentOrders = $stmtO->fetchAll(PDO::FETCH_ASSOC);

        $stmtCount = $pdo->query("SELECT COUNT(*) FROM orders");
        $totalOrdersCount = (int)$stmtCount->fetchColumn();

        $stmtPending = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'PENDING_CONFIRMATION' OR status = 'PENDING'");
        $pendingOrdersCount = (int)$stmtPending->fetchColumn();
    } catch (\Exception $e) {
        if (file_exists($jsonOrdersFile)) {
            $allO = json_decode(file_get_contents($jsonOrdersFile), true) ?? [];
            $totalOrdersCount = count($allO);
            $recentOrders = array_slice($allO, 0, 5);
            foreach ($allO as $o) {
                $st = strtoupper($o['status'] ?? '');
                if ($st === 'PENDING_CONFIRMATION' || $st === 'PENDING') $pendingOrdersCount++;
            }
        }
    }
} else if (file_exists($jsonOrdersFile)) {
    $allO = json_decode(file_get_contents($jsonOrdersFile), true) ?? [];
    $totalOrdersCount = count($allO);
    $recentOrders = array_slice($allO, 0, 5);
    foreach ($allO as $o) {
        $st = strtoupper($o['status'] ?? '');
        if ($st === 'PENDING_CONFIRMATION' || $st === 'PENDING') $pendingOrdersCount++;
    }
}

$isAcceptingOrders = ($settings['accepting_orders'] ?? '1') === '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raj Confections — Admin Control Center</title>
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
            --radius: 16px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
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

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
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

        /* Container */
        .container {
            max-width: 1140px;
            margin: 40px auto 0;
            padding: 0 24px;
        }

        /* Welcome & Status Banner */
        .status-banner {
            background: #ffffff;
            border-radius: var(--radius);
            padding: 28px 32px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-md);
            margin-bottom: 32px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .status-info h1 {
            font-size: 24px;
            font-weight: 800;
            color: var(--text-dark);
            letter-spacing: -0.02em;
            margin-bottom: 4px;
        }

        .status-info p {
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .status-indicator-box {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 12px 20px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }

        .status-indicator-box.online {
            background: #ecfdf5;
            border-color: #a7f3d0;
        }

        .status-indicator-box.paused {
            background: #fffbeb;
            border-color: #fde68a;
        }

        .pulse-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .online .pulse-dot {
            background: var(--success);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2);
        }

        .paused .pulse-dot {
            background: var(--warning);
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.2);
        }

        .status-text {
            display: flex;
            flex-col: column;
        }

        .status-title {
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .online .status-title { color: #065f46; }
        .paused .status-title { color: #92400e; }

        .btn-toggle-status {
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .online .btn-toggle-status {
            background: #ffffff;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .online .btn-toggle-status:hover {
            background: #d1fae5;
        }

        .paused .btn-toggle-status {
            background: #ffffff;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .paused .btn-toggle-status:hover {
            background: #fef3c7;
        }

        /* Section Title */
        .section-header {
            margin-bottom: 20px;
        }

        .section-header h2 {
            font-size: 18px;
            font-weight: 800;
            color: var(--text-dark);
        }

        /* Dashboard Module Grid */
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 28px;
        }

        .module-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 32px;
            border: 2px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
        }

        .module-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .module-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .module-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-products { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
        .icon-assets { background: #fdf2f8; color: #db2777; border: 1px solid #fbcfe8; }

        .module-badge {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 4px 10px;
            border-radius: 20px;
            background: #f1f5f9;
            color: var(--text-muted);
        }

        .module-title {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .module-desc {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 24px;
            line-height: 1.6;
        }

        .module-stats {
            display: flex;
            gap: 20px;
            margin-bottom: 28px;
            padding-top: 16px;
            border-top: 1px solid var(--border-color);
        }

        .stat-item .label {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .stat-item .val {
            font-size: 20px;
            font-weight: 800;
            color: var(--text-dark);
        }

        .btn-module {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 20px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            color: #ffffff;
            background: var(--primary);
            transition: background 0.2s, transform 0.2s;
        }

        .btn-module:hover {
            background: var(--primary-hover);
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
    </style>
</head>
<body>

    <!-- Header Navigation Bar -->
    <nav class="navbar">
        <div class="brand-section">
            <div class="brand-icon">
                <i data-lucide="layout-dashboard" style="width:24px; height:24px;"></i>
            </div>
            <div>
                <div class="brand-title">Raj Confections</div>
                <div class="brand-subtitle">Admin Control Center</div>
            </div>
        </div>

        <div class="nav-links">
            <a href="admin_dashboard.php" class="nav-link active">
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
            <a href="admin_gallery.php" class="nav-link">
                <i data-lucide="camera" style="width:16px; height:16px;"></i>
                <span>Creations Gallery</span>
            </a>
            <a href="admin_assets.php" class="nav-link">
                <i data-lucide="image" style="width:16px; height:16px;"></i>
                <span>Assets & Status</span>
            </a>
        </div>

        <div class="nav-actions">
            <a href="admin_login.php?action=logout" class="btn-logout">
                <i data-lucide="log-out" style="width:14px; height:14px;"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>

    <!-- Main Content Container -->
    <div class="container">

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'status_updated'): ?>
            <div class="toast-success">
                <i data-lucide="check-circle-2" style="width:18px; height:18px;"></i>
                <span>Store order acceptance status updated successfully!</span>
            </div>
        <?php endif; ?>

        <!-- Welcome & Live Status Banner -->
        <div class="status-banner">
            <div class="status-info">
                <h1>Welcome, Administrator</h1>
                <p>Manage catalog cakes, store image assets, and live order acceptance status.</p>
            </div>

            <div class="status-indicator-box <?= $isAcceptingOrders ? 'online' : 'paused' ?>">
                <div class="pulse-dot"></div>
                <div class="status-text">
                    <div class="status-title"><?= $isAcceptingOrders ? 'Accepting Orders' : 'Order Taking Paused' ?></div>
                </div>
                <a href="admin_dashboard.php?action=toggle_orders" class="btn-toggle-status">
                    <?= $isAcceptingOrders ? 'Pause Orders' : 'Resume Orders' ?>
                </a>
            </div>
        </div>

        <div class="section-header">
            <h2>Management Modules</h2>
        </div>

        <!-- Dashboard Modules Grid -->
        <div class="modules-grid">

            <!-- Module 1: Customer Orders CMS -->
            <div class="module-card">
                <div>
                    <div class="module-header">
                        <div class="module-icon" style="background: #eef2ff; color: #4f46e5; border: 1px solid #c7d2fe;">
                            <i data-lucide="shopping-bag" style="width:28px; height:28px;"></i>
                        </div>
                        <span class="module-badge" style="background:#e0e7ff; color:#3730a3;">Live Orders</span>
                    </div>

                    <div class="module-title">Customer Orders</div>
                    <div class="module-desc">
                        View real-time customer cake orders recorded in MariaDB, update order baking/delivery statuses, and review custom requests.
                    </div>

                    <div class="module-stats">
                        <div class="stat-item">
                            <div class="label">Total Orders</div>
                            <div class="val"><?= $totalOrdersCount ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="label">Pending Confirmation</div>
                            <div class="val" style="color:#d97706;"><?= $pendingOrdersCount ?></div>
                        </div>
                    </div>
                </div>

                <a href="admin_orders.php" class="btn-module" style="background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);">
                    <span>View & Manage Orders</span>
                    <i data-lucide="arrow-right" style="width:16px; height:16px;"></i>
                </a>
            </div>

            <!-- Module 2: Products Catalog -->
            <div class="module-card">
                <div>
                    <div class="module-header">
                        <div class="module-icon icon-products">
                            <i data-lucide="cake" style="width:28px; height:28px;"></i>
                        </div>
                        <span class="module-badge">CMS Module</span>
                    </div>

                    <div class="module-title">Products Catalog</div>
                    <div class="module-desc">
                        Create, edit, or remove cakes and party accessories. Toggle featured cakes for the website homepage catalog grid.
                    </div>

                    <div class="module-stats">
                        <div class="stat-item">
                            <div class="label">Total Products</div>
                            <div class="val"><?= $totalProducts ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="label">Featured Cakes</div>
                            <div class="val"><?= $featuredCount ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="label">Cakes / Addons</div>
                            <div class="val"><?= $cakeCount ?> / <?= $addonCount ?></div>
                        </div>
                    </div>
                </div>

                <a href="admin_products.php" class="btn-module">
                    <span>Manage Products Catalog</span>
                    <i data-lucide="arrow-right" style="width:16px; height:16px;"></i>
                </a>
            </div>

            <!-- Module 2: Creations Gallery CMS -->
            <div class="module-card">
                <div>
                    <div class="module-header">
                        <div class="module-icon" style="background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0;">
                            <i data-lucide="camera" style="width:28px; height:28px;"></i>
                        </div>
                        <span class="module-badge">Creations CMS</span>
                    </div>

                    <div class="module-title">Creations Gallery</div>
                    <div class="module-desc">
                        Upload custom cake photos, past bakes showcase, and select featured images to display on the Custom Cake Builder page.
                    </div>

                    <div class="module-stats">
                        <div class="stat-item">
                            <div class="label">Total Images</div>
                            <div class="val"><?= $totalGalleryCount ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="label">Featured Images</div>
                            <div class="val"><?= $featuredGalleryCount ?></div>
                        </div>
                    </div>
                </div>

                <a href="admin_gallery.php" class="btn-module" style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);">
                    <span>Manage Creations Gallery</span>
                    <i data-lucide="arrow-right" style="width:16px; height:16px;"></i>
                </a>
            </div>

            <!-- Module 3: Assets & Store Settings -->
            <div class="module-card">
                <div>
                    <div class="module-header">
                        <div class="module-icon icon-assets">
                            <i data-lucide="sliders" style="width:28px; height:28px;"></i>
                        </div>
                        <span class="module-badge">Store Assets</span>
                    </div>

                    <div class="module-title">Assets & Settings</div>
                    <div class="module-desc">
                        Update website image assets (Hero cake, Custom cake teaser image), announcement text, contact details, and toggle live store order acceptance.
                    </div>

                    <div class="module-stats">
                        <div class="stat-item">
                            <div class="label">Store Status</div>
                            <div class="val" style="font-size:14px; font-weight:800; color: <?= $isAcceptingOrders ? '#059669' : '#d97706' ?>">
                                <?= $isAcceptingOrders ? 'Accepting Orders' : 'Orders Paused' ?>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="label">Phone Contacts</div>
                            <div class="val" style="font-size:14px; font-weight:700; overflow:hidden; text-overflow:ellipsis;">
                                <?= htmlspecialchars($settings['contact_phone_1'] ?? '+91 94774 89551') ?>
                            </div>
                        </div>
                    </div>
                </div>

                <a href="admin_assets.php" class="btn-module" style="background: linear-gradient(135deg, #db2777 0%, #be185d 100%);">
                    <span>Manage Assets & Settings</span>
                    <i data-lucide="arrow-right" style="width:16px; height:16px;"></i>
                </a>
            </div>

        </div>

        <!-- Recent Incoming Orders Table Widget -->
        <div style="margin-top: 40px; background: #ffffff; border-radius: var(--radius); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); padding: 28px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i data-lucide="clock" style="width:20px; height:20px; color:var(--primary);"></i>
                    <h2 style="font-size: 18px; font-weight: 800; color: var(--text-dark);">Recent Customer Orders</h2>
                </div>
                <a href="admin_orders.php" style="font-size: 13px; font-weight: 700; color: var(--primary); text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                    <span>View All Orders</span>
                    <i data-lucide="chevron-right" style="width:14px; height:14px;"></i>
                </a>
            </div>

            <?php if (empty($recentOrders)): ?>
                <div style="text-align: center; padding: 32px 16px; color: var(--text-muted);">
                    <i data-lucide="inbox" style="width:40px; height:40px; color:#cbd5e1; margin-bottom:8px;"></i>
                    <p style="font-weight:600; font-size:14px;">No recent customer orders recorded yet.</p>
                    <p style="font-size:12px;">Placing an order on the website will automatically record it here.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid var(--border-color); text-align: left;">
                                <th style="padding: 10px 14px; color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Order ID</th>
                                <th style="padding: 10px 14px; color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Customer</th>
                                <th style="padding: 10px 14px; color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Fulfillment</th>
                                <th style="padding: 10px 14px; color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Amount</th>
                                <th style="padding: 10px 14px; color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Status</th>
                                <th style="padding: 10px 14px; color: var(--text-muted); font-size: 11px; text-transform: uppercase;">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $ro): 
                                $st = strtoupper($ro['status'] ?? 'PENDING');
                                $statusStyle = 'background:#fef3c7; color:#b45309;';
                                if ($st === 'CONFIRMED') $statusStyle = 'background:#dbeafe; color:#1d4ed8;';
                                if ($st === 'BAKING') $statusStyle = 'background:#f3e8ff; color:#6b21a8;';
                                if ($st === 'DELIVERED') $statusStyle = 'background:#d1fae5; color:#065f46;';
                            ?>
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td style="padding: 12px 14px; font-family: monospace; font-weight: 800; color: var(--primary);">
                                        <?= htmlspecialchars($ro['order_id']) ?>
                                    </td>
                                    <td style="padding: 12px 14px; font-weight: 700;">
                                        <?= htmlspecialchars($ro['customer_name'] ?? 'Guest') ?>
                                        <div style="font-size: 11px; font-weight: 500; color: var(--text-muted);">
                                            <?= htmlspecialchars($ro['customer_phone'] ?? '') ?>
                                        </div>
                                    </td>
                                    <td style="padding: 12px 14px; text-transform: capitalize; font-weight: 600;">
                                        <?= htmlspecialchars($ro['fulfillment_type'] ?? 'Pickup') ?>
                                    </td>
                                    <td style="padding: 12px 14px; font-weight: 800;">
                                        ₹<?= number_format(floatval($ro['total_amount'] ?? 0), 2) ?>
                                    </td>
                                    <td style="padding: 12px 14px;">
                                        <span style="display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 800; <?= $statusStyle ?>">
                                            <?= htmlspecialchars($st) ?>
                                        </span>
                                    </td>
                                    <td style="padding: 12px 14px; font-size: 12px; color: var(--text-muted);">
                                        <?= date('d M Y, h:i A', strtotime($ro['created_at'] ?? 'now')) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
