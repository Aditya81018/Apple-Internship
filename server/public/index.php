<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/auth.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

header('Content-Type: application/json');

// 1. Fixed ID & Password Authentication Endpoints
if (($uri === '/api/login' || $uri === '/api/auth/login') && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $id = trim($input['id'] ?? $input['username'] ?? '');
    $password = trim($input['password'] ?? '');

    if (empty($id) || empty($password)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => "ID/Username and Password are required."
        ]);
        exit;
    }

    if (verifyAdminCredentials($id, $password)) {
        $token = loginAdminSession($id);
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Authentication successful",
            "token" => $token,
            "user" => [
                "id" => $id,
                "role" => "admin"
            ]
        ]);
        exit;
    } else {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "error" => "Invalid Server ID or Password."
        ]);
        exit;
    }
}

if ($uri === '/api/auth/me' && $method === 'GET') {
    if (isAdminLoggedIn()) {
        echo json_encode([
            "authenticated" => true,
            "user" => [
                "id" => $_SESSION['admin_id'] ?? getAdminCredentials()['id'],
                "role" => "admin"
            ]
        ]);
    } else {
        echo json_encode([
            "authenticated" => false
        ]);
    }
    exit;
}

if ($uri === '/api/auth/logout' && $method === 'POST') {
    logoutAdminSession();
    echo json_encode([
        "success" => true,
        "message" => "Logged out successfully"
    ]);
    exit;
}

// 2. Health Check
if ($uri === '/api/health') {
    echo json_encode([
        "status" => "ok",
        "timestamp" => date('c'),
        "database" => isset($pdo) ? "connected" : "disconnected",
        "auth_system" => "fixed_credentials"
    ]);
    exit;
}

// 2. Settings Endpoints
if ($uri === '/api/settings' && $method === 'GET') {
    $settings = [];
    $jsonFile = __DIR__ . '/../data/settings.json';
    if (file_exists($jsonFile)) {
        $settings = json_decode(file_get_contents($jsonFile), true) ?? [];
    }

    if (isset($pdo)) {
        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
            $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            if (!empty($rows)) {
                $settings = array_merge($settings, $rows);
            }
        } catch (\Exception $e) {
            // Fall back to JSON settings
        }
    }

    echo json_encode($settings);
    exit;
}

if ($uri === '/api/settings' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $jsonFile = __DIR__ . '/../data/settings.json';
    $existing = [];
    if (file_exists($jsonFile)) {
        $existing = json_decode(file_get_contents($jsonFile), true) ?? [];
    }

    foreach ($input as $key => $val) {
        $existing[$key] = (string)$val;
    }
    file_put_contents($jsonFile, json_encode($existing, JSON_PRETTY_PRINT));

    if (isset($pdo)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            foreach ($input as $key => $val) {
                $stmt->execute([$key, (string)$val]);
            }
        } catch (\Exception $e) {
            // DB fallback handled via JSON update
        }
    }

    echo json_encode(["success" => true, "settings" => $existing]);
    exit;
}

// 3. Fetch Products
if ($uri === '/api/products' && $method === 'GET') {
    if (isset($pdo)) {
        $stmt = $pdo->query("SELECT * FROM products ORDER BY id ASC");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($products as &$p) {
            $p['is_featured'] = (bool)($p['is_featured'] ?? 0);
        }
        echo json_encode($products);
    } else {
        echo json_encode([]);
    }
    exit;
}

