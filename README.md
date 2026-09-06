# Raj Confections — Artisan Eggless Bakery Web Platform & Admin CMS

Raj Confections is an e-commerce catalog, custom cake builder, creations showcase, and order management platform for an artisan eggless bakery. It features a React 19 Single Page Application (SPA) frontend served directly via a PHP web server backed by a MariaDB / MySQL database (`raj-confections-db`) with automatic JSON file fallback storage.

---

## Architecture Overview

```
.
├── server/             # PHP Backend Server, Admin CMS Suite & API Dispatcher
│   ├── config/         # Database PDO connection manager & environment loader
│   ├── data/           # Offline JSON fallback storage (products.json, settings.json, gallery.json, orders.json)
│   ├── public/         # Serves compiled React SPA index.html, JS/CSS assets, and REST API (index.php)
│   ├── uploads/        # Custom cake reference images upload storage
│   ├── schema.sql      # MariaDB / MySQL database schema definition script
│   ├── seed.php        # Database seeding CLI script
│   ├── admin_dashboard.php  # Admin Control Center homepage (/admin)
│   ├── admin_orders.php     # Admin Customer Orders Manager (/admin/orders)
│   ├── admin_products.php   # Admin Products Catalog Manager (/admin/products)
│   ├── admin_gallery.php    # Admin Creations Gallery Manager (/admin/gallery)
│   ├── admin_assets.php     # Admin Store Assets & Status Settings (/admin/assets)
│   ├── admin_login.php      # Fixed ID & Password Admin Authentication (/login)
│   └── router.php      # Master PHP router script serving React SPA, static assets, APIs & Admin CMS
├── website/            # React 19 + TypeScript + Vite + Tailwind CSS Frontend Application
│   ├── src/
│   │   ├── components/ # Navigation, Layout, CatalogGrid, Cart Drawer, UI Primitives
│   │   ├── pages/      # Home, Catalog, Gallery, CustomCake, Checkout
│   │   ├── store/      # Zustand Cart & Drawer State Management
│   │   └── utils/      # WhatsApp URL builder & helpers
│   └── package.json    # Frontend package configuration
├── GEMINI.md           # Workspace instructions & coding standards
└── README.md           # Application documentation
```

---

## Features Showcase

### 1. Customer-Facing Web Application (React SPA)
- **Homepage (`/`)**:
  - Live TopBar Announcement Banner displaying dietary badges (100% Eggless, Gluten-Free options) and an active **Accepting Orders** / **Orders Paused** indicator pill.
  - Hero Showcase section with customizable hero cake image.
  - Featured Specialty Cakes grid displaying handpicked signature cakes.
  - Value Proposition highlights (Strictly eggless, baked fresh daily).
  - Bespoke Custom Cake Teaser card with direct link to Gallery and Custom Cake Builder.
  - Easy 3-step ordering guide and verified customer reviews.
  - Auto-hiding sticky Navbar header on scroll down / reveal on scroll up.

- **Menu Catalog (`/catalog`)**:
  - Full product showcase for cakes and birthday add-on accessories.
  - Category filter tabs (*All Cakes*, *Celebration Cakes*, *Fruit & Special*, *Birthday Add-ons*).
  - Interactive search bar and real-time price range slider.
  - Weight selection options (1 lb, 2 lb, 3 lb, 5 lb) with dynamic price calculation.
  - One-click "Add to Cart" integration.

- **Creations Gallery (`/gallery`)**:
  - Public showcase of past bakes and custom creations.
  - Category filter tabs (*All Creations*, *Celebration Cakes*, *Wedding & Tier*, *Pastry & Cupcakes*, *Custom Creations*).
  - Responsive cards with featured star badges and category tags.
  - Interactive Lightbox Preview Modal with high-resolution image preview and "Order Custom Cake Like This" CTA button.

- **Custom Cake Builder (`/custom`)**:
  - Artisan custom order builder form.
  - Base flavor selector (Chocolate Truffle, Vanilla, Strawberry, Red Velvet, Rosomalai Edition, Mango Fruit, etc.).
  - Weight selection chips (1 lb, 2 lb, 3 lb, 5 lb, 10 lb+).
  - Detailed design and customization instructions text area.
  - Client-side reference photo upload with automatic canvas-based JPEG image compression.
  - Dynamic "Get Inspired by Our Creations" grid fetching featured gallery items live from the database.

- **Cart & WhatsApp Checkout (`/checkout`)**:
  - Sliding side-drawer cart accessible from anywhere on the site.
  - Delivery vs. Store Pickup toggle with date picker (enforces 3-day advance notice) and time slot selection.
  - Live delivery address radius validator.
  - Automatic order database recording (`POST /api/orders`) prior to checkout redirect.
  - Pre-formatted WhatsApp order message generator with uploaded reference image links.

---

### 2. Complete Admin Control Center CMS (`/admin`)

- **Fixed Credentials Authentication (`/login`)**:
  - Secure fixed ID & password login session handling.

