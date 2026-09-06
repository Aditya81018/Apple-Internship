-- ============================================================================
-- Raj Confections Database Schema
-- Database: raj-confections-db (MariaDB / MySQL)
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `raj-confections-db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `raj-confections-db`;

-- ----------------------------------------------------------------------------
-- Table: products
-- Stores standard catalog cakes and birthday add-on products
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id` VARCHAR(64) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `category` VARCHAR(50) NOT NULL DEFAULT 'cake',
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `image` TEXT NULL,
  `sizes` JSON NULL,
  `prices` JSON NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `is_featured` TINYINT(1) NOT NULL DEFAULT 0;

-- ----------------------------------------------------------------------------
-- Table: orders
-- Stores customer checkout order records
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `order_id` VARCHAR(64) NOT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'PENDING_CONFIRMATION',
  `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `customer_name` VARCHAR(255) NULL,
  `customer_phone` VARCHAR(50) NULL,
  `fulfillment_type` VARCHAR(50) NULL,
  `fulfillment_date` DATE NULL,
  `fulfillment_time` TIME NULL,
  `delivery_address` TEXT NULL,
  `name_on_cake` VARCHAR(255) NULL,
  `special_notes` TEXT NULL,
  `raw_payload` JSON NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: order_items
-- Individual line items associated with an order
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `order_id` VARCHAR(64) NOT NULL,
  `product_id` VARCHAR(64) NULL,
  `product_name` VARCHAR(255) NOT NULL,
  `size` VARCHAR(50) NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_order_items_order_id` (`order_id`),
  CONSTRAINT `fk_order_items_orders`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
