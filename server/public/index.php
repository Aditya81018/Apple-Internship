<?php
/**
 * Raj Confections - PHP Backend Server API Controller
 * 
 * RESTful API endpoints connected to MariaDB / MySQL (`raj-confections-db`)
 * with graceful fallback to local JSON database storage.
 */

// Enable error reporting for dev clarity
ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once __DIR__ . '/../config/database.php';

// Allow Cross-Origin Resource Sharing (CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle HTTP OPTIONS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Parse requested URL path
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Helper function to send JSON responses
function sendJsonResponse($data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit(0);
}

// Data directory paths
$dataDir = __DIR__ . '/../data';
$uploadsDir = __DIR__ . '/../uploads';

if (!file_exists($dataDir)) {
    mkdir($dataDir, 0755, true);
}
if (!file_exists($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

// Check database connection
$pdo = Database::getConnection();

// --------------------------------------------------------------------------
// Routing Logic
// --------------------------------------------------------------------------

// 1. Static asset serving for /uploads/{filename}
if (preg_match('#^/uploads/([^/]+)$#', $requestUri, $matches)) {
    $filename = basename($matches[1]);
    $filePath = $uploadsDir . '/' . $filename;

    if (file_exists($filePath) && is_file($filePath)) {
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
        header("Content-Type: " . $mimeType);
        header("Content-Length: " . filesize($filePath));
        readfile($filePath);
        exit(0);
    } else {
        sendJsonResponse(['error' => 'File not found'], 404);
    }
}

// 2. GET /api/health
if ($requestUri === '/api/health' && $requestMethod === 'GET') {
    sendJsonResponse([
        'status' => 'ok',
        'app' => 'Raj Confections API',
        'version' => '1.1.0',
        'database' => $pdo ? 'connected (MariaDB/MySQL)' : 'fallback (JSON)',
        'timestamp' => date('Y-m-d H:i:s T'),
        'server' => 'PHP/' . phpversion()
    ]);
}

// 3. GET /api/products
if ($requestUri === '/api/products' && $requestMethod === 'GET') {
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT id, name, category, price, image, sizes, prices FROM products ORDER BY category ASC, name ASC");
            $products = [];
            while ($row = $stmt->fetch()) {
                $row['price'] = (float) $row['price'];
                $row['sizes'] = json_decode($row['sizes'] ?: '[]', true);
                $row['prices'] = json_decode($row['prices'] ?: '{}', true);
                $products[] = $row;
            }
            sendJsonResponse($products);
        } catch (PDOException $e) {
            error_log("Database Query Error: " . $e->getMessage());
        }
    }

    // Fallback to products.json
    $productsFile = $dataDir . '/products.json';
    if (file_exists($productsFile)) {
        $productsData = json_decode(file_get_contents($productsFile), true);
        sendJsonResponse($productsData ?? []);
    } else {
        sendJsonResponse(['error' => 'Products database not found'], 404);
    }
}

