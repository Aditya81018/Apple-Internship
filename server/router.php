<?php
/**
 * Router script for PHP Built-in Development Web Server
 * Usage: php -S localhost:8000 server/router.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve uploads directly if file exists
if (preg_match('#^/uploads/(.+)#', $uri, $matches)) {
    $filePath = __DIR__ . '/uploads/' . basename($matches[1]);
    if (file_exists($filePath) && is_file($filePath)) {
        return false; // Serve file directly
    }
}

// Forward all other requests to public/index.php controller
require_header_or_index:
require_once __DIR__ . '/public/index.php';
