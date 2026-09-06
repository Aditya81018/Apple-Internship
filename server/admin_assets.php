<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/storage.php';
requireAdminAuth();

$message = '';
$error = '';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acceptingOrders = isset($_POST['accepting_orders']) ? '1' : '0';
    $topbarText = trim($_POST['topbar_text'] ?? '');
    $heroUrl = trim($_POST['hero_image_url'] ?? '');
    $customCakeUrl = trim($_POST['custom_cake_teaser_image_url'] ?? '');
    $phone1 = trim($_POST['contact_phone_1'] ?? '');
    $phone2 = trim($_POST['contact_phone_2'] ?? '');

    // Handle Hero Image File Upload
    if (isset($_FILES['hero_image_file']) && $_FILES['hero_image_file']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadFileToStorage($_FILES['hero_image_file']);
        if ($uploadResult['success']) {
            $heroUrl = $uploadResult['url'];
        } else {
            $error .= " Hero image upload failed: " . ($uploadResult['error'] ?? 'Unknown error');
        }
    }

    // Handle Custom Cake Image File Upload
    if (isset($_FILES['custom_cake_image_file']) && $_FILES['custom_cake_image_file']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadFileToStorage($_FILES['custom_cake_image_file']);
        if ($uploadResult['success']) {
            $customCakeUrl = $uploadResult['url'];
        } else {
            $error .= " Custom cake image upload failed: " . ($uploadResult['error'] ?? 'Unknown error');
        }
    }

    if (empty($error)) {
        $updatedSettings = [
            'accepting_orders' => $acceptingOrders,
            'topbar_text' => $topbarText,
            'hero_image_url' => $heroUrl,
            'custom_cake_teaser_image_url' => $customCakeUrl,
            'contact_phone_1' => $phone1,
            'contact_phone_2' => $phone2
        ];

        // Save to JSON
        file_put_contents($jsonFile, json_encode(array_merge($settings, $updatedSettings), JSON_PRETTY_PRINT));

        // Save to DB
        if (isset($pdo) && $pdo) {
            try {
                $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                foreach ($updatedSettings as $k => $v) {
                    $stmt->execute([$k, $v]);
                }
            } catch (\Exception $e) {
                // DB fallback handled
            }
        }

        header("Location: admin_assets.php?msg=updated");
        exit;
    }
}