// 4. POST /api/orders
if ($requestUri === '/api/orders' && $requestMethod === 'POST') {
    $rawInput = file_get_contents('php://input');
    $orderData = json_decode($rawInput, true);

    if (!$orderData || !is_array($orderData)) {
        sendJsonResponse(['error' => 'Invalid JSON payload received'], 400);
    }

    // Generate unique order reference ID: RC-YYYYMMDD-XXXX
    $datePrefix = date('Ymd');
    $randomHex = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
    $orderId = "RC-{$datePrefix}-{$randomHex}";

    $items = $orderData['items'] ?? [];
    $totalAmount = (float) ($orderData['total_amount'] ?? 0);
    $customer = $orderData['customer'] ?? [];
    $fulfillment = $orderData['fulfillment'] ?? [];
    $customization = $orderData['customization'] ?? [];

    $dbSaved = false;

    if ($pdo) {
        try {
            $pdo->beginTransaction();

            $stmtOrder = $pdo->prepare("
                INSERT INTO `orders` (
                    `order_id`, `status`, `total_amount`,
                    `customer_name`, `customer_phone`,
                    `fulfillment_type`, `fulfillment_date`, `fulfillment_time`, `delivery_address`,
                    `name_on_cake`, `special_notes`, `raw_payload`
                ) VALUES (
                    :order_id, 'PENDING_CONFIRMATION', :total_amount,
                    :customer_name, :customer_phone,
                    :fulfillment_type, :fulfillment_date, :fulfillment_time, :delivery_address,
                    :name_on_cake, :special_notes, :raw_payload
                )
            ");

            $stmtOrder->execute([
                ':order_id'         => $orderId,
                ':total_amount'     => $totalAmount,
                ':customer_name'    => $customer['name'] ?? null,
                ':customer_phone'   => $customer['phone'] ?? null,
                ':fulfillment_type' => $fulfillment['type'] ?? null,
                ':fulfillment_date' => $fulfillment['date'] ?? null,
                ':fulfillment_time' => $fulfillment['time'] ?? null,
                ':delivery_address' => $fulfillment['address'] ?? null,
                ':name_on_cake'     => $customization['name_on_cake'] ?? null,
                ':special_notes'    => $customization['special_notes'] ?? null,
                ':raw_payload'      => json_encode($orderData)
            ]);

            if (!empty($items) && is_array($items)) {
                $stmtItem = $pdo->prepare("
                    INSERT INTO `order_items` (
                        `order_id`, `product_id`, `product_name`, `size`, `quantity`, `unit_price`
                    ) VALUES (
                        :order_id, :product_id, :product_name, :size, :quantity, :unit_price
                    )
                ");

                foreach ($items as $item) {
                    $stmtItem->execute([
                        ':order_id'     => $orderId,
                        ':product_id'   => $item['id'] ?? null,
                        ':product_name' => $item['name'] ?? 'Custom Item',
                        ':size'         => $item['size'] ?? null,
                        ':quantity'     => (int) ($item['quantity'] ?? 1),
                        ':unit_price'   => (float) ($item['price'] ?? 0)
                    ]);
                }
            }

            $pdo->commit();
            $dbSaved = true;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Database Order Insert Failed: " . $e->getMessage());
        }
    }

    // Always update JSON orders store as backup
    $ordersFile = $dataDir . '/orders.json';
    $existingOrders = [];
    if (file_exists($ordersFile)) {
        $existingOrders = json_decode(file_get_contents($ordersFile), true) ?: [];
    }

    $newOrder = [
        'order_id' => $orderId,
        'created_at' => date('c'),
        'items' => $items,
        'total_amount' => $totalAmount,
        'customer' => $customer,
        'fulfillment' => $fulfillment,
        'customization' => $customization,
        'status' => 'PENDING_CONFIRMATION',
        'db_persisted' => $dbSaved
    ];

    $existingOrders[] = $newOrder;
    file_put_contents($ordersFile, json_encode($existingOrders, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    sendJsonResponse([
        'success' => true,
        'message' => 'Order created successfully',
        'order_id' => $orderId,
        'storage' => $dbSaved ? 'mariadb' : 'json_fallback',
        'order' => $newOrder
    ], 201);
}

// 5. POST /api/upload (Custom cake reference image upload)
if ($requestUri === '/api/upload' && $requestMethod === 'POST') {
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($extension, $allowedExtensions)) {
            sendJsonResponse(['error' => 'Invalid file format. Only JPG, PNG, WEBP, and GIF are allowed.'], 400);
        }

        $newFilename = 'cake_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.' . $extension;
        $destination = $uploadsDir . '/' . $newFilename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            sendJsonResponse([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'filename' => $newFilename,
                'url' => '/uploads/' . $newFilename
            ], 201);
        } else {
            sendJsonResponse(['error' => 'Failed to save uploaded file'], 500);
        }
    }

    $rawInput = file_get_contents('php://input');
    $jsonData = json_decode($rawInput, true);

    if ($jsonData && !empty($jsonData['image'])) {
        $base64Image = $jsonData['image'];
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
            $data = substr($base64Image, strpos($base64Image, ',') + 1);
            $type = strtolower($type[1]);
            
            $data = base64_decode($data);
            if ($data !== false) {
                $newFilename = 'cake_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.' . $type;
                $destination = $uploadsDir . '/' . $newFilename;
                file_put_contents($destination, $data);
                
                sendJsonResponse([
                    'success' => true,
                    'message' => 'Base64 image uploaded successfully',
                    'filename' => $newFilename,
                    'url' => '/uploads/' . $newFilename
                ], 201);
            }
        }
    }

    sendJsonResponse(['error' => 'No image file or valid base64 payload provided'], 400);
}

// 6. Default Fallback / 404
sendJsonResponse([
    'error' => 'Endpoint not found',
    'path' => $requestUri,
    'method' => $requestMethod
], 404);
