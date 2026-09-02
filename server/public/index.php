<?php
/**
 * Raj Confections - PHP Backend Server API Controller
 * 
 * Provides RESTful API endpoints for catalog management, order processing,
 * custom cake reference image upload, and health monitoring.
 */

// Enable error reporting for dev clarity (can be muted in production)
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Allow Cross-Origin Resource Sharing (CORS) for Vite dev server & external clients
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

// Ensure required directories exist
if (!file_exists($dataDir)) {
    mkdir($dataDir, 0755, true);
}
if (!file_exists($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

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
        'version' => '1.0.0',
        'timestamp' => date('Y-m-d H:i:s T'),
        'server' => 'PHP/' . phpversion()
    ]);
}

// 3. GET /api/products
if ($requestUri === '/api/products' && $requestMethod === 'GET') {
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

    $ordersFile = $dataDir . '/orders.json';
    $existingOrders = [];
    if (file_exists($ordersFile)) {
        $existingOrders = json_decode(file_get_contents($ordersFile), true) ?: [];
    }

    $newOrder = [
        'order_id' => $orderId,
        'created_at' => date('c'),
        'items' => $orderData['items'] ?? [],
        'total_amount' => $orderData['total_amount'] ?? 0,
        'customer' => $orderData['customer'] ?? [],
        'fulfillment' => $orderData['fulfillment'] ?? [],
        'customization' => $orderData['customization'] ?? [],
        'status' => 'PENDING_CONFIRMATION'
    ];

    $existingOrders[] = $newOrder;

    file_put_contents($ordersFile, json_encode($existingOrders, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    sendJsonResponse([
        'success' => true,
        'message' => 'Order created successfully',
        'order_id' => $orderId,
        'order' => $newOrder
    ], 201);
}

// 5. POST /api/upload (Custom cake reference image upload)
if ($requestUri === '/api/upload' && $requestMethod === 'POST') {
    // Check if multipart form data file upload
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

    // Fallback: Check if base64 image string is passed in JSON payload
    $rawInput = file_get_contents('php://input');
    $jsonData = json_decode($rawInput, true);

    if ($jsonData && !empty($jsonData['image'])) {
        $base64Image = $jsonData['image'];
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
            $data = substr($base64Image, strpos($base64Image, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, etc.
            
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
