<?php
require_once __DIR__ . '/config/auth.php';
requireAdminAuth();

$message = '';
$error = '';

// Fetch orders (DB with JSON fallback)
function fetchAllOrders() {
    global $pdo;
    $orders = [];

    if (isset($pdo) && $pdo) {
        try {
            $stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC");
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
            foreach ($orders as &$ord) {
                $itemStmt->execute([$ord['order_id']]);
                $ord['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($ord['raw_payload'])) {
                    $ord['payload'] = json_decode($ord['raw_payload'], true);
                }
            }
            return $orders;
        } catch (\Exception $e) {
            // Fall back
        }
    }

    $jsonFile = __DIR__ . '/data/orders.json';
    if (file_exists($jsonFile)) {
        return json_decode(file_get_contents($jsonFile), true) ?? [];
    }
    return [];
}

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $orderId = trim($_POST['order_id'] ?? '');
    $newStatus = trim($_POST['status'] ?? '');

    if (!empty($orderId) && !empty($newStatus)) {
        if (isset($pdo) && $pdo) {
            try {
                $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
                $stmt->execute([$newStatus, $orderId]);
            } catch (\Exception $e) {
                // Fall back
            }
        }

        $jsonFile = __DIR__ . '/data/orders.json';
        if (file_exists($jsonFile)) {
            $jsonOrders = json_decode(file_get_contents($jsonFile), true) ?? [];
            foreach ($jsonOrders as &$o) {
                if (($o['order_id'] ?? '') === $orderId) {
                    $o['status'] = $newStatus;
                    break;
                }
            }
            file_put_contents($jsonFile, json_encode($jsonOrders, JSON_PRETTY_PRINT));
        }

        header("Location: admin_orders.php?msg=updated");
        exit;
    }
}

$orders = fetchAllOrders();

// Filter status via GET param
$statusFilter = $_GET['filter'] ?? 'all';
$filteredOrders = array_filter($orders, function($o) use ($statusFilter) {
    if ($statusFilter === 'all') return true;
    return strtolower($o['status'] ?? '') === strtolower($statusFilter);
});

// Calculate statistics
$totalOrders = count($orders);
$pendingCount = 0;
$bakingCount = 0;
$deliveredCount = 0;
$totalRevenue = 0;

