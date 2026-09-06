<?php
// server/config/auth.php

require_once __DIR__ . '/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Get fixed server admin credentials.
 */
function getAdminCredentials(): array {
    $id = getenv('ADMIN_ID') ?: ($_ENV['ADMIN_ID'] ?? 'admin');
    $pass = getenv('ADMIN_PASSWORD') ?: ($_ENV['ADMIN_PASSWORD'] ?? 'password123');
    return [
        'id' => $id,
        'password' => $pass
    ];
}

/**
 * Verify given ID and password against fixed server credentials.
 */
function verifyAdminCredentials(string $id, string $pass): bool {
    $creds = getAdminCredentials();
    return hash_equals((string)$creds['id'], trim($id)) && hash_equals((string)$creds['password'], trim($pass));
}

/**
 * Check if current session or Bearer token is authenticated.
 */
function isAdminLoggedIn(): bool {
    // 1. Check PHP Session
    if (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        return true;
    }

    // 2. Check HTTP Authorization Bearer Token
    $headers = array_change_key_case(getallheaders() ?: [], CASE_LOWER);
    $authHeader = $headers['authorization'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '');
    if (!empty($authHeader) && preg_match('/Bearer\s+(.+)$/i', $authHeader, $matches)) {
        $token = trim($matches[1]);
        $expectedToken = md5(getAdminCredentials()['id'] . ':' . getAdminCredentials()['password']);
        if (hash_equals($expectedToken, $token)) {
            return true;
        }
    }

    return false;
}

/**
 * Require authentication for web pages or API endpoints.
 */
function requireAdminAuth(bool $isApi = false): void {
    if (isAdminLoggedIn()) {
        return;
    }

    if ($isApi) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "error" => "Unauthorized access. Valid ID and Password required."
        ]);
        exit;
    } else {
        $currentUri = urlencode($_SERVER['REQUEST_URI'] ?? '/admin_products.php');
        header("Location: /admin_login.php?redirect=" . $currentUri);
        exit;
    }
}

/**
 * Perform login session set
 */
function loginAdminSession(string $id): string {
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_id'] = $id;
    $_SESSION['login_time'] = time();
    
    // Return persistent token for API clients
    return md5(getAdminCredentials()['id'] . ':' . getAdminCredentials()['password']);
}

/**
 * Perform logout
 */
function logoutAdminSession(): void {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