- **Admin Dashboard (`admin_dashboard.php` / `/admin`)**:
  - Key business analytics (Total products, Featured count, Total gallery items, Total customer orders, Sales volume).
  - One-click **Store Order Intake Status Toggle** (Accepting Orders vs. Paused).
  - Recent Incoming Orders table widget displaying real-time customer orders.
  - Navigation cards to all CMS sub-modules.

- **Admin Orders Manager (`admin_orders.php` / `/admin/orders`)**:
  - Full orders management suite for tracking live orders.
  - Summary metrics: Total Orders, Pending Confirmation, Confirmed/Baking, Total Revenue.
  - Status filter tabs (*All*, *Pending*, *Confirmed*, *Baking*, *Delivered*, *Cancelled*).
  - Detailed order cards displaying customer phone, fulfillment type, delivery address, delivery date/time, and special notes.
  - Line items breakdown with custom cake uploaded image previews.
  - Status update selector allowing real-time status updates in database & JSON fallback.

- **Admin Products Catalog (`admin_products.php` / `/admin/products`)**:
  - Manage catalog products and add-on items.
  - Product creation form (`admin_product_add.php`) and editor (`admin_product_edit.php`) with image file upload or URL inputs.
  - One-click **Featured Cake Toggle** to feature cakes on the homepage grid.
  - Product deletion with confirmation safeguards.

- **Admin Creations Gallery (`admin_gallery.php` / `/admin/gallery`)**:
  - Upload past bake photos to the creations gallery.
  - Category assignment (*Celebration Cakes*, *Wedding & Tier*, *Pastry & Cupcakes*, *Custom Creations*).
  - One-click **Make Featured / Featured** toggle (featured items appear in the Custom Cake inspiration grid).
  - Image delete functionality.

- **Admin Assets & Store Settings (`admin_assets.php` / `/admin/assets`)**:
  - Toggle live store order acceptance.
  - Update topbar announcement banner text.
  - Upload or update Hero Cake image asset and Custom Cake builder teaser image asset with live image previews.
  - Manage primary and secondary store contact phone numbers.

---

## 🛠️ Database Schema (`raj-confections-db`)

The database uses MariaDB / MySQL with the following primary tables:

1. **`products`**: Stores catalog cakes and party add-on items (`id`, `name`, `category`, `price`, `image`, `sizes`, `prices`, `is_featured`, `created_at`, `updated_at`).
2. **`orders`**: Customer checkout order records (`id`, `order_id`, `status`, `total_amount`, `customer_name`, `customer_phone`, `fulfillment_type`, `fulfillment_date`, `fulfillment_time`, `delivery_address`, `name_on_cake`, `special_notes`, `raw_payload`, `created_at`).
3. **`order_items`**: Line items for orders (`id`, `order_id`, `product_id`, `product_name`, `size`, `quantity`, `unit_price`, `created_at`).
4. **`settings`**: Key-value store for site configuration, assets, and active status (`setting_key`, `setting_value`, `updated_at`).
5. **`gallery`**: Creations showcase items (`id`, `title`, `category`, `image`, `is_featured`, `created_at`).

*Note: All API endpoints automatically fall back to reading/writing JSON files in `server/data/` if the MariaDB database is temporarily offline.*

---

## 📡 REST API Reference

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/health` | Diagnostics verifying API uptime, database connectivity, and auth status |
| `POST` | `/api/login` | Authenticates admin using fixed credentials |
| `GET` | `/api/auth/me` | Checks current admin session status |
| `POST` | `/api/auth/logout` | Logs out admin session |
| `GET` | `/api/settings` | Returns active store settings, status, banner text, and image URLs |
| `POST` | `/api/settings` | Updates store settings (Admin only) |
| `GET` | `/api/products` | Retrieves all catalog products from MariaDB / JSON fallback |
| `GET` | `/api/gallery` | Retrieves gallery items (supports `?featured=1` filter) |
| `POST` | `/api/gallery` | Adds new gallery item (Admin only) |
| `POST` | `/api/gallery/toggle_featured` | Flips featured status of a gallery item |
| `DELETE` | `/api/gallery?id=...` | Deletes a gallery item |
| `GET` | `/api/orders` | Retrieves customer orders with line items (supports `?id=...` filter) |
| `POST` | `/api/orders` | Records new customer order in database & JSON fallback |
| `POST` | `/api/orders/update_status` | Updates order status (Admin only) |
| `POST` | `/api/upload` | Uploads image file to local storage / Supabase |

---

## ⚡ Quickstart Guide

### 1. Database Setup
Execute the schema setup script and seed catalog data:
```bash
mariadb -u root -p < server/schema.sql
php server/seed.php
```

### 2. Compile React Frontend into PHP Server
To build the React SPA and place compiled assets into `server/public/`:
```bash
cd website
pnpm install
pnpm build
cp -r dist/* ../server/public/
```

### 3. Start the Unified PHP Server
Start the PHP web server from the project root:
```bash
php -S localhost:8000 server/router.php
```

Open `http://localhost:8000` in your browser. The single PHP server serves:
- React Website Frontend at `/`, `/catalog`, `/gallery`, `/custom`, `/checkout`
- Admin Control Center CMS at `/admin`
- REST APIs at `/api/...`