// 3. Orders Endpoints (Database-backed with JSON fallback)
if ($uri === '/api/orders' && $method === 'GET') {
    $orderIdParam = $_GET['id'] ?? null;
    $orders = [];

    if (isset($pdo)) {
        try {
            if ($orderIdParam) {
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ?");
                $stmt->execute([$orderIdParam]);
                $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC");
                $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            // Fetch line items for each order
            $itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
            foreach ($orders as &$ord) {
                $itemStmt->execute([$ord['order_id']]);
                $ord['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($ord['raw_payload'])) {
                    $ord['decoded_payload'] = json_decode($ord['raw_payload'], true);
                }
            }
        } catch (\Exception $e) {
            $orders = [];
        }
    }

    if (empty($orders)) {
        $jsonFile = __DIR__ . '/../data/orders.json';
        if (file_exists($jsonFile)) {
            $jsonOrders = json_decode(file_get_contents($jsonFile), true) ?? [];
            if ($orderIdParam) {
                $orders = array_values(array_filter($jsonOrders, fn($o) => ($o['order_id'] ?? '') === $orderIdParam));
            } else {
                $orders = $jsonOrders;
            }
        }
    }

    echo json_encode($orders);
    exit;
}

if ($uri === '/api/orders' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $orderId = "RC-" . date('Ymd') . "-" . strtoupper(substr(md5(uniqid()), 0, 4));
    $totalAmount = floatval($input['total_amount'] ?? $input['totalAmount'] ?? 0);
    
    $customerName = trim($input['customer']['name'] ?? $input['name'] ?? 'Guest');
    $customerPhone = trim($input['customer']['phone'] ?? $input['phone'] ?? '');
    
    $fulfillmentType = trim($input['fulfillment']['type'] ?? $input['fulfillment'] ?? 'pickup');
    $fulfillmentDate = trim($input['fulfillment']['date'] ?? $input['date'] ?? date('Y-m-d'));
    if (empty($fulfillmentDate)) $fulfillmentDate = date('Y-m-d');
    $fulfillmentTime = trim($input['fulfillment']['time'] ?? $input['time'] ?? '12:00 PM');
    $address = trim($input['fulfillment']['address'] ?? $input['address'] ?? '');
    
    $cakeName = trim($input['customization']['name_on_cake'] ?? $input['name_on_cake'] ?? '');
    $specialNotes = trim($input['customization']['special_notes'] ?? $input['notes'] ?? $input['special_notes'] ?? '');
    $status = 'PENDING_CONFIRMATION';
    $createdAt = date('Y-m-d H:i:s');

    $dbInserted = false;

    if (isset($pdo)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO orders (order_id, status, total_amount, customer_name, customer_phone, fulfillment_type, fulfillment_date, fulfillment_time, delivery_address, name_on_cake, special_notes, raw_payload, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $orderId,
                $status,
                $totalAmount,
                $customerName,
                $customerPhone,
                $fulfillmentType,
                $fulfillmentDate,
                $fulfillmentTime,
                $address,
                $cakeName,
                $specialNotes,
                json_encode($input)
            ]);

            if (!empty($input['items']) && is_array($input['items'])) {
                $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, size, quantity, unit_price) VALUES (?, ?, ?, ?, ?, ?)");
                foreach ($input['items'] as $item) {
                    $itemStmt->execute([
                        $orderId,
                        $item['id'] ?? $item['productId'] ?? 'unknown',
                        $item['name'] ?? 'Product',
                        $item['size'] ?? 'Standard',
                        intval($item['quantity'] ?? 1),
                        floatval($item['price'] ?? 0)
                    ]);
                }
            }

            $pdo->commit();
            $dbInserted = true;

        } catch (\Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
        }
    }

    // Sync to JSON file for fallback
    $jsonFile = __DIR__ . '/../data/orders.json';
    $jsonOrders = [];
    if (file_exists($jsonFile)) {
        $jsonOrders = json_decode(file_get_contents($jsonFile), true) ?? [];
    }

    $newOrderRecord = [
        "id" => time(),
        "order_id" => $orderId,
        "status" => $status,
        "total_amount" => $totalAmount,
        "customer_name" => $customerName,
        "customer_phone" => $customerPhone,
        "fulfillment_type" => $fulfillmentType,
        "fulfillment_date" => $fulfillmentDate,
        "fulfillment_time" => $fulfillmentTime,
        "delivery_address" => $address,
        "name_on_cake" => $cakeName,
        "special_notes" => $specialNotes,
        "items" => $input['items'] ?? [],
        "created_at" => $createdAt
    ];

    array_unshift($jsonOrders, $newOrderRecord);
    file_put_contents($jsonFile, json_encode($jsonOrders, JSON_PRETTY_PRINT));

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Order recorded successfully",
        "order_id" => $orderId,
        "storage" => $dbInserted ? "mariadb" : "json_fallback",
        "order" => $newOrderRecord
    ]);
    exit;
}

if ($uri === '/api/orders/update_status' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $orderId = trim($input['order_id'] ?? $_GET['order_id'] ?? '');
    $newStatus = trim($input['status'] ?? '');

    if (empty($orderId) || empty($newStatus)) {
        http_response_code(400);
        echo json_encode(["error" => "order_id and status are required."]);
        exit;
    }

    if (isset($pdo)) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
            $stmt->execute([$newStatus, $orderId]);
        } catch (\Exception $e) {
            // Fall back
        }
    }

    $jsonFile = __DIR__ . '/../data/orders.json';
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

    echo json_encode(["success" => true, "order_id" => $orderId, "status" => $newStatus]);
    exit;
}

// 4. Gallery Endpoints
if ($uri === '/api/gallery' && $method === 'GET') {
    $featuredOnly = isset($_GET['featured']) && $_GET['featured'] == '1';
    $gallery = [];

    if (isset($pdo)) {
        try {
            $sql = "SELECT * FROM gallery";
            if ($featuredOnly) {
                $sql .= " WHERE is_featured = 1";
            }
            $sql .= " ORDER BY id DESC";
            $stmt = $pdo->query($sql);
            $gallery = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($gallery as &$item) {
                $item['id'] = (int)$item['id'];
                $item['is_featured'] = (int)($item['is_featured'] ?? 0);
            }
        } catch (\Exception $e) {
            $gallery = [];
        }
    }

    if (empty($gallery)) {
        $jsonFile = __DIR__ . '/../data/gallery.json';
        if (file_exists($jsonFile)) {
            $gallery = json_decode(file_get_contents($jsonFile), true) ?? [];
            if ($featuredOnly) {
                $gallery = array_values(array_filter($gallery, fn($item) => !empty($item['is_featured'])));
            }
        }
    }

    echo json_encode($gallery);
    exit;
}

