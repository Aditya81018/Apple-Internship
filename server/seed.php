<?php
/**
 * Database Seeding CLI Script
 * Imports initial product catalog from data/products.json into MariaDB/MySQL.
 * Usage: php server/seed.php
 */

require_once __DIR__ . '/config/database.php';

$pdo = Database::getConnection();

if (!$pdo) {
    echo "❌ Error: Could not connect to MariaDB/MySQL database.\n";
    echo "Check DB_HOST, DB_USER, DB_PASS environment variables or ensure MariaDB service is running.\n";
    exit(1);
}

$productsFile = __DIR__ . '/data/products.json';
if (!file_exists($productsFile)) {
    echo "❌ Error: products.json file not found at {$productsFile}\n";
    exit(1);
}

$products = json_decode(file_get_contents($productsFile), true);
if (!is_array($products)) {
    echo "❌ Error: Failed to parse products.json\n";
    exit(1);
}

echo "🌱 Starting database seed for 'raj-confections-db'...\n";

$stmt = $pdo->prepare("
    INSERT INTO `products` (`id`, `name`, `category`, `price`, `image`, `sizes`, `prices`)
    VALUES (:id, :name, :category, :price, :image, :sizes, :prices)
    ON DUPLICATE KEY UPDATE
        `name` = VALUES(`name`),
        `category` = VALUES(`category`),
        `price` = VALUES(`price`),
        `image` = VALUES(`image`),
        `sizes` = VALUES(`sizes`),
        `prices` = VALUES(`prices`)
");

$count = 0;
foreach ($products as $item) {
    $stmt->execute([
        ':id'       => $item['id'],
        ':name'     => $item['name'],
        ':category' => $item['category'] ?? 'cake',
        ':price'    => $item['price'] ?? 0,
        ':image'    => $item['image'] ?? '',
        ':sizes'    => json_encode($item['sizes'] ?? []),
        ':prices'   => json_encode($item['prices'] ?? new stdClass())
    ]);
    $count++;
    echo "  [+] Processed product: {$item['name']} ({$item['id']})\n";
}

echo "✅ Seeding completed successfully! Total products imported/updated: {$count}\n";
