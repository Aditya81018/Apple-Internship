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

// 2. Fetch Products
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

require_once __DIR__ . '/../config/storage.php';

// 4. Image Upload Endpoint (Supabase / Local Storage)
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