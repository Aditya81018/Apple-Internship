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

-- ----------------------------------------------------------------------------
-- Table: settings
-- Key-value store for store configuration, assets, and active status
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key` VARCHAR(64) NOT NULL,
  `setting_value` TEXT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings if missing
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
('accepting_orders', '1'),
('topbar_text', '100% EGGLESS | GLUTEN-FREE | LACTOSE-FREE CREAMS'),
('hero_image_url', 'https://images.unsplash.com/photo-1588195538326-c5b1e9f80a1b?auto=format&fit=crop&w=800&q=80'),
('custom_cake_teaser_image_url', 'https://images.unsplash.com/photo-1535141192574-5d4897c13636?auto=format&fit=crop&w=600&q=80'),
('contact_phone_1', '+91 94774 89551'),
('contact_phone_2', '+91 94323 65368');

-- ----------------------------------------------------------------------------
-- Table: gallery
-- Stores showcase creation images with category and featured status
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `gallery` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) NOT NULL DEFAULT 'Celebration Cakes',
  `image` TEXT NOT NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `gallery` (`id`, `title`, `category`, `image`, `is_featured`) VALUES
(1, 'Inspirational Mango Glaze', 'Celebration Cakes', 'https://images.unsplash.com/photo-1565958011703-44f9829ba187?auto=format&fit=crop&w=800&q=80', 1),
(2, 'Berry Vanilla Cupcake Tower', 'Pastry & Cupcakes', 'https://images.unsplash.com/photo-1587314168485-3236d6710814?auto=format&fit=crop&w=800&q=80', 1),
(3, 'Dark Fudgy Brownie Stack', 'Pastry & Cupcakes', 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?auto=format&fit=crop&w=800&q=80', 1),
(4, 'Strawberry Delight Custom Cake', 'Celebration Cakes', 'https://images.unsplash.com/photo-1535141192574-5d4897c13636?auto=format&fit=crop&w=800&q=80', 1),
(5, 'Three-Tier Floral Wedding Grace', 'Wedding & Tier', 'https://images.unsplash.com/photo-1519869325930-281384150729?auto=format&fit=crop&w=800&q=80', 0),
(6, 'Pistachio Saffron Gold Cake', 'Celebration Cakes', 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=800&q=80', 0);