if ($uri === '/api/gallery' && $method === 'POST') {
    require_once __DIR__ . '/../config/storage.php';

    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? 'Celebration Cakes');
    $isFeatured = isset($_POST['is_featured']) && ($_POST['is_featured'] === '1' || $_POST['is_featured'] === 'true' || $_POST['is_featured'] === true) ? 1 : 0;
    $imageUrl = trim($_POST['image'] ?? '');

    // Check for JSON body if POST body is raw json
    if (empty($title) && empty($_FILES)) {
        $rawInput = json_decode(file_get_contents('php://input'), true);
        if ($rawInput) {
            $title = trim($rawInput['title'] ?? '');
            $category = trim($rawInput['category'] ?? 'Celebration Cakes');
            $isFeatured = !empty($rawInput['is_featured']) ? 1 : 0;
            $imageUrl = trim($rawInput['image'] ?? '');
        }
    }

    // Handle uploaded file if present
    $file = $_FILES['image_file'] ?? $_FILES['image'] ?? null;
    if ($file && !empty($file['tmp_name'])) {
        $uploadRes = uploadFileToStorage($file);
        if ($uploadRes['success']) {
            $imageUrl = $uploadRes['url'];
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Image upload failed: " . ($uploadRes['error'] ?? 'Unknown error')]);
            exit;
        }
    }

    if (empty($title) || empty($imageUrl)) {
        http_response_code(400);
        echo json_encode(["error" => "Title and Image are required."]);
        exit;
    }

    $newId = time();
    $createdAt = date('Y-m-d H:i:s');

    if (isset($pdo)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO gallery (title, category, image, is_featured, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$title, $category, $imageUrl, $isFeatured]);
            $newId = (int)$pdo->lastInsertId();
        } catch (\Exception $e) {
            // DB fallback handled via JSON
        }
    }

    // Sync to JSON file
    $jsonFile = __DIR__ . '/../data/gallery.json';
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

    http_response_code(201);
    echo json_encode(["success" => true, "item" => $newItem]);
    exit;
}

if ($uri === '/api/gallery/toggle_featured' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $id = (int)($input['id'] ?? $_GET['id'] ?? 0);

    if (!$id) {
        http_response_code(400);
        echo json_encode(["error" => "Gallery item ID is required."]);
        exit;
    }

    $newStatus = 0;
    if (isset($pdo)) {
        try {
            $stmt = $pdo->prepare("UPDATE gallery SET is_featured = 1 - is_featured WHERE id = ?");
            $stmt->execute([$id]);
            
            $getStmt = $pdo->prepare("SELECT is_featured FROM gallery WHERE id = ?");
            $getStmt->execute([$id]);
            $newStatus = (int)$getStmt->fetchColumn();
        } catch (\Exception $e) {
            // Fall back to JSON
        }
    }

    // Also update JSON file
    $jsonFile = __DIR__ . '/../data/gallery.json';
    if (file_exists($jsonFile)) {
        $jsonItems = json_decode(file_get_contents($jsonFile), true) ?? [];
        foreach ($jsonItems as &$item) {
            if ($item['id'] == $id) {
                $item['is_featured'] = 1 - ($item['is_featured'] ?? 0);
                $newStatus = $item['is_featured'];
                break;
            }
        }
        file_put_contents($jsonFile, json_encode($jsonItems, JSON_PRETTY_PRINT));
    }

    echo json_encode(["success" => true, "id" => $id, "is_featured" => $newStatus]);
    exit;
}

if ($uri === '/api/gallery' && $method === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $id = (int)($input['id'] ?? $_GET['id'] ?? 0);

    if (!$id) {
        http_response_code(400);
        echo json_encode(["error" => "Gallery item ID is required."]);
        exit;
    }

    if (isset($pdo)) {
        try {
            $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
            $stmt->execute([$id]);
        } catch (\Exception $e) {
            // Fall back to JSON
        }
    }

    $jsonFile = __DIR__ . '/../data/gallery.json';
    if (file_exists($jsonFile)) {
        $jsonItems = json_decode(file_get_contents($jsonFile), true) ?? [];
        $jsonItems = array_values(array_filter($jsonItems, fn($item) => $item['id'] != $id));
        file_put_contents($jsonFile, json_encode($jsonItems, JSON_PRETTY_PRINT));
    }

    echo json_encode(["success" => true, "message" => "Gallery item deleted", "id" => $id]);
    exit;
}

// 5. Image Upload Endpoint (Supabase / Local Storage)
if ($uri === '/api/upload' && $method === 'POST') {
    $file = $_FILES['image'] ?? $_FILES['image_file'] ?? $_FILES['file'] ?? null;
    if ($file) {
        $result = uploadFileToStorage($file);
        if ($result['success']) {
            http_response_code(201);
            echo json_encode($result);
            exit;
        } else {
            http_response_code(400);
            echo json_encode(["error" => $result['error'] ?? "Upload failed"]);
            exit;
        }
    }

    http_response_code(400);
    echo json_encode(["error" => "No file attached to upload request."]);
    exit;
}

// Default 404
http_response_code(404);
echo json_encode(["error" => "API endpoint not found"]);