$isAcceptingOrders = ($settings['accepting_orders'] ?? '1') === '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assets & Store Settings — Raj Confections CMS</title>
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
            max-width: 840px;
            margin: 40px auto 0;
            padding: 0 24px;
        }

        .card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 36px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-md);
            margin-bottom: 28px;
        }

        .card-header {
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 16px;
        }

        .card-header h2 {
            font-size: 18px;
            font-weight: 800;
            color: var(--text-dark);
        }

        .form-group {
            margin-bottom: 24px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 8px;
        }

        input[type="text"], input[type="file"], textarea {
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

        input[type="text"]:focus, textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .note {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 6px;
        }

        .asset-preview-row {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-top: 12px;
            background: #f8fafc;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .asset-img-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: #ffffff;
        }

        .status-toggle-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            border-radius: 12px;
            border: 2px solid var(--border-color);
            background: #f8fafc;
        }

        .status-toggle-box.active {
            border-color: #a7f3d0;
            background: #ecfdf5;
        }

        .status-toggle-box.paused {
            border-color: #fde68a;
            background: #fffbeb;
        }

        .status-toggle-label {
            display: flex;
            flex-direction: column;
        }

        .status-title {
            font-size: 15px;
            font-weight: 800;
        }

        .active .status-title { color: #065f46; }
        .paused .status-title { color: #92400e; }

        .status-subtitle {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* Toggle switch CSS */
        .switch {
            position: relative;
            display: inline-block;
            width: 52px;
            height: 28px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #cbd5e1;
            transition: .3s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: var(--success);
        }

        input:checked + .slider:before {
            transform: translateX(24px);
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
    </style>
</head>
<body>

    <!-- Header Navigation Bar -->
    <nav class="navbar">
        <div class="brand-section">
            <div class="brand-icon">
                <i data-lucide="sliders" style="width:24px; height:24px;"></i>
            </div>
            <div>
                <div class="brand-title">Raj Confections</div>
                <div class="brand-subtitle">Assets & Settings CMS</div>
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
            <a href="admin_gallery.php" class="nav-link">
                <i data-lucide="camera" style="width:16px; height:16px;"></i>
                <span>Creations Gallery</span>
            </a>
            <a href="admin_assets.php" class="nav-link active">
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

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
            <div class="toast-success">
                <i data-lucide="check-circle-2" style="width:18px; height:18px;"></i>
                <span>Store assets and settings updated successfully!</span>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert-error">
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="admin_assets.php" enctype="multipart/form-data">

            <!-- Card 1: Live Order Acceptance Indicator -->
            <div class="card">
                <div class="card-header">
                    <i data-lucide="radio" style="width:20px; height:20px; color:var(--primary);"></i>
                    <h2>Order Acceptance Status Indicator</h2>
                </div>

                <div class="status-toggle-box <?= $isAcceptingOrders ? 'active' : 'paused' ?>">
                    <div class="status-toggle-label">
                        <div class="status-title">
                            <?= $isAcceptingOrders ? 'Store is ONLINE & Accepting Orders' : 'Store is PAUSED (Not accepting new orders)' ?>
                        </div>
                        <div class="status-subtitle">
                            Controls the live status indicator badge on website topbar and checkout notice.
                        </div>
                    </div>

                    <label class="switch">
                        <input type="checkbox" name="accepting_orders" value="1" <?= $isAcceptingOrders ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            <!-- Card 2: TopBar Announcement Banner Text -->
            <div class="card">
                <div class="card-header">
                    <i data-lucide="megaphone" style="width:20px; height:20px; color:var(--primary);"></i>
                    <h2>TopBar Announcement Banner</h2>
                </div>

                <div class="form-group">
                    <label for="topbar_text">TopBar Text Content</label>
                    <input type="text" id="topbar_text" name="topbar_text" value="<?= htmlspecialchars($settings['topbar_text'] ?? '100% EGGLESS | GLUTEN-FREE | LACTOSE-FREE CREAMS') ?>" placeholder="e.g. 100% EGGLESS | FRESH BAKED DAILY">
                    <div class="note">Displays across the top sticky banner of the website.</div>
                </div>
            </div>

            <!-- Card 3: Website Image Assets -->
            <div class="card">
                <div class="card-header">
                    <i data-lucide="image" style="width:20px; height:20px; color:var(--primary);"></i>
                    <h2>Homepage Image Assets</h2>
                </div>

                <!-- Asset A: Hero Cake Image -->
                <div class="form-group">
                    <label for="hero_image_file">1. Hero Cake Main Image Asset</label>
                    <input type="file" id="hero_image_file" name="hero_image_file" accept="image/*">
                    <div style="margin-top:8px;">
                        <input type="text" id="hero_image_url" name="hero_image_url" value="<?= htmlspecialchars($settings['hero_image_url'] ?? '') ?>" placeholder="https://... or /uploads/...">
                    </div>
                    <?php if (!empty($settings['hero_image_url'])): ?>
                        <div class="asset-preview-row">
                            <img src="<?= htmlspecialchars($settings['hero_image_url']) ?>" alt="Hero Preview" class="asset-img-preview" onerror="this.src='https://placehold.co/100x100?text=Hero'">
                            <div>
                                <strong style="font-size:12px;">Active Hero Image Asset</strong>
                                <div class="note" style="word-break:break-all; font-family:monospace;"><?= htmlspecialchars($settings['hero_image_url']) ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Asset B: Custom Cake Teaser Image -->
                <div class="form-group" style="margin-top:28px;">
                    <label for="custom_cake_image_file">2. Custom Cake Builder Teaser Image Asset</label>
                    <input type="file" id="custom_cake_image_file" name="custom_cake_image_file" accept="image/*">
                    <div style="margin-top:8px;">
                        <input type="text" id="custom_cake_teaser_image_url" name="custom_cake_teaser_image_url" value="<?= htmlspecialchars($settings['custom_cake_teaser_image_url'] ?? '') ?>" placeholder="https://... or /uploads/...">
                    </div>
                    <?php if (!empty($settings['custom_cake_teaser_image_url'])): ?>
                        <div class="asset-preview-row">
                            <img src="<?= htmlspecialchars($settings['custom_cake_teaser_image_url']) ?>" alt="Custom Teaser Preview" class="asset-img-preview" onerror="this.src='https://placehold.co/100x100?text=Custom'">
                            <div>
                                <strong style="font-size:12px;">Active Custom Cake Teaser Asset</strong>
                                <div class="note" style="word-break:break-all; font-family:monospace;"><?= htmlspecialchars($settings['custom_cake_teaser_image_url']) ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Card 4: Store Contact Numbers -->
            <div class="card">
                <div class="card-header">
                    <i data-lucide="phone" style="width:20px; height:20px; color:var(--primary);"></i>
                    <h2>Store Contact Information</h2>
                </div>

                <div class="form-group">
                    <label for="contact_phone_1">Primary Phone Number</label>
                    <input type="text" id="contact_phone_1" name="contact_phone_1" value="<?= htmlspecialchars($settings['contact_phone_1'] ?? '+91 94774 89551') ?>">
                </div>

                <div class="form-group">
                    <label for="contact_phone_2">Secondary Phone Number</label>
                    <input type="text" id="contact_phone_2" name="contact_phone_2" value="<?= htmlspecialchars($settings['contact_phone_2'] ?? '+91 94323 65368') ?>">
                </div>
            </div>

            <!-- Submit Button -->
            <div style="text-align: right;">
                <button type="submit" class="btn-submit">
                    <i data-lucide="save" style="width:16px; height:16px;"></i>
                    <span>Save Assets & Settings</span>
                </button>
            </div>

        </form>

    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
