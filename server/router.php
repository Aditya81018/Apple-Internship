<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 1. Serve uploads directly via PHP for absolute reliability
if (preg_match('#^/uploads/(.+)#', $uri, $matches)) {
    $filePath = __DIR__ . '/uploads/' . basename($matches[1]);
    if (file_exists($filePath) && is_file($filePath)) {
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'txt' => 'text/plain'
        ];
        header("Content-Type: " . ($mimeTypes[$ext] ?? 'application/octet-stream'));
        readfile($filePath);
        exit;
    }
}

// 2. Route all API requests through public/index.php
if (strpos($uri, '/api/') === 0) {
    require __DIR__ . '/public/index.php';
    exit;
}

// 3. Serve Admin CMS PHP scripts directly
$adminFilePath = __DIR__ . $uri;
if (file_exists($adminFilePath) && is_file($adminFilePath) && pathinfo($adminFilePath, PATHINFO_EXTENSION) === 'php') {
    require $adminFilePath;
    exit;
}

// 4. Shortcut for /admin URL
if ($uri === '/admin' || $uri === '/admin/') {
    require __DIR__ . '/admin_products.php';
    exit;
}

// 5. Default fallback to public/index.php
require __DIR__ . '/public/index.php';