foreach ($orders as $o) {
    $st = strtoupper($o['status'] ?? '');
    if ($st === 'PENDING_CONFIRMATION' || $st === 'PENDING') $pendingCount++;
    if ($st === 'CONFIRMED' || $st === 'BAKING') $bakingCount++;
    if ($st === 'DELIVERED') $deliveredCount++;
    $totalRevenue += floatval($o['total_amount'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Orders Manager — Raj Confections CMS</title>
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

        /* Container */
        .container {
            max-width: 1100px;
            margin: 40px auto 0;
            padding: 0 24px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: #ffffff;
            border-radius: var(--radius);
            padding: 20px 24px;
            border: 1px solid var(--border-color);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-val {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-dark);
            line-height: 1.2;
        }

        .stat-lbl {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        /* Filter Tabs */
        .filter-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .filter-tab {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            color: var(--text-muted);
            text-decoration: none;
            background: #ffffff;
            border: 1px solid var(--border-color);
            transition: all 0.2s;
            white-space: nowrap;
        }

        .filter-tab:hover, .filter-tab.active {
            background: var(--primary);
            color: #ffffff;
            border-color: var(--primary);
        }

        /* Orders List Cards */
        .orders-stack {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .order-card {
            background: #ffffff;
            border-radius: var(--radius);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        .order-header {
            padding: 18px 24px;
            background: #f8fafc;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .order-id-tag {
            font-family: monospace;
            font-size: 15px;
            font-weight: 800;
            color: var(--primary);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .badge-pending { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .badge-confirmed { background: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .badge-baking { background: #f3e8ff; color: #6b21a8; border: 1px solid #e9d5ff; }
        .badge-delivered { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        .order-body {
            padding: 24px;
        }

        .customer-meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 20px;
        }

        .meta-field {
            font-size: 13px;
        }

        .meta-field label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .meta-field span {
            font-weight: 700;
            color: var(--text-dark);
        }

        /* Line Items Table */
        .items-title {
            font-size: 13px;
            font-weight: 800;
            color: var(--text-dark);
            text-transform: uppercase;
            margin-bottom: 12px;
            letter-spacing: 0.03em;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 16px;
        }

        .items-table th {
            text-align: left;
            padding: 8px 12px;
            background: #f1f5f9;
            color: var(--text-muted);
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
        }

        .items-table td {
            padding: 12px;
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
        }

        .custom-img-thumb {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .order-footer {
            padding: 16px 24px;
            background: #f8fafc;
            border-top: 1px solid var(--border-color);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .status-update-form {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        select.status-select {
            padding: 8px 14px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            font-size: 13px;
            font-weight: 700;
            outline: none;
            background: #ffffff;
            cursor: pointer;
        }

        .btn-update-status {
            padding: 8px 16px;
            border-radius: 8px;
            background: var(--primary);
            color: #ffffff;
            border: none;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-update-status:hover {
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

        .empty-state {
            text-align: center;
            padding: 48px 24px;
            color: var(--text-muted);
            background: #ffffff;
            border-radius: var(--radius);
            border: 1px solid var(--border-color);
        }
    </style>
</head>
<body>

    <!-- Header Navigation Bar -->
    <nav class="navbar">
        <div class="brand-section">
            <div class="brand-icon">
                <i data-lucide="shopping-cart" style="width:24px; height:24px;"></i>
            </div>
            <div>
                <div class="brand-title">Raj Confections</div>
                <div class="brand-subtitle">Customer Orders CMS</div>
            </div>
        </div>

        <div class="nav-links">
            <a href="admin_dashboard.php" class="nav-link">
                <i data-lucide="home" style="width:16px; height:16px;"></i>
                <span>Dashboard</span>
            </a>
            <a href="admin_orders.php" class="nav-link active">
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
                <span>Order status updated successfully!</span>
            </div>
        <?php endif; ?>

        <!-- Stats Overview Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe;">
                    <i data-lucide="shopping-bag" style="width:24px; height:24px;"></i>
                </div>
                <div>
                    <div class="stat-val"><?= $totalOrders ?></div>
                    <div class="stat-lbl">Total Orders</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background:#fffbeb; color:#d97706; border:1px solid #fde68a;">
                    <i data-lucide="clock" style="width:24px; height:24px;"></i>
                </div>
                <div>
                    <div class="stat-val"><?= $pendingCount ?></div>
                    <div class="stat-lbl">Pending Confirmation</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background:#f3e8ff; color:#7e22ce; border:1px solid #e9d5ff;">
                    <i data-lucide="flame" style="width:24px; height:24px;"></i>
                </div>
                <div>
                    <div class="stat-val"><?= $bakingCount ?></div>
                    <div class="stat-lbl">Confirmed / Baking</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background:#ecfdf5; color:#059669; border:1px solid #a7f3d0;">
                    <i data-lucide="indian-rupee" style="width:24px; height:24px;"></i>
                </div>
                <div>
                    <div class="stat-val">₹<?= number_format($totalRevenue, 2) ?></div>
                    <div class="stat-lbl">Total Order Value</div>
                </div>
            </div>
        </div>

        <!-- Status Filter Tabs -->
        <div class="filter-bar">
            <a href="admin_orders.php?filter=all" class="filter-tab <?= $statusFilter === 'all' ? 'active' : '' ?>">All Orders (<?= $totalOrders ?>)</a>
            <a href="admin_orders.php?filter=pending_confirmation" class="filter-tab <?= strtolower($statusFilter) === 'pending_confirmation' ? 'active' : '' ?>">Pending</a>
            <a href="admin_orders.php?filter=confirmed" class="filter-tab <?= strtolower($statusFilter) === 'confirmed' ? 'active' : '' ?>">Confirmed</a>
            <a href="admin_orders.php?filter=baking" class="filter-tab <?= strtolower($statusFilter) === 'baking' ? 'active' : '' ?>">Baking</a>
            <a href="admin_orders.php?filter=delivered" class="filter-tab <?= strtolower($statusFilter) === 'delivered' ? 'active' : '' ?>">Delivered</a>
            <a href="admin_orders.php?filter=cancelled" class="filter-tab <?= strtolower($statusFilter) === 'cancelled' ? 'active' : '' ?>">Cancelled</a>
        </div>

        <!-- Orders Stack -->
        <?php if (empty($filteredOrders)): ?>
            <div class="empty-state">
                <i data-lucide="inbox" style="width:48px; height:48px; color:#cbd5e1; margin-bottom:12px;"></i>
                <p style="font-weight:700;">No customer orders found in this view.</p>
                <p style="font-size:12px;">When customers place orders on the website, they will appear here live.</p>
            </div>
        <?php else: ?>
            <div class="orders-stack">
                <?php foreach ($filteredOrders as $ord): 
                    $st = strtoupper($ord['status'] ?? 'PENDING_CONFIRMATION');
                    $badgeClass = 'badge-pending';
                    if ($st === 'CONFIRMED') $badgeClass = 'badge-confirmed';
                    if ($st === 'BAKING') $badgeClass = 'badge-baking';
                    if ($st === 'DELIVERED') $badgeClass = 'badge-delivered';
                    if ($st === 'CANCELLED') $badgeClass = 'badge-cancelled';
                ?>
                    <div class="order-card">
                        <!-- Order Header -->
                        <div class="order-header">
                            <div>
                                <span class="order-id-tag"><?= htmlspecialchars($ord['order_id']) ?></span>
                                <span style="font-size:12px; color:var(--text-muted); margin-left:10px;">
                                    Placed on <?= date('d M Y, h:i A', strtotime($ord['created_at'] ?? 'now')) ?>
                                </span>
                            </div>

                            <div class="status-badge <?= $badgeClass ?>">
                                <i data-lucide="circle-dot" style="width:12px; height:12px;"></i>
                                <span><?= htmlspecialchars($st) ?></span>
                            </div>
                        </div>

                        <!-- Order Body -->
                        <div class="order-body">
                            <div class="customer-meta-grid">
                                <div class="meta-field">
                                    <label>Customer Name</label>
                                    <span><?= htmlspecialchars($ord['customer_name'] ?? 'Guest') ?></span>
                                </div>
                                <div class="meta-field">
                                    <label>Phone Number</label>
                                    <span>
                                        <a href="tel:<?= htmlspecialchars($ord['customer_phone']) ?>" style="color:var(--primary); text-decoration:none;">
                                            <?= htmlspecialchars($ord['customer_phone'] ?? 'N/A') ?>
                                        </a>
                                    </span>
                                </div>
                                <div class="meta-field">
                                    <label>Fulfillment Type</label>
                                    <span style="text-transform:capitalize;"><?= htmlspecialchars($ord['fulfillment_type'] ?? 'Pickup') ?></span>
                                </div>
                                <div class="meta-field">
                                    <label>Fulfillment Date & Time</label>
                                    <span>
                                        <?= htmlspecialchars($ord['fulfillment_date'] ?? '') ?> @ <?= htmlspecialchars($ord['fulfillment_time'] ?? '') ?>
                                    </span>
                                </div>
                                <?php if (!empty($ord['delivery_address'])): ?>
                                    <div class="meta-field" style="grid-column: span 2;">
                                        <label>Delivery Address</label>
                                        <span><?= htmlspecialchars($ord['delivery_address']) ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($ord['special_notes'])): ?>
                                    <div class="meta-field" style="grid-column: span 2;">
                                        <label>Special Instructions / Notes</label>
                                        <span style="color:#b45309; font-style:italic;"><?= htmlspecialchars($ord['special_notes']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Line Items -->
                            <div class="items-title">Ordered Items</div>
                            <?php 
                                $items = $ord['items'] ?? [];
                                if (empty($items) && !empty($ord['payload']['items'])) {
                                    $items = $ord['payload']['items'];
                                }
                            ?>
                            <?php if (!empty($items)): ?>
                                <table class="items-table">
                                    <thead>
                                        <tr>
                                            <th>Item Name</th>
                                            <th>Size / Weight</th>
                                            <th>Qty</th>
                                            <th>Unit Price</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($items as $it): 
                                            $name = $it['product_name'] ?? $it['name'] ?? 'Product';
                                            $size = $it['size'] ?? 'Standard';
                                            $qty = intval($it['quantity'] ?? 1);
                                            $price = floatval($it['unit_price'] ?? $it['price'] ?? 0);
                                            $subtotal = $qty * $price;
                                        ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($name) ?></strong>
                                                    <?php if (!empty($it['customDetails'])): ?>
                                                        <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">
                                                            <div>Flavor: <?= htmlspecialchars($it['customDetails']['flavor'] ?? '') ?></div>
                                                            <div>Notes: <?= htmlspecialchars($it['customDetails']['instructions'] ?? '') ?></div>
                                                            <?php if (!empty($it['customDetails']['uploadedImageUrl'])): ?>
                                                                <a href="<?= htmlspecialchars($it['customDetails']['uploadedImageUrl']) ?>" target="_blank" style="display:inline-block; margin-top:4px;">
                                                                    <img src="<?= htmlspecialchars($it['customDetails']['uploadedImageUrl']) ?>" alt="Reference Upload" class="custom-img-thumb">
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($size) ?></td>
                                                <td><?= $qty ?></td>
                                                <td>₹<?= number_format($price, 2) ?></td>
                                                <td><strong>₹<?= number_format($subtotal, 2) ?></strong></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>

                        <!-- Order Footer -->
                        <div class="order-footer">
                            <div>
                                <span style="font-size:13px; color:var(--text-muted); font-weight:600;">Total Amount: </span>
                                <strong style="font-size:18px; color:var(--text-dark);">₹<?= number_format($ord['total_amount'] ?? 0, 2) ?></strong>
                            </div>

                            <form method="POST" action="admin_orders.php" class="status-update-form">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="order_id" value="<?= htmlspecialchars($ord['order_id']) ?>">
                                
                                <select name="status" class="status-select">
                                    <option value="PENDING_CONFIRMATION" <?= $st === 'PENDING_CONFIRMATION' ? 'selected' : '' ?>>PENDING_CONFIRMATION</option>
                                    <option value="CONFIRMED" <?= $st === 'CONFIRMED' ? 'selected' : '' ?>>CONFIRMED</option>
                                    <option value="BAKING" <?= $st === 'BAKING' ? 'selected' : '' ?>>BAKING</option>
                                    <option value="OUT_FOR_DELIVERY" <?= $st === 'OUT_FOR_DELIVERY' ? 'selected' : '' ?>>OUT_FOR_DELIVERY</option>
                                    <option value="DELIVERED" <?= $st === 'DELIVERED' ? 'selected' : '' ?>>DELIVERED</option>
                                    <option value="CANCELLED" <?= $st === 'CANCELLED' ? 'selected' : '' ?>>CANCELLED</option>
                                </select>

                                <button type="submit" class="btn-update-status">
                                    Update Status
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
