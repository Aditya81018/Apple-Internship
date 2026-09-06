<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 1. Serve static assets directly with proper MIME types
if (preg_match('#^/assets/(.+)#', $uri, $matches)) {
    $filePath = __DIR__ . '/public/assets/' . basename($matches[1]);
    if (file_exists($filePath) && is_file($filePath)) {
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'js'    => 'application/javascript',
            'css'   => 'text/css',
            'woff2' => 'font/woff2',
            'woff'  => 'font/woff',
            'ttf'   => 'font/ttf',
            'svg'   => 'image/svg+xml',
            'png'   => 'image/png',
            'jpg'   => 'image/jpeg',
            'jpeg'  => 'image/jpeg',
            'webp'  => 'image/webp',
            'json'  => 'application/json'
        ];
        header("Content-Type: " . ($mimeTypes[$ext] ?? 'application/octet-stream'));
        readfile($filePath);
        exit;
    }
}

// 2. Serve uploads directly
if (preg_match('#^/uploads/(.+)#', $uri, $matches)) {
    $filePath = __DIR__ . '/uploads/' . basename($matches[1]);
    if (file_exists($filePath) && is_file($filePath)) {
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'txt'  => 'text/plain'
        ];
        header("Content-Type: " . ($mimeTypes[$ext] ?? 'application/octet-stream'));
        readfile($filePath);
        exit;
    }
}

// 3. Route all API requests through public/index.php
if (strpos($uri, '/api/') === 0) {
    require __DIR__ . '/public/index.php';
    exit;
}

// 4. Route Admin CMS URLs to PHP scripts
if ($uri === '/login' || $uri === '/admin/login') {
    require __DIR__ . '/admin_login.php';
    exit;
}
if ($uri === '/admin/products') {
    require __DIR__ . '/admin_products.php';
    exit;
}
if ($uri === '/admin/product_add') {
    require __DIR__ . '/admin_product_add.php';
    exit;
}
if ($uri === '/admin/product_edit') {
    require __DIR__ . '/admin_product_edit.php';
    exit;
}
if ($uri === '/admin/assets') {
    require __DIR__ . '/admin_assets.php';
    exit;
}
if ($uri === '/admin/gallery') {
    require __DIR__ . '/admin_gallery.php';
    exit;
}
if ($uri === '/admin/orders') {
    require __DIR__ . '/admin_orders.php';
    exit;
}
if ($uri === '/admin' || $uri === '/admin/') {
    require __DIR__ . '/admin_dashboard.php';
    exit;
}

// Serve direct PHP files if explicitly requested
$adminFilePath = __DIR__ . $uri;
if (file_exists($adminFilePath) && is_file($adminFilePath) && pathinfo($adminFilePath, PATHINFO_EXTENSION) === 'php') {
    require $adminFilePath;
    exit;
}

// 5. Serve React Single Page Application (SPA) compiled index.html for all frontend website routes
$spaIndexPath = __DIR__ . '/public/index.html';
if (file_exists($spaIndexPath)) {
    header("Content-Type: text/html; charset=UTF-8");
    readfile($spaIndexPath);
    exit;
}

// Fallback to public/index.php if index.html is missing
require __DIR__ . '/public/index.php';