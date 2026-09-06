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

// 3. Create Order & Line Items (Database-backed)
if ($uri === '/api/orders' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $orderId = "RC-" . date('Ymd') . "-" . strtoupper(substr(md5(uniqid()), 0, 4));
    $totalAmount = $input['total_amount'] ?? 0;
    
    $customerName = $input['customer']['name'] ?? 'Guest';
    $customerPhone = $input['customer']['phone'] ?? '';
    
    $fulfillmentType = $input['fulfillment']['type'] ?? 'delivery';
    $fulfillmentDate = $input['fulfillment']['date'] ?? date('Y-m-d');
    $fulfillmentTime = $input['fulfillment']['time'] ?? '';
    $address = $input['fulfillment']['address'] ?? '';
    
    $cakeName = $input['customization']['name_on_cake'] ?? '';
    $specialNotes = $input['customization']['special_notes'] ?? '';

    if (isset($pdo)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO orders (order_id, total_amount, customer_name, customer_phone, fulfillment_type, fulfillment_date, fulfillment_time, address, name_on_cake, special_notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$orderId, $totalAmount, $customerName, $customerPhone, $fulfillmentType, $fulfillmentDate, $fulfillmentTime, $address, $cakeName, $specialNotes]);
            $dbOrderId = $pdo->lastInsertId();

            if (!empty($input['items']) && is_array($input['items'])) {
                $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, size, quantity, price) VALUES (?, ?, ?, ?, ?, ?)");
                foreach ($input['items'] as $item) {
                    $itemStmt->execute([
                        $dbOrderId,
                        $item['id'] ?? 'unknown',
                        $item['name'] ?? 'Product',
                        $item['size'] ?? 'Standard',
                        $item['quantity'] ?? 1,
                        $item['price'] ?? 0
                    ]);
                }
            }

            $pdo->commit();

            http_response_code(201);
            echo json_encode([
                "success" => true,
                "message" => "Order created successfully",
                "order_id" => $orderId,
                "storage" => "mariadb",
                "order" => $input
            ]);
            exit;

        } catch (\Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            http_response_code(500);
            echo json_encode(["error" => "Database error: " . $e->getMessage()]);
            exit;
        }
    }

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Order received (Fallback mode - DB not connected)",
        "order_id" => $orderId,
        "order" => $input
    ]);
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