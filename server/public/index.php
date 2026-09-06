<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$host = '127.0.0.1';
$db   = 'raj-confections-db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (\PDOException $e) {
    // Database connection fallback
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

header('Content-Type: application/json');

// 1. Health Check
if ($uri === '/api/health') {
    echo json_encode([
        "status" => "ok",
        "timestamp" => date('c'),
        "database" => isset($pdo) ? "connected" : "disconnected"
    ]);
    exit;
}

// 2. Fetch Products
if ($uri === '/api/products' && $method === 'GET') {
    if (isset($pdo)) {
        $stmt = $pdo->query("SELECT * FROM products ORDER BY id ASC");
        echo json_encode($stmt->fetchAll());
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

// 4. Custom Cake Image Upload
if ($uri === '/api/upload' && $method === 'POST') {
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['image']['tmp_name'];
        $origName = basename($_FILES['image']['name']);
        $ext = pathinfo($origName, PATHINFO_EXTENSION);
        $newName = 'upload_' . time() . '_' . uniqid() . '.' . ($ext ?: 'jpg');
        $targetFile = $uploadDir . $newName;

        if (move_uploaded_file($tmpName, $targetFile)) {
            http_response_code(201);
            echo json_encode([
                "success" => true,
                "message" => "File uploaded successfully",
                "filename" => $newName,
                "url" => "/uploads/" . $newName
            ]);
            exit;
        }
    }

    http_response_code(400);
    echo json_encode(["error" => "No file uploaded or upload failed"]);
    exit;
}

// Default 404
http_response_code(404);
echo json_encode(["error" => "API endpoint not found"]);