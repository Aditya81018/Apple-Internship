<?php
/**
 * MariaDB / MySQL Database Connection Manager (PDO)
 * Raj Confections - PHP Server
 */

class Database {
    private static ?PDO $instance = null;
    private static bool $connectionAttempted = false;

    /**
     * Get or establish PDO database connection
     *
     * @return PDO|null Returns PDO object on success, or null if connection fails
     */
    public static function getConnection(): ?PDO {
        if (self::$instance !== null) {
            return self::$instance;
        }

        if (self::$connectionAttempted) {
            return null; // Avoid repeated failing connection overhead
        }

        self::$connectionAttempted = true;

        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: '3306';
        $dbname = getenv('DB_NAME') ?: 'raj-confections-db';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 3, // 3-second timeout for quick fallback
        ];

        try {
            self::$instance = new PDO($dsn, $user, $pass, $options);
            return self::$instance;
        } catch (PDOException $e) {
            // Log connection failure silently for fallback handler
            error_log("Database Connection Warning: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if database connection is active
     */
    public static function isConnected(): bool {
        return self::getConnection() !== null;
    }
}
