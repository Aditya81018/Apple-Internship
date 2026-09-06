<?php
/**
 * Database Seeding CLI Script
 * Imports initial product catalog from data/products.json into MariaDB/MySQL.
 * Usage: php server/seed.php
 */

require_once __DIR__ . '/config/database.php';

$pdo = Database::getConnection();

if (!$pdo) {
    echo "[ERROR] Could not connect to MariaDB/MySQL database.\n";
    echo "Check DB_HOST, DB_USER, DB_PASS environment variables or ensure MariaDB service is running.\n";
    exit(1);
}

$productsFile = __DIR__ . '/../website/src/data/products.json';
if (!file_exists($productsFile)) {
    $productsFile = __DIR__ . '/data/products.json';
}

if (!file_exists($productsFile)) {
    echo "[ERROR] products.json file not found at {$productsFile}\n";
    exit(1);
}

$products = json_decode(file_get_contents($productsFile), true);
if (!is_array($products)) {
    echo "[ERROR] Failed to parse products.json\n";
    exit(1);
}

echo "[INFO] Starting database seed for 'raj-confections-db'...\n";

// Ensure column is added if missing
try {
    $pdo->exec("ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `is_featured` TINYINT(1) NOT NULL DEFAULT 0");
} catch (\Exception $e) {
    // Ignore if unsupported by older MariaDB version, insert will use schema
}

$stmt = $pdo->prepare("
    INSERT INTO `products` (`id`, `name`, `category`, `price`, `image`, `sizes`, `prices`, `is_featured`)
    VALUES (:id, :name, :category, :price, :image, :sizes, :prices, :is_featured)
    ON DUPLICATE KEY UPDATE
        `name` = VALUES(`name`),
        `category` = VALUES(`category`),
        `price` = VALUES(`price`),
        `image` = VALUES(`image`),
        `sizes` = VALUES(`sizes`),
        `prices` = VALUES(`prices`),
        `is_featured` = VALUES(`is_featured`)
");

$count = 0;
foreach ($products as $item) {
    $stmt->execute([
        ':id'          => $item['id'],
        ':name'        => $item['name'],
        ':category'    => $item['category'] ?? 'cake',
        ':price'       => $item['price'] ?? 0,
        ':image'       => $item['image'] ?? '',
        ':sizes'       => json_encode($item['sizes'] ?? []),
        ':prices'      => json_encode($item['prices'] ?? new stdClass()),
        ':is_featured' => !empty($item['is_featured']) ? 1 : 0
    ]);
    $count++;
    echo "  [+] Processed product: {$item['name']} ({$item['id']})\n";
}

echo "[SUCCESS] Seeding completed successfully! Total products imported/updated: {$count}\n